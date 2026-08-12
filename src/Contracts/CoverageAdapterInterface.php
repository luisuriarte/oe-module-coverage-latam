<?php

/**
 * oe-module-coverage-latam — CoverageAdapterInterface
 *
 * Contrato que deben implementar todos los adaptadores de integración
 * con sistemas externos de financiadores (obras sociales, prepagas, etc.).
 *
 * Empezar con el adaptador de carga manual (ManualFallbackAdapter) y
 * conectar implementaciones reales por financiador sin modificar el núcleo.
 *
 * @package   OpenEMR\Modules\CoverageLatam\Contracts
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Contracts;

/**
 * Interfaz que deben implementar todos los adaptadores de integración
 * con sistemas externos de financiadores.
 */
interface CoverageAdapterInterface
{
    /**
     * Verifica si un afiliado tiene cobertura vigente a una fecha dada.
     *
     * @param int    $pid          ID del paciente (patient_data.pid)
     * @param int    $insDataId    ID del registro de cobertura (insurance_data.id)
     * @param string $checkDate    Fecha de verificación en formato Y-m-d (por defecto hoy)
     *
     * @return EligibilityResultInterface Resultado de la verificación
     */
    public function checkEligibility(int $pid, int $insDataId, string $checkDate = ''): EligibilityResultInterface;

    /**
     * Envía una solicitud de autorización previa al financiador.
     *
     * @param int    $pid              ID del paciente
     * @param int    $insCompanyId     ID del financiador (insurance_companies.id)
     * @param string $codeType         Tipo de código (ct_key, ej: 'NNAR', 'CPT4')
     * @param string $code             Código de la práctica a autorizar
     * @param int    $quantity         Cantidad de sesiones/unidades solicitadas
     * @param array  $extraParams      Parámetros adicionales específicos del financiador
     *
     * @return AuthorizationResultInterface Resultado de la solicitud (incluye estado y número de autorización si fue aprobada)
     */
    public function requestAuthorization(
        int $pid,
        int $insCompanyId,
        string $codeType,
        string $code,
        int $quantity = 1,
        array $extraParams = []
    ): AuthorizationResultInterface;

    /**
     * Consulta el estado actual de una autorización previamente enviada.
     *
     * @param string $authNumber   Número de autorización del financiador
     * @param int    $insCompanyId ID del financiador
     *
     * @return AuthorizationResultInterface Estado actualizado de la autorización
     */
    public function queryAuthorizationStatus(string $authNumber, int $insCompanyId): AuthorizationResultInterface;

    /**
     * Retorna la clave única del adaptador (debe coincidir con covl_adapters.adapter_key).
     *
     * @return string
     */
    public function getAdapterKey(): string;

    /**
     * Indica si el adaptador está disponible y correctamente configurado.
     *
     * @return bool
     */
    public function isAvailable(): bool;
}
