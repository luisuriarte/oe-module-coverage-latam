<?php

/**
 * oe-module-coverage-latam — CountryPackImporter
 *
 * Importa un paquete de país (packs/<country_code>.json) a la base de datos
 * del módulo reemplazando los scripts SQL seed hardcodeados (p. ej.
 * sql/argentina_seed.sql).
 *
 * Proceso (idempotente, todo en una transacción):
 *   1. Registra el nomenclador nacional (code_type) en code_types si no existe.
 *   2. Garantiza las columnas auxiliares (moneda y descripciones de mapeo).
 *   3. Upsert del paquete en covl_country_packs (name, version, currency_*,
 *      default_rules_loaded = 1).
 *   4. Upsert de cada entrada de auth_rules[] → covl_auth_rules.
 *   5. Upsert de cada entrada de frequency_rules[] → covl_frequency_rules.
 *   6. Upsert de cada entrada de code_maps[] → covl_country_code_maps.
 *
 * Los upserts aprovechan las UNIQUE KEY existentes:
 *   - uq_covl_auth_rules  (insurance_company_id, plan_pattern, code_type, code)
 *   - uq_covl_freq_rules  (insurance_company_id, code_type, code)
 *   - uq_covl_code_map    (local_code_type, local_code, standard_code_type, standard_code)
 *
 * Reglas base genéricas: insurance_company_id = 0 (no existe como financiador,
 * sirve de plantilla por país). plan_pattern y code vacíos se convierten al
 * sentinel '0' (nunca NULL, para que la UNIQUE KEY detecte duplicados).
 *
 * @package   OpenEMR\Modules\CoverageLatam\Service
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Service;

/**
 * Importa el contenido de un paquete de país (JSON) a la base de datos de forma
 * idempotente y transaccional, devolviendo un resumen insertado/actualizado.
 */
class CountryPackImporter
{
    /**
     * Lee packs/{countryCode}.json, valida su estructura y lo importa a la BD.
     *
     * @param string $countryCode Código ISO 3166-1 alpha-2 (ej: 'AR').
     * @return array<string, mixed> Resumen del import (inserted/updated por tabla).
     * @throws \RuntimeException Si el archivo no existe, el JSON es inválido o
     *                           la estructura no tiene los campos requeridos.
     */
    public function importCountryPack(string $countryCode): array
    {
        $code = strtoupper(trim($countryCode));
        if (!preg_match('/^[A-Z]{2}$/', $code)) {
            throw new \RuntimeException(sprintf(
                'Código de país inválido "%s" (se espera ISO 3166-1 alpha-2).',
                $countryCode
            ));
        }

        $pack = (new CountryPackCatalog())->get($code);
        if ($pack === null) {
            throw new \RuntimeException(sprintf(
                'No existe el paquete packs/%s.json en el catálogo del módulo.',
                strtolower($code)
            ));
        }

        return $this->import($pack);
    }

