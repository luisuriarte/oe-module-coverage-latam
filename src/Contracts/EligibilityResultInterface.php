<?php

/**
 * oe-module-coverage-latam — EligibilityResultInterface
 *
 * Contrato del objeto de resultado para verificaciones de elegibilidad/vigencia.
 *
 * @package   OpenEMR\Modules\CoverageLatam\Contracts
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Contracts;

/**
 * Resultado de una verificación de elegibilidad/vigencia de afiliado.
 */
interface EligibilityResultInterface
{
    /**
     * Indica si el afiliado tiene cobertura vigente a la fecha consultada.
     *
     * @return bool
     */
    public function isEligible(): bool;

    /**
     * Retorna el nombre del plan activo del afiliado según el financiador.
     *
     * @return string|null
     */
    public function getPlanName(): ?string;

    /**
     * Retorna la fecha de vencimiento de la cobertura según el financiador.
     *
     * @return string|null  Formato Y-m-d o null si no aplica
     */
    public function getCoverageEndDate(): ?string;

    /**
     * Retorna el mensaje de estado devuelto por el financiador.
     *
     * @return string
     */
    public function getStatusMessage(): string;

    /**
     * Retorna la respuesta cruda del sistema externo para trazabilidad.
     *
     * @return array
     */
    public function getRawResponse(): array;

    /**
     * Indica si la respuesta provino de un sistema en línea o fue carga manual.
     *
     * @return bool
     */
    public function isManual(): bool;
}
