<?php

/**
 * oe-module-coverage-latam — ManualFallbackAdapter
 *
 * Adaptador de carga manual: implementa CoverageAdapterInterface sin llamadas
 * a sistemas externos. Registra la operación como manual y retorna un resultado
 * "pendiente" para que el operador complete los datos desde la interfaz.
 *
 * Es el adaptador por defecto instalado con el módulo. Actúa como fallback
 * cuando no existe una integración real configurada para el financiador.
 *
 * @package   OpenEMR\Modules\CoverageLatam\Adapter
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Adapter;

use OpenEMR\Modules\CoverageLatam\Contracts\CoverageAdapterInterface;
use OpenEMR\Modules\CoverageLatam\Contracts\EligibilityResultInterface;
use OpenEMR\Modules\CoverageLatam\Contracts\AuthorizationResultInterface;
use OpenEMR\Modules\CoverageLatam\Dto\EligibilityResult;
use OpenEMR\Modules\CoverageLatam\Dto\AuthorizationResult;

/**
 * Adaptador de carga manual sin integración en línea.
 *
 * Registra cada operación como "manual" y retorna resultados que indican
 * al operador que debe completar la información vía interfaz.
 */
class ManualFallbackAdapter implements CoverageAdapterInterface
{
    public const ADAPTER_KEY = 'manual_fallback';

    public function getAdapterKey(): string
    {
        return self::ADAPTER_KEY;
    }

    public function isAvailable(): bool
    {
        // El adaptador manual siempre está disponible como fallback
        return true;
    }

    /**
     * Verifica elegibilidad en modo manual.
     * No realiza llamadas externas; retorna estado "requiere verificación manual".
     */
    public function checkEligibility(int $pid, int $insDataId, string $checkDate = ''): EligibilityResultInterface
    {
        return EligibilityResult::eligible(
            statusMessage: 'Verificación manual requerida — sin integración en línea con el financiador',
            rawResponse: [
                'adaptador'    => self::ADAPTER_KEY,
                'pid'          => $pid,
                'ins_data_id'  => $insDataId,
                'fecha'        => $checkDate ?: date('Y-m-d'),
                'modo'         => 'manual',
            ],
            manual: true
        );
    }

    /**
     * Solicita autorización en modo manual.
     * Registra la solicitud como pendiente; el operador debe completarla desde la interfaz.
     */
    public function requestAuthorization(
        int $pid,
        int $insCompanyId,
        string $codeType,
        string $code,
        int $quantity = 1,
        array $extraParams = []
    ): AuthorizationResultInterface {
        return AuthorizationResult::pending(
            rawResponse: [
                'adaptador'      => self::ADAPTER_KEY,
                'pid'            => $pid,
                'financiador_id' => $insCompanyId,
                'tipo_codigo'    => $codeType,
                'codigo'         => $code,
                'cantidad'       => $quantity,
                'modo'           => 'manual',
                'mensaje'        => 'Autorización ingresada manualmente — pendiente de gestión con el financiador',
            ],
            manual: true
        );
    }

    /**
     * Consulta el estado de una autorización en modo manual.
     * Retorna siempre "pendiente" ya que no hay integración que consultar.
     */
    public function queryAuthorizationStatus(string $authNumber, int $insCompanyId): AuthorizationResultInterface
    {
        return AuthorizationResult::pending(
            rawResponse: [
                'adaptador'      => self::ADAPTER_KEY,
                'num_autorizacion' => $authNumber,
                'financiador_id' => $insCompanyId,
                'modo'           => 'manual',
                'mensaje'        => 'Consulta manual — actualizar el estado desde la interfaz del módulo',
            ],
            manual: true
        );
    }
}