    /**
     * Importa un paquete ya cargado (contenido de packs/<code>.json).
     *
     * @param array<string, mixed> $pack
     * @return array<string, mixed> Resumen del import.
     * @throws \RuntimeException Si la estructura del paquete es inválida.
     */
    public function import(array $pack): array
    {
        $this->validate($pack);

        $countryCode = strtoupper((string) $pack['country_code']);
        $name        = (string) $pack['name'];
        $version     = (string) $pack['version'];
        $codeType    = is_array($pack['code_type'] ?? null) ? $pack['code_type'] : [];
        $codeTypeKey = (string) ($codeType['ct_key'] ?? '');
        $currency    = is_array($pack['currency'] ?? null) ? $pack['currency'] : [];

        $summary = [
            'country_code'    => $countryCode,
            'name'            => $name,
            'version'         => $version,
            'code_type_key'   => $codeTypeKey,
            'currency'        => [
                'code'   => (string) ($currency['code'] ?? ''),
                'name'   => (string) ($currency['name'] ?? ''),
                'symbol' => (string) ($currency['symbol'] ?? ''),
            ],
            'auth_rules'      => ['inserted' => 0, 'updated' => 0],
            'frequency_rules' => ['inserted' => 0, 'updated' => 0],
            'code_maps'       => ['inserted' => 0, 'updated' => 0],
        ];

        sqlBeginTrans();
        try {
            // 1. Nomenclador nacional en code_types (si no existe)
            if ($codeTypeKey !== '') {
                $this->registerCodeType($codeType);
            }

            // 1b. Garantizar columnas auxiliares (moneda + descripciones de mapeo)
            $this->ensureSchemaColumns();

            // 2. Paquete en covl_country_packs (upsert) + flag de reglas cargadas
            $this->upsertCountryPack(
                $countryCode,
                $name,
                $version,
                $codeTypeKey,
                $currency,
                1
            );

            // 3-5. Reglas y mapeos
            if ($codeTypeKey === '') {
                // Sin nomenclador no puede haber reglas base
                $authRules  = $pack['auth_rules'] ?? [];
                $freqRules  = $pack['frequency_rules'] ?? [];
                $codeMaps   = $pack['code_maps'] ?? [];
                if (!empty($authRules) || !empty($freqRules) || !empty($codeMaps)) {
                    throw new \RuntimeException(sprintf(
                        'El paquete %s define reglas o mapeos pero no un code_type.ct_key.',
                        $countryCode
                    ));
                }
            } else {
                $summary['auth_rules']      = $this->upsertAuthRules($countryCode, $codeTypeKey, $pack['auth_rules'] ?? []);
                $summary['frequency_rules'] = $this->upsertFrequencyRules($countryCode, $codeTypeKey, $pack['frequency_rules'] ?? []);
                $summary['code_maps']       = $this->upsertCodeMaps($countryCode, $codeTypeKey, $pack['code_maps'] ?? []);
            }

            sqlCommitTrans();
        } catch (\Throwable $e) {
            sqlRollbackTrans();
            throw new \RuntimeException(
                'Error al importar el paquete ' . $countryCode . ': ' . $e->getMessage(),
                0,
                $e
            );
        }

        return $summary;
    }

    /**
     * Valida la estructura mínima del paquete. Lanza excepción con mensaje claro.
     *
     * @param array<string, mixed> $pack
     * @throws \RuntimeException
     */
    private function validate(array $pack): void
    {
        $required = [
            'country_code'    => 'country_code (ISO 3166-1 alpha-2)',
            'name'            => 'name (nombre descriptivo del país)',
            'version'         => 'version (versión del paquete)',
            'code_type'       => 'code_type (nomenclador nacional con ct_key)',
            'auth_rules'      => 'auth_rules[] (puede estar vacío)',
            'frequency_rules' => 'frequency_rules[] (puede estar vacío)',
            'code_maps'       => 'code_maps[] (puede estar vacío)',
            'currency'        => 'currency (bloque de moneda con code)',
        ];

        foreach ($required as $key => $label) {
            if (!array_key_exists($key, $pack)) {
                throw new \RuntimeException(sprintf(
                    'Estructura de paquete inválida: falta el campo "%s".',
                    $label
                ));
            }
        }

        if (!preg_match('/^[A-Z]{2}$/', strtoupper((string) $pack['country_code']))) {
            throw new \RuntimeException('Estructura de paquete inválida: country_code debe ser ISO 3166-1 alpha-2.');
        }
        if (!is_array($pack['code_type']) || empty($pack['code_type']['ct_key'])) {
            throw new \RuntimeException('Estructura de paquete inválida: code_type.ct_key es obligatorio.');
        }
        if (!is_array($pack['currency']) || empty($pack['currency']['code'])) {
            throw new \RuntimeException('Estructura de paquete inválida: currency.code es obligatorio.');
        }
        foreach (['auth_rules', 'frequency_rules', 'code_maps'] as $listKey) {
            if (!is_array($pack[$listKey])) {
                throw new \RuntimeException(sprintf(
                    'Estructura de paquete inválida: %s debe ser un arreglo.',
                    $listKey
                ));
            }
        }
    }

    /**
     * Upsert del paquete en covl_country_packs.
     *
     * @param array<string, mixed> $currency
     */
    private function upsertCountryPack(
        string $countryCode,
        string $name,
        string $version,
        string $codeTypeKey,
        array $currency,
        int $rulesLoaded
    ): void {
        sqlStatement(
            "INSERT INTO covl_country_packs
                (country_code, name, version, code_type_key, currency_code, currency_name, currency_symbol, default_rules_loaded)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                version = VALUES(version),
                code_type_key = VALUES(code_type_key),
                currency_code = VALUES(currency_code),
                currency_name = VALUES(currency_name),
                currency_symbol = VALUES(currency_symbol),
                default_rules_loaded = VALUES(default_rules_loaded)",
            [
                $countryCode,
                $name,
                $version,
                $codeTypeKey,
                (string) ($currency['code'] ?? 'USD'),
                (string) ($currency['name'] ?? ''),
                (string) ($currency['symbol'] ?? ''),
                $rulesLoaded,
            ]
        );
    }

