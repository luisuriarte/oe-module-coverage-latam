<?php

/**
 * oe-module-coverage-latam — AuthorizationRepository
 *
 * Acceso a datos para la entidad de autorizaciones previas y su historial.
 * Utiliza las funciones nativas de OpenEMR (sqlStatement / sqlInsert / sqlQuery).
 *
 * @package   OpenEMR\Modules\CoverageLatam\Repository
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Repository;

/**
 * Repositorio de autorizaciones previas.
 *
 * Todas las consultas son parametrizadas con marcadores de posición (?) para
 * prevenir inyección SQL, siguiendo el patrón de OpenEMR.
 */
class AuthorizationRepository
{
    /**
     * Busca una autorización por su ID.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $result = sqlQuery(
            "SELECT * FROM covl_authorizations WHERE id = ? LIMIT 1",
            [$id]
        );
        return $result ?: null;
    }

    /**
     * Busca autorizaciones activas de un paciente para una práctica dada.
     *
     * @param int    $pid          ID del paciente
     * @param string $codeType     Tipo de código
     * @param string $code         Código de la práctica
     * @param int    $insCompanyId ID del financiador
     *
     * @return array[]
     */
    public function findActiveByPatientAndCode(int $pid, string $codeType, string $code, int $insCompanyId): array
    {
        $results = [];
        $res = sqlStatement(
            "SELECT * FROM covl_authorizations
             WHERE pid = ?
               AND code_type = ?
               AND code = ?
               AND insurance_company_id = ?
               AND status IN ('pendiente', 'en_auditoria', 'aprobada')
             ORDER BY request_date DESC",
            [$pid, $codeType, $code, $insCompanyId]
        );
        while ($row = sqlFetchArray($res)) {
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Crea un nuevo registro de autorización.
     *
     * @param array $data Datos de la autorización
     * @return int ID del registro creado
     */
    public function createAuthorization(array $data): int
    {
        return sqlInsert(
            "INSERT INTO covl_authorizations
                (pid, encounter_id, insurance_data_id, insurance_company_id,
                 code_type, code, code_text, quantity, status,
                 auth_number, valid_from, valid_until, requested_by, adapter_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['pid'],
                $data['encounter_id'] ?? null,
                $data['insurance_data_id'],
                $data['insurance_company_id'],
                $data['code_type'],
                $data['code'],
                $data['code_text'] ?? null,
                $data['quantity'] ?? 1,
                $data['status'],
                $data['auth_number'] ?? null,
                $data['valid_from'] ?? null,
                $data['valid_until'] ?? null,
                $data['requested_by'] ?? null,
                $data['adapter_id'] ?? null,
            ]
        );
    }

    /**
     * Actualiza el estado de una autorización.
     *
     * @param int    $authId    ID de la autorización
     * @param string $newStatus Nuevo estado
     * @param array  $extraData Datos adicionales (auth_number, valid_from, valid_until, reject_reason)
     *
     * @return bool
     */
    public function updateAuthorizationStatus(int $authId, string $newStatus, array $extraData = []): bool
    {
        $sets   = ['status = ?'];
        $params = [$newStatus];

        if (isset($extraData['auth_number'])) {
            $sets[]   = 'auth_number = ?';
            $params[] = $extraData['auth_number'];
        }
        if (isset($extraData['valid_from'])) {
            $sets[]   = 'valid_from = ?';
            $params[] = $extraData['valid_from'];
        }
        if (isset($extraData['valid_until'])) {
            $sets[]   = 'valid_until = ?';
            $params[] = $extraData['valid_until'];
        }
        if (isset($extraData['reject_reason'])) {
            $sets[]   = 'reject_reason = ?';
            $params[] = $extraData['reject_reason'];
        }
        if (in_array($newStatus, ['aprobada', 'rechazada'], true)) {
            $sets[]   = 'response_date = NOW()';
        }

        $params[] = $authId;

        sqlStatement(
            "UPDATE covl_authorizations SET " . implode(', ', $sets) . " WHERE id = ?",
            $params
        );

        return true;
    }

    /**
     * Registra un cambio de estado en el historial de auditoría.
     *
     * @param int         $authId     ID de la autorización
     * @param string|null $fromStatus Estado anterior
     * @param string      $toStatus   Nuevo estado
     * @param int         $changedBy  ID del usuario
     * @param string      $reason     Motivo del cambio
     */
    public function logStatusChange(
        int     $authId,
        ?string $fromStatus,
        string  $toStatus,
        int     $changedBy = 0,
        string  $reason    = ''
    ): void {
        sqlInsert(
            "INSERT INTO covl_authorization_history
                (authorization_id, status_from, status_to, changed_by, change_reason)
             VALUES (?, ?, ?, ?, ?)",
            [$authId, $fromStatus, $toStatus, $changedBy ?: null, $reason ?: null]
        );
    }

    /**
     * Busca la regla de autorización con mayor prioridad para la combinación dada.
     *
     * La resolución de prioridad: menor valor en `priority` = más prioritario.
     * Plan_pattern usa LIKE para soportar comodines (%).
     *
     * @param int    $insCompanyId ID del financiador
     * @param string $planName     Nombre del plan del paciente
     * @param string $codeType     Tipo de código
     * @param string $code         Código de la práctica
     *
     * @return array|null
     */
    public function findBestAuthRule(int $insCompanyId, string $planName, string $codeType, string $code): ?array
    {
        // Evalúa reglas específicas (con code) primero, luego genéricas (code IS NULL)
        // y aplica plan_pattern como LIKE
        $result = sqlQuery(
            "SELECT *
             FROM covl_auth_rules
             WHERE active = 1
               AND insurance_company_id = ?
               AND code_type = ?
               AND (code = ? OR code IS NULL)
               AND (plan_pattern IS NULL OR ? LIKE plan_pattern)
             ORDER BY priority ASC, (code IS NULL) ASC
             LIMIT 1",
            [$insCompanyId, $codeType, $code, $planName]
        );
        return $result ?: null;
    }

    /**
     * Retorna el ID del adaptador por su clave.
     *
     * @param string $adapterKey
     * @return int|null
     */
    public function getAdapterId(string $adapterKey): ?int
    {
        $result = sqlQuery(
            "SELECT id FROM covl_adapters WHERE adapter_key = ? LIMIT 1",
            [$adapterKey]
        );
        return $result ? (int) $result['id'] : null;
    }
}
