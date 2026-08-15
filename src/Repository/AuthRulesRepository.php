<?php

/**
 * oe-module-coverage-latam — AuthRulesRepository
 *
 * CRUD para covl_auth_rules: reglas de autorización por práctica × financiador × plan.
 *
 * @package   OpenEMR\Modules\CoverageLatam\Repository
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Repository;

/**
 * Repositorio de reglas de autorización (covl_auth_rules).
 * Todas las consultas usan las funciones nativas de OpenEMR (sqlStatement / sqlQuery / sqlInsert).
 */
class AuthRulesRepository
{
    /**
     * Lista las reglas de autorización con filtros opcionales.
     *
     * @param array $filters  Claves soportadas:
     *                        - country_code string  (ej: 'AR')
     *                        - insurance_company_id int
     *                        - code_type string
     *                        - code string
     *                        - active int (1|0; omitir = todos)
     *                        - limit int  (por defecto 200)
     *                        - offset int (por defecto 0)
     *
     * @return array[]
     */
    public function list(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['country_code'])) {
            $where[]  = 'r.country_code = ?';
            $params[] = $filters['country_code'];
        }
        if (isset($filters['insurance_company_id']) && $filters['insurance_company_id'] !== '') {
            $where[]  = 'r.insurance_company_id = ?';
            $params[] = (int) $filters['insurance_company_id'];
        }
        if (!empty($filters['code_type'])) {
            $where[]  = 'r.code_type = ?';
            $params[] = $filters['code_type'];
        }
        if (!empty($filters['code'])) {
            $where[]  = 'r.code LIKE ?';
            $params[] = '%' . $filters['code'] . '%';
        }
        if (isset($filters['active']) && $filters['active'] !== '') {
            $where[]  = 'r.active = ?';
            $params[] = (int) $filters['active'];
        }

        $limit  = isset($filters['limit'])  ? (int) $filters['limit']  : 200;
        $offset = isset($filters['offset']) ? (int) $filters['offset'] : 0;

        $sql = "SELECT r.*,
                       ic.name AS insurer_name
                FROM covl_auth_rules r
                LEFT JOIN insurance_companies ic ON ic.id = r.insurance_company_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY r.country_code, r.priority, r.insurance_company_id, r.code_type, r.code
                LIMIT {$limit} OFFSET {$offset}";

        $res     = sqlStatement($sql, $params);
        $results = [];
        while ($row = sqlFetchArray($res)) {
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Cuenta el total de reglas con los mismos filtros (para paginación).
     */
    public function count(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['country_code'])) {
            $where[]  = 'country_code = ?';
            $params[] = $filters['country_code'];
        }
        if (isset($filters['insurance_company_id']) && $filters['insurance_company_id'] !== '') {
            $where[]  = 'insurance_company_id = ?';
            $params[] = (int) $filters['insurance_company_id'];
        }
        if (!empty($filters['code_type'])) {
            $where[]  = 'code_type = ?';
            $params[] = $filters['code_type'];
        }
        if (!empty($filters['code'])) {
            $where[]  = 'code LIKE ?';
            $params[] = '%' . $filters['code'] . '%';
        }
        if (isset($filters['active']) && $filters['active'] !== '') {
            $where[]  = 'active = ?';
            $params[] = (int) $filters['active'];
        }

        $row = sqlQuery(
            "SELECT COUNT(*) AS total FROM covl_auth_rules WHERE " . implode(' AND ', $where),
            $params
        );
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Retorna una regla por su ID.
     */
    public function findById(int $id): ?array
    {
        $row = sqlQuery("SELECT * FROM covl_auth_rules WHERE id = ? LIMIT 1", [$id]);
        return $row ?: null;
    }

    /**
     * Crea una nueva regla de autorización.
     *
     * @return int ID del nuevo registro
     */
    public function create(array $data): int
    {
        return (int) sqlInsert(
            "INSERT INTO covl_auth_rules
               (insurance_company_id, plan_pattern, code_type, code,
                auth_mode, max_quantity, priority, active, country_code, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                (int)   $data['insurance_company_id'],
                        $data['plan_pattern']  ?? '0',
                        $data['code_type'],
                        $data['code']          ?? '0',
                        $data['auth_mode'],
                isset($data['max_quantity']) && $data['max_quantity'] !== '' ? (int) $data['max_quantity'] : null,
                (int)  ($data['priority'] ?? 100),
                (int)  ($data['active']   ?? 1),
                        $data['country_code']  ?? null,
                        $data['notes']         ?? null,
            ]
        );
    }

    /**
     * Actualiza una regla de autorización existente.
     */
    public function update(int $id, array $data): bool
    {
        sqlStatement(
            "UPDATE covl_auth_rules SET
               insurance_company_id = ?,
               plan_pattern         = ?,
               code_type            = ?,
               code                 = ?,
               auth_mode            = ?,
               max_quantity         = ?,
               priority             = ?,
               active               = ?,
               country_code         = ?,
               notes                = ?
             WHERE id = ?",
            [
                (int)   $data['insurance_company_id'],
                        $data['plan_pattern']  ?? '0',
                        $data['code_type'],
                        $data['code']          ?? '0',
                        $data['auth_mode'],
                isset($data['max_quantity']) && $data['max_quantity'] !== '' ? (int) $data['max_quantity'] : null,
                (int)  ($data['priority'] ?? 100),
                (int)  ($data['active']   ?? 1),
                        $data['country_code']  ?? null,
                        $data['notes']         ?? null,
                $id,
            ]
        );
        return true;
    }

    /**
     * Alterna el estado activo/inactivo de una regla.
     *
     * @return bool Nuevo valor de active
     */
    public function toggleActive(int $id): bool
    {
        sqlStatement(
            "UPDATE covl_auth_rules SET active = IF(active = 1, 0, 1) WHERE id = ?",
            [$id]
        );
        $row = sqlQuery("SELECT active FROM covl_auth_rules WHERE id = ? LIMIT 1", [$id]);
        return (bool) ($row['active'] ?? false);
    }

    /**
     * Elimina una regla de autorización.
     */
    public function delete(int $id): bool
    {
        sqlStatement("DELETE FROM covl_auth_rules WHERE id = ?", [$id]);
        return true;
    }
}