    /**
     * Upsert de reglas de autorización base (insurance_company_id = 0 genérico).
     *
     * @return array{inserted: int, updated: int}
     */
    private function upsertAuthRules(string $countryCode, string $codeTypeKey, array $rules): array
    {
        $result = ['inserted' => 0, 'updated' => 0];
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            $code      = (string) ($rule['code'] ?? '');
            $planPattern = (string) ($rule['plan_pattern'] ?? '');
            $code      = ($code === '') ? '0' : $code;
            $planPattern = ($planPattern === '') ? '0' : $planPattern;

            $authMode = (string) ($rule['auth_mode'] ?? 'requerida');
            $priority = (int) ($rule['priority'] ?? 100);
            $maxQty   = isset($rule['max_quantity']) && $rule['max_quantity'] !== ''
                ? (int) $rule['max_quantity']
                : null;
            $notes    = (string) ($rule['notes'] ?? '');

$existsRow = sqlQuery(
                "SELECT id FROM covl_auth_rules
                 WHERE insurance_company_id = 0 AND plan_pattern = ? AND code_type = ? AND code = ?
                 LIMIT 1",
                [$planPattern, $codeTypeKey, $code]
            );
            $exists = is_array($existsRow);

            $shouldInsert = !$exists;

            if ($shouldInsert) {
                sqlStatement(
                    "INSERT INTO covl_auth_rules
                        (insurance_company_id, plan_pattern, code_type, code, auth_mode, max_quantity, priority, active, country_code, notes)
                     VALUES (0, ?, ?, ?, ?, ?, ?, 1, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        auth_mode = VALUES(auth_mode),
                        max_quantity = VALUES(max_quantity),
                        priority = VALUES(priority),
                        active = 1,
                        country_code = VALUES(country_code),
                        notes = VALUES(notes)",
                    [
                        $planPattern,
                        $codeTypeKey,
                        $code,
                        $authMode,
                        $maxQty,
                        $priority,
                        $countryCode,
                        $notes,
                    ]
                );
                $result['inserted']++;
            } else {
                sqlStatement(
                    "INSERT INTO covl_auth_rules
                        (insurance_company_id, plan_pattern, code_type, code, auth_mode, max_quantity, priority, active, country_code, notes)
                     VALUES (0, ?, ?, ?, ?, ?, ?, 1, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        auth_mode = VALUES(auth_mode),
                        max_quantity = VALUES(max_quantity),
                        priority = VALUES(priority),
                        active = 1,
                        country_code = VALUES(country_code),
                        notes = VALUES(notes)",
                    [
                        $planPattern,
                        $codeTypeKey,
                        $code,
                        $authMode,
                        $maxQty,
                        $priority,
                        $countryCode,
                        $notes,
                    ]
                );
                $result['updated']++;
            }
        }
        error_log("[covl] AuthRules summary: inserted={$result['inserted']}, updated={$result['updated']}");
        return $result;
    }

    /**
     * Upsert de reglas de frecuencia base (insurance_company_id = 0 genérico).
     *
     * @return array{inserted: int, updated: int}
     */
    private function upsertFrequencyRules(string $countryCode, string $codeTypeKey, array $rules): array
    {
        $result = ['inserted' => 0, 'updated' => 0];
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            $code = (string) ($rule['code'] ?? '');
            if ($code === '') {
                continue;
            }

            $minInterval = (int) ($rule['min_interval_days'] ?? 180);
            $maxPerYear  = isset($rule['max_per_year']) && $rule['max_per_year'] !== ''
                ? (int) $rule['max_per_year']
                : null;
            $severity    = (string) ($rule['severity'] ?? 'alerta');
            $notes       = (string) ($rule['notes'] ?? '');

            $existsRow = sqlQuery(
                "SELECT id FROM covl_frequency_rules
                 WHERE insurance_company_id = 0 AND code_type = ? AND code = ?
                 LIMIT 1",
                [$codeTypeKey, $code]
            );
            $exists = is_array($existsRow);

            if ($exists) {
                sqlStatement(
                    "INSERT INTO covl_frequency_rules
                        (insurance_company_id, code_type, code, min_interval_days, max_per_year, severity, active, country_code, notes)
                     VALUES (0, ?, ?, ?, ?, ?, 1, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        min_interval_days = VALUES(min_interval_days),
                        max_per_year = VALUES(max_per_year),
                        severity = VALUES(severity),
                        active = 1,
                        country_code = VALUES(country_code),
                        notes = VALUES(notes)",
                    [
                        $codeTypeKey,
                        $code,
                        $minInterval,
                        $maxPerYear,
                        $severity,
                        $countryCode,
                        $notes,
                    ]
                );
                $result['updated']++;
            } else {
                sqlStatement(
                    "INSERT INTO covl_frequency_rules
                        (insurance_company_id, code_type, code, min_interval_days, max_per_year, severity, active, country_code, notes)
                     VALUES (0, ?, ?, ?, ?, ?, 1, ?, ?)",
                    [
                        $codeTypeKey,
                        $code,
                        $minInterval,
                        $maxPerYear,
                        $severity,
                        $countryCode,
                        $notes,
                    ]
                );
                $result['inserted']++;
            }
        }
        error_log("[covl] FrequencyRules summary: inserted={$result['inserted']}, updated={$result['updated']}");
        return $result;
    }

    /**
     * Upsert de equivalencias de códigos locales → estándar.
     * El INSERT se construye según las columnas existentes para tolerar
     * instalaciones previas sin local_desc/standard_desc.
     *
     * @return array{inserted: int, updated: int}
     */
    private function upsertCodeMaps(string $countryCode, string $codeTypeKey, array $maps): array
    {
        $result = ['inserted' => 0, 'updated' => 0];
        if (empty($maps)) {
            return $result;
        }

        $cols   = $this->tableColumns('covl_country_code_maps');
        $hasDesc = isset($cols['local_desc']) && isset($cols['standard_desc']);

        foreach ($maps as $map) {
            if (!is_array($map)) {
                continue;
            }
            $localCode = (string) ($map['local_code'] ?? '');
            $stdCode   = (string) ($map['standard_code'] ?? '');
            if ($localCode === '' || $stdCode === '') {
                continue;
            }

            $stdType   = (string) ($map['standard_code_type'] ?? 'CPT4');
            $equival   = (string) ($map['equivalence'] ?? 'aproximada');
            $localDesc = (string) ($map['local_desc'] ?? '');
            $stdDesc   = (string) ($map['standard_desc'] ?? '');

            $existsRow = sqlQuery(
                "SELECT id FROM covl_country_code_maps
                 WHERE local_code_type = ? AND local_code = ? AND standard_code_type = ? AND standard_code = ?
                 LIMIT 1",
                [$codeTypeKey, $localCode, $stdType, $stdCode]
            );
            $exists = is_array($existsRow);

            $columns  = ['country_code', 'local_code_type', 'local_code', 'standard_code_type', 'standard_code'];
            $values   = [$countryCode, $codeTypeKey, $localCode, $stdType, $stdCode];
            $updates  = [
                'country_code = VALUES(country_code)',
                'equivalence = VALUES(equivalence)',
                'active = 1',
            ];

            if ($hasDesc) {
                $columns[] = 'local_desc';
                $values[]  = $localDesc;
                $columns[] = 'standard_desc';
                $values[]  = $stdDesc;
                $updates[] = 'local_desc = VALUES(local_desc)';
                $updates[] = 'standard_desc = VALUES(standard_desc)';
            }
            $columns[] = 'equivalence';
            $values[]  = $equival;
            $columns[] = 'active';
            $values[]  = 1;

            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            sqlStatement(
                "INSERT INTO covl_country_code_maps (" . implode(', ', $columns) . ")
                 VALUES (" . $placeholders . ")
                 ON DUPLICATE KEY UPDATE " . implode(', ', $updates),
                $values
            );

            if ($exists) {
                $result['updated']++;
            } else {
                $result['inserted']++;
            }
        }
        return $result;
    }

    /**
     * Registra el tipo de código nacional en code_types si no existe.
     * Se adapta a las columnas disponibles del schema (OpenEMR 5.x–8.x).
     *
     * @param array<string, mixed> $codeType
     */
    private function registerCodeType(array $codeType): void
    {
        $ctKey = (string) ($codeType['ct_key'] ?? '');
        if ($ctKey === '') {
            return;
        }

        $existsRow = sqlQuery("SELECT ct_id FROM code_types WHERE ct_key = ? LIMIT 1", [$ctKey]);
        if (is_array($existsRow)) {
            return; // Ya registrado
        }

        $columns = $this->tableColumns('code_types');
        $nextIdRow = sqlQuery("SELECT COALESCE(MAX(ct_id), 100) + 1 AS next_id FROM code_types");
        $nextId = (int) (is_array($nextIdRow) ? ($nextIdRow['next_id'] ?? 100) : 100);

        $fields = [];
        $params = [];

        $fields[] = 'ct_id';
        $params[] = $nextId;

        $fields[] = 'ct_key';
        $params[] = $ctKey;

        $ctName = (string) ($codeType['ct_name'] ?? $codeType['ct_term'] ?? $ctKey);

        // Nombre: usar la columna disponible (ct_name en OpenEMR moderno, ct_label en forks)
        if (isset($columns['ct_name'])) {
            $fields[] = 'ct_name';
            $params[] = $ctName;
        } elseif (isset($columns['ct_label'])) {
            $fields[] = 'ct_label';
            $params[] = $ctName;
        }

        if (isset($columns['ct_term'])) {
            $fields[] = 'ct_term';
            $params[] = (string) ($codeType['ct_term'] ?? $ctKey);
        }
        if (isset($columns['ct_type'])) {
            $fields[] = 'ct_type';
            $params[] = (int) ($codeType['ct_type'] ?? 0);
        }
        if (isset($columns['ct_active'])) {
            $fields[] = 'ct_active';
            $params[] = 1;
        }
        if (isset($columns['ct_claim_type'])) {
            $fields[] = 'ct_claim_type';
            $params[] = (string) ($codeType['ct_claim_type'] ?? 'Professional');
        }
        if (isset($columns['ct_claim_category'])) {
            $fields[] = 'ct_claim_category';
            $params[] = (int) ($codeType['ct_claim_category'] ?? 12);
        }

        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        sqlStatement(
            "INSERT INTO code_types (" . implode(', ', $fields) . ") VALUES (" . $placeholders . ")",
            $params
        );
    }

    /**
     * Garantiza la existencia de columnas auxiliares usadas por el importer:
     *   - covl_country_packs: currency_code, currency_name, currency_symbol
     *   - covl_country_code_maps: local_desc, standard_desc
     * (idempotente, para instalaciones creadas con un schema más antiguo).
     */
    private function ensureSchemaColumns(): void
    {
        $packsCols = $this->tableColumns('covl_country_packs');
        if (!isset($packsCols['currency_code'])) {
            sqlStatement("ALTER TABLE covl_country_packs ADD COLUMN currency_code char(3) NOT NULL DEFAULT 'USD' AFTER code_type_key");
        }
        if (!isset($packsCols['currency_name'])) {
            sqlStatement("ALTER TABLE covl_country_packs ADD COLUMN currency_name varchar(50) DEFAULT NULL AFTER currency_code");
        }
        if (!isset($packsCols['currency_symbol'])) {
            sqlStatement("ALTER TABLE covl_country_packs ADD COLUMN currency_symbol varchar(5) DEFAULT NULL AFTER currency_name");
        }

        $mapCols = $this->tableColumns('covl_country_code_maps');
        if (!isset($mapCols['local_desc'])) {
            sqlStatement("ALTER TABLE covl_country_code_maps ADD COLUMN local_desc varchar(255) DEFAULT NULL AFTER standard_code");
        }
        if (!isset($mapCols['standard_desc'])) {
            sqlStatement("ALTER TABLE covl_country_code_maps ADD COLUMN standard_desc varchar(255) DEFAULT NULL AFTER local_desc");
        }
    }

    /**
     * Retorna las columnas existentes de una tabla (adaptación de schema).
     *
     * @return array<string, bool>
     */
    private function tableColumns(string $table): array
    {
        $res  = sqlStatement("SHOW COLUMNS FROM `" . $table . "`");
        $cols = [];
        while ($row = sqlFetchArray($res)) {
            $cols[$row['Field']] = true;
        }
        return $cols;
    }
}
