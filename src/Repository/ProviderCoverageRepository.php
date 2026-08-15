<?php

/**
 * oe-module-coverage-latam — ProviderCoverageRepository
 *
 * CRUD para covl_provider_coverage: convenios de prestadores × financiador.
 *
 * Nota de diseño: en la tabla, facility_id = 0 (sentinel) significa "aplica a
 * todas las sedes". En la API y la UI se presenta como "Todas las sedes".
 *
 * @package   OpenEMR\Modules\CoverageLatam\Repository
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Repository;

/**
 * Repositorio de convenios de prestadores (covl_provider_coverage).
 * Todas las consultas usan las funciones nativas de OpenEMR (sqlStatement / sqlQuery / sqlInsert).
 */
class ProviderCoverageRepository
{
    /**
     * Lista los convenios con filtros opcionales.
     *
     * @param array $filters  Claves soportadas:
     *                        - user_id int (profesional)
     *                        - insurance_company_id int
     *                        - facility_id int (0 = todas las sedes)
     *                        - active int (1|0; omitir = todos)
     *                        - search string (matrícula, especialidades o nombre del profesional)
     *                        - limit int  (por defecto 100)
     *                        - offset int (por defecto 0)
     *
     * @return array[]
     */
    public function list(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $where[]  = 'pc.user_id = ?';
            $params[] = (int) $filters['user_id'];
        }
        if (isset($filters['insurance_company_id']) && $filters['insurance_company_id'] !== '') {
            $where[]  = 'pc.insurance_company_id = ?';
            $params[] = (int) $filters['insurance_company_id'];
        }
        if (isset($filters['facility_id']) && $filters['facility_id'] !== '') {
            $where[]  = 'pc.facility_id = ?';
            $params[] = (int) $filters['facility_id'];
        }
        if (isset($filters['active']) && $filters['active'] !== '') {
            $where[]  = 'pc.active = ?';
            $params[] = (int) $filters['active'];
        }
        if (!empty($filters['search'])) {
            $where[]  = '(pc.provider_number LIKE ? OR pc.specialties LIKE ? OR CONCAT(u.fname, " ", u.lname) LIKE ?)';
            $like     = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $limit  = isset($filters['limit'])  ? (int) $filters['limit']  : 100;
        $offset = isset($filters['offset']) ? (int) $filters['offset'] : 0;

        $sql = "SELECT pc.*,
                       u.fname,
                       u.mname,
                       u.lname,
                       u.username,
                       u.specialty AS user_specialty,
                       ic.name     AS insurer_name,
                       f.name      AS facility_name
                FROM covl_provider_coverage pc
                LEFT JOIN users u                ON u.id  = pc.user_id
                LEFT JOIN insurance_companies ic ON ic.id = pc.insurance_company_id
                LEFT JOIN facility f             ON f.id  = pc.facility_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY pc.date_from DESC, pc.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $res     = sqlStatement($sql, $params);
        $results = [];
        while ($row = sqlFetchArray($res)) {
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Cuenta el total de convenios con los mismos filtros (para paginación).
     */
    public function count(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];

        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $where[]  = 'pc.user_id = ?';
            $params[] = (int) $filters['user_id'];
        }
        if (isset($filters['insurance_company_id']) && $filters['insurance_company_id'] !== '') {
            $where[]  = 'pc.insurance_company_id = ?';
            $params[] = (int) $filters['insurance_company_id'];
        }
        if (isset($filters['facility_id']) && $filters['facility_id'] !== '') {
            $where[]  = 'pc.facility_id = ?';
            $params[] = (int) $filters['facility_id'];
        }
        if (isset($filters['active']) && $filters['active'] !== '') {
            $where[]  = 'pc.active = ?';
            $params[] = (int) $filters['active'];
        }
        if (!empty($filters['search'])) {
            $where[]  = '(pc.provider_number LIKE ? OR pc.specialties LIKE ? OR CONCAT(u.fname, " ", u.lname) LIKE ?)';
            $like     = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $row = sqlQuery(
            "SELECT COUNT(*) AS total
             FROM covl_provider_coverage pc
             LEFT JOIN users u ON u.id = pc.user_id
             WHERE " . implode(' AND ', $where),
            $params
        );
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Retorna un convenio por su ID.
     */
    public function findById(int $id): ?array
    {
        $row = sqlQuery(
            "SELECT pc.*,
                    u.fname,
                    u.mname,
                    u.lname,
                    u.username,
                    u.specialty AS user_specialty,
                    ic.name     AS insurer_name,
                    f.name      AS facility_name
             FROM covl_provider_coverage pc
             LEFT JOIN users u                ON u.id  = pc.user_id
             LEFT JOIN insurance_companies ic ON ic.id = pc.insurance_company_id
             LEFT JOIN facility f             ON f.id  = pc.facility_id
             WHERE pc.id = ?
             LIMIT 1",
            [$id]
        );
        return $row ?: null;
    }

    /**
     * Crea un nuevo convenio.
     *
     * @return int ID del nuevo registro
     */
    public function create(array $data): int
    {
        return (int) sqlInsert(
            "INSERT INTO covl_provider_coverage
               (user_id, insurance_company_id, facility_id, provider_number,
                date_from, date_to, specialties, active, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                (int)   $data['user_id'],
                (int)   $data['insurance_company_id'],
                (int)  ($data['facility_id']     ?? 0),
                        $data['provider_number'] ?? null,
                        $data['date_from'],
                        $data['date_to']         ?? null,
                        $data['specialties']     ?? null,
                (int)  ($data['active']          ?? 1),
                        $data['notes']           ?? null,
            ]
        );
    }

    /**
     * Actualiza un convenio existente.
     */
    public function update(int $id, array $data): bool
    {
        sqlStatement(
            "UPDATE covl_provider_coverage SET
               user_id             = ?,
               insurance_company_id = ?,
               facility_id         = ?,
               provider_number     = ?,
               date_from           = ?,
               date_to             = ?,
               specialties         = ?,
               active              = ?,
               notes               = ?
             WHERE id = ?",
            [
                (int)   $data['user_id'],
                (int)   $data['insurance_company_id'],
                (int)  ($data['facility_id']     ?? 0),
                        $data['provider_number'] ?? null,
                        $data['date_from'],
                        $data['date_to']         ?? null,
                        $data['specialties']     ?? null,
                (int)  ($data['active']          ?? 1),
                        $data['notes']           ?? null,
                $id,
            ]
        );
        return true;
    }

    /**
     * Alterna el estado activo/inactivo de un convenio.
     *
     * @return bool Nuevo valor de active
     */
    public function toggleActive(int $id): bool
    {
        sqlStatement(
            "UPDATE covl_provider_coverage SET active = IF(active = 1, 0, 1) WHERE id = ?",
            [$id]
        );
        $row = sqlQuery("SELECT active FROM covl_provider_coverage WHERE id = ? LIMIT 1", [$id]);
        return (bool) ($row['active'] ?? false);
    }

    /**
     * Elimina un convenio.
     */
    public function delete(int $id): bool
    {
        sqlStatement("DELETE FROM covl_provider_coverage WHERE id = ?", [$id]);
        return true;
    }

    /**
     * Lista profesionales activos para los selectores del formulario.
     *
     * @return array[]  [['id'=>, 'fullname'=>, 'specialty'=>], ...]
     */
    public function listProfessionals(): array
    {
        $res     = sqlStatement(
            "SELECT id, fname, mname, lname, username, specialty
             FROM users
             WHERE active = 1 AND authorized = 1
             ORDER BY lname ASC, fname ASC"
        );
        $results = [];
        while ($row = sqlFetchArray($res)) {
            $results[] = [
                'id'         => (int) $row['id'],
                'fullname'   => trim(($row['lname'] ?? '') . ', ' . ($row['fname'] ?? '') . ' ' . ($row['mname'] ?? '')),
                'username'   => $row['username'],
                'specialty'  => $row['specialty'],
            ];
        }
        return $results;
    }

    /**
     * Lista las sedes activas para los selectores del formulario.
     *
     * @return array[]  [['id'=>, 'name'=>], ...]
     */
    public function listFacilities(): array
    {
        $res     = sqlStatement("SELECT id, name FROM facility ORDER BY name ASC");
        $results = [];
        while ($row = sqlFetchArray($res)) {
            $results[] = ['id' => (int) $row['id'], 'name' => $row['name']];
        }
        return $results;
    }
}
