<?php

/**
 * oe-module-coverage-latam — CountryPackInstaller
 *
 * Instala un paquete de país (definido en packs/*.json):
 *   1. Registra el nomenclador nacional (tipo de código) en code_types si no existe.
 *   2. Da de alta / actualiza el paquete en covl_country_packs.
 *   3. Carga reglas de autorización base (INSERT IGNORE, idempotente).
 *   4. Carga reglas de frecuencia base (INSERT IGNORE, idempotente).
 *   5. Carga equivalencias de códigos a estándares (INSERT IGNORE, idempotente).
 *
 * @package   OpenEMR\Modules\CoverageLatam\Service
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Service;

/**
 * Aplica un paquete de país sobre la base de datos del módulo de forma idempotente.
 */
class CountryPackInstaller
{
    /**
     * Instala (o actualiza) un paquete de país.
     *
     * @param array<string, mixed> $pack Definición del paquete (contenido del JSON).
     * @return array<string, mixed> Resumen de lo instalado.
     * @throws \RuntimeException Si el paquete es inválido.
     */
    public function install(array $pack): array
    {
        $countryCode = strtoupper((string) ($pack['country_code'] ?? ''));
        if (!preg_match('/^[A-Z]{2}$/', $countryCode)) {
            throw new \RuntimeException('country_code inválido (se espera ISO 3166-1 alpha-2).');
        }

        $name    = (string) ($pack['name'] ?? $countryCode);
        $version = (string) ($pack['version'] ?? '1.0.0');
        $codeType = is_array($pack['code_type'] ?? null) ? $pack['code_type'] : [];
        $currency = is_array($pack['currency'] ?? null) ? $pack['currency'] : [];

        // 1. Nomenclador nacional en code_types
        $codeTypeKey = $codeType['ct_key'] ?? null;
        if ($codeTypeKey) {
            $this->registerCodeType($codeType);
        }

        // 1b. Garantizar columnas de moneda (soporte de instalaciones previas)
        $this->ensureCurrencyColumns();

        // 2. Paquete en covl_country_packs (upsert)
        $hasRules = !empty($pack['auth_rules']) || !empty($pack['frequency_rules']);
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
                $hasRules ? 1 : 0,
            ]
        );

        // 3. Reglas de autorización base
        $authCount = $this->loadAuthRules($countryCode, $codeTypeKey, $pack['auth_rules'] ?? []);

        // 4. Reglas de frecuencia base
        $freqCount = $this->loadFrequencyRules($countryCode, $codeTypeKey, $pack['frequency_rules'] ?? []);

        // 5. Equivalencias de códigos a estándares
        $mapCount = $this->loadCodeMaps($countryCode, $codeTypeKey, $pack['code_maps'] ?? []);

        return [
            'country_code'      => $countryCode,
            'version'           => $version,
            'code_type_key'     => $codeTypeKey,
            'auth_rules'        => $authCount,
            'frequency_rules'   => $freqCount,
            'code_maps'         => $mapCount,
            'default_rules'     => $hasRules,
        ];
    }

    /**
     * Garantiza la existencia de las columnas de moneda en covl_country_packs
     * (idempotente, para instalaciones creadas antes de la moneda por país).
     */
    private function ensureCurrencyColumns(): void
    {
        $cols = $this->tableColumns('covl_country_packs');
        if (!isset($cols['currency_code'])) {
            sqlStatement("ALTER TABLE covl_country_packs ADD COLUMN currency_code char(3) NOT NULL DEFAULT 'USD' AFTER code_type_key");
        }
        if (!isset($cols['currency_name'])) {
            sqlStatement("ALTER TABLE covl_country_packs ADD COLUMN currency_name varchar(50) DEFAULT NULL AFTER currency_code");
        }
        if (!isset($cols['currency_symbol'])) {
            sqlStatement("ALTER TABLE covl_country_packs ADD COLUMN currency_symbol varchar(5) DEFAULT NULL AFTER currency_name");
        }
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

        $exists = sqlQuery("SELECT ct_id FROM code_types WHERE ct_key = ? LIMIT 1", [$ctKey]);
        if ($exists) {
            return; // Ya registrado
        }

        $columns = $this->tableColumns('code_types');
        $nextId  = (int) (sqlQuery("SELECT COALESCE(MAX(ct_id), 100) + 1 AS next_id FROM code_types")['next_id'] ?? 100);

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
     * Carga las reglas de autorización base del paquete (idempotente).
     */
    private function loadAuthRules(string $countryCode, ?string $codeTypeKey, array $rules): int
    {
        if ($codeTypeKey === null || $codeTypeKey === '') {
            return 0;
        }
        $count = 0;
        foreach ($rules as $rule) {
            $code = (string) ($rule['code'] ?? '');
            if ($code === '') {
                continue;
            }
            sqlStatement(
                "INSERT IGNORE INTO covl_auth_rules
                    (insurance_company_id, plan_pattern, code_type, code, auth_mode, max_quantity, priority, active, country_code, notes)
                 VALUES (0, '0', ?, ?, ?, ?, ?, 1, ?, ?)",
                [
                    $codeTypeKey,
                    $code,
                    (string) ($rule['auth_mode'] ?? 'requerida'),
                    isset($rule['max_quantity']) ? (int) $rule['max_quantity'] : null,
                    (int) ($rule['priority'] ?? 100),
                    $countryCode,
                    (string) ($rule['notes'] ?? ''),
                ]
            );
            $count++;
        }
        return $count;
    }

    /**
     * Carga las reglas de frecuencia base del paquete (idempotente).
     */
    private function loadFrequencyRules(string $countryCode, ?string $codeTypeKey, array $rules): int
    {
        if ($codeTypeKey === null || $codeTypeKey === '') {
            return 0;
        }
        $count = 0;
        foreach ($rules as $rule) {
            $code = (string) ($rule['code'] ?? '');
            if ($code === '') {
                continue;
            }
            sqlStatement(
                "INSERT IGNORE INTO covl_frequency_rules
                    (insurance_company_id, code_type, code, min_interval_days, max_per_year, severity, active, country_code, notes)
                 VALUES (0, ?, ?, ?, ?, ?, 1, ?, ?)",
                [
                    $codeTypeKey,
                    $code,
                    (int) ($rule['min_interval_days'] ?? 180),
                    isset($rule['max_per_year']) ? (int) $rule['max_per_year'] : null,
                    (string) ($rule['severity'] ?? 'alerta'),
                    $countryCode,
                    (string) ($rule['notes'] ?? ''),
                ]
            );
            $count++;
        }
        return $count;
    }

    /**
     * Carga las equivalencias de códigos a estándares (idempotente).
     */
    private function loadCodeMaps(string $countryCode, ?string $codeTypeKey, array $maps): int
    {
        if ($codeTypeKey === null || $codeTypeKey === '') {
            return 0;
        }
        $count = 0;
        foreach ($maps as $map) {
            $localCode = (string) ($map['local_code'] ?? '');
            $stdCode   = (string) ($map['standard_code'] ?? '');
            if ($localCode === '' || $stdCode === '') {
                continue;
            }
            sqlStatement(
                "INSERT IGNORE INTO covl_country_code_maps
                    (country_code, local_code_type, local_code, standard_code_type, standard_code, local_desc, standard_desc, equivalence, active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)",
                [
                    $countryCode,
                    $codeTypeKey,
                    $localCode,
                    (string) ($map['standard_code_type'] ?? 'CPT4'),
                    $stdCode,
                    (string) ($map['local_desc'] ?? ''),
                    (string) ($map['standard_desc'] ?? ''),
                    (string) ($map['equivalence'] ?? 'aproximada'),
                ]
            );
            $count++;
        }
        return $count;
    }

    /**
     * Retorna las columnas existentes de una tabla (adaptación de schema).
     *
     * @return array<string, bool>
     */
    private function tableColumns(string $table): array
    {
        $res = sqlStatement("SHOW COLUMNS FROM `" . $table . "`");
        $cols = [];
        while ($row = sqlFetchArray($res)) {
            $cols[$row['Field']] = true;
        }
        return $cols;
    }
}
