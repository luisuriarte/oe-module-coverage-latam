<?php

/**
 * oe-module-coverage-latam — AuthorizationService
 *
 * Servicio principal de gestión de autorizaciones previas.
 * Aplica las reglas de covl_auth_rules para determinar el modo de autorización
 * y delega al adaptador correspondiente.
 *
 * @package   OpenEMR\Modules\CoverageLatam\Service
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Service;

use OpenEMR\Modules\CoverageLatam\Contracts\AuthorizationResultInterface;
use OpenEMR\Modules\CoverageLatam\Dto\AuthorizationResult;
use OpenEMR\Modules\CoverageLatam\Repository\AuthorizationRepository;

/**
 * Servicio de autorizaciones previas.
 *
 * Flujo principal:
 * 1. Evalúa las reglas de covl_auth_rules para la combinación práctica × financiador × plan.
 * 2. Si auth_mode = 'automatica' → aprueba sin gestión externa.
 * 3. Si auth_mode = 'no_requerida' → no crea registro de autorización.
 * 4. Si auth_mode = 'requerida' → crea registro 'pendiente' y delega al adaptador.
 * 5. Registra el resultado en covl_authorizations y covl_authorization_history.
 */
class AuthorizationService
{
    public function __construct(
        private readonly AuthorizationRepository $repo,
        private readonly AdapterRegistry         $adapterRegistry,
    ) {
    }

    /**
     * Determina si una práctica requiere autorización y cuál es el modo.
     *
     * @param int    $insCompanyId  ID del financiador
     * @param string $planName      Nombre del plan del afiliado
     * @param string $codeType      Tipo de código (ct_key)
     * @param string $code          Código de la práctica
     *
     * @return array{mode: string, rule_id: int|null, max_quantity: int|null}
     *              mode: 'automatica' | 'requerida' | 'no_requerida'
     */
    public function resolveAuthMode(int $insCompanyId, string $planName, string $codeType, string $code): array
    {
        // Busca la regla más prioritaria que coincide con la combinación
        $rule = $this->repo->findBestAuthRule($insCompanyId, $planName, $codeType, $code);

        if ($rule === null) {
            // Sin regla configurada → comportamiento por defecto: requerida
            return ['mode' => 'requerida', 'rule_id' => null, 'max_quantity' => null];
        }

        return [
            'mode'         => $rule['auth_mode'],
            'rule_id'      => (int) $rule['id'],
            'max_quantity' => $rule['max_quantity'] !== null ? (int) $rule['max_quantity'] : null,
        ];
    }

    /**
     * Crea y procesa una solicitud de autorización.
     *
     * @param int    $pid           ID del paciente
     * @param int    $insDataId     ID del registro de cobertura (insurance_data.id)
     * @param int    $insCompanyId  ID del financiador
     * @param string $planName      Nombre del plan del afiliado
     * @param string $codeType      Tipo de código
     * @param string $code          Código de la práctica
     * @param string $codeText      Descripción de la práctica
     * @param int    $quantity      Cantidad solicitada
     * @param int    $requestedBy   ID del usuario que solicita
     * @param int|null $encounterId ID del encuentro (puede ser null)
     *
     * @return array{authorization_id: int|null, status: string, result: AuthorizationResultInterface|null}
     */
    public function requestAuthorization(
        int    $pid,
        int    $insDataId,
        int    $insCompanyId,
        string $planName,
        string $codeType,
        string $code,
        string $codeText,
        int    $quantity     = 1,
        int    $requestedBy  = 0,
        ?int   $encounterId  = null,
    ): array {
        $authMode = $this->resolveAuthMode($insCompanyId, $planName, $codeType, $code);

        // Si no requiere autorización, no se crea ningún registro
        if ($authMode['mode'] === 'no_requerida') {
            return [
                'authorization_id' => null,
                'status'           => 'no_requerida',
                'result'           => null,
            ];
        }

        // Obtener el adaptador apropiado para el financiador
        $adapter = $this->adapterRegistry->getForInsurer($insCompanyId);

        // Delegar al adaptador
        $result = $adapter->requestAuthorization($pid, $insCompanyId, $codeType, $code, $quantity);

        // Determinar estado final
        $finalStatus = $result->getStatus();
        if ($authMode['mode'] === 'automatica') {
            // Si la cantidad solicitada excede el máximo auto-aprobable → escalar a requerida
            if ($authMode['max_quantity'] !== null && $quantity > $authMode['max_quantity']) {
                $finalStatus = 'pendiente'; // Requiere revisión manual por exceder el límite automático
            } elseif ($finalStatus === 'pendiente') {
                $finalStatus = 'aprobada';  // Auto-aprobación dentro del límite configurado
            }
        }

        // Ejecutar dentro de transacción: si logStatusChange falla, se revierte el INSERT
        sqlBeginTrans();
        try {
            $authId = $this->repo->createAuthorization([
                'pid'                  => $pid,
                'encounter_id'         => $encounterId,
                'insurance_data_id'    => $insDataId,
                'insurance_company_id' => $insCompanyId,
                'code_type'            => $codeType,
                'code'                 => $code,
                'code_text'            => $codeText,
                'quantity'             => $quantity,
                'status'               => $finalStatus,
                'auth_number'          => $result->getAuthNumber(),
                'valid_from'           => $result->getValidFrom(),
                'valid_until'          => $result->getValidUntil(),
                'reject_reason'        => $result->getRejectReason(),
                'requested_by'         => $requestedBy,
                'adapter_id'           => $this->repo->getAdapterId($adapter->getAdapterKey()),
            ]);

            // Registrar en historial
            $this->repo->logStatusChange($authId, null, $finalStatus, $requestedBy, 'Creación de autorización');
            sqlCommitTrans();
        } catch (\Throwable $e) {
            sqlRollbackTrans();
            throw $e;
        }

        return [
            'authorization_id' => $authId,
            'status'           => $finalStatus,
            'result'           => $result,
        ];
    }

    /**
     * Actualiza el estado de una autorización existente.
     *
     * @param int    $authId       ID de la autorización
     * @param string $newStatus    Nuevo estado
     * @param int    $changedBy    ID del usuario que realiza el cambio
     * @param string $reason       Motivo del cambio de estado
     * @param array  $extraData    Datos adicionales (auth_number, valid_from, valid_until, reject_reason)
     */
    public function updateStatus(
        int    $authId,
        string $newStatus,
        int    $changedBy = 0,
        string $reason    = '',
        array  $extraData = []
    ): bool {
        $auth = $this->repo->findById($authId);
        if ($auth === null) {
            return false;
        }

        $updated = $this->repo->updateAuthorizationStatus($authId, $newStatus, $extraData);

        if ($updated) {
            $this->repo->logStatusChange($authId, $auth['status'], $newStatus, $changedBy, $reason);
        }

        return $updated;
    }

    /**
     * Vincula una autorización previa existente a un encuentro clínico (encounter_id)
     * cuando la práctica autorizada finaliza su ciclo de vida y se facturá.
     *
     * @param int $authId      ID de la autorización
     * @param int $encounterId ID del encuentro
     *
     * @return bool
     */
    public function linkToEncounter(int $authId, int $encounterId): bool
    {
        return $this->repo->linkToEncounter($authId, $encounterId);
    }
}

