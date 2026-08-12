<?php

/**
 * oe-module-coverage-latam — EligibilityResult
 *
 * Implementación concreta del resultado de verificación de elegibilidad.
 * Usada tanto por adaptadores en línea como por el ManualFallbackAdapter.
 *
 * @package   OpenEMR\Modules\CoverageLatam\Dto
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Dto;

use OpenEMR\Modules\CoverageLatam\Contracts\EligibilityResultInterface;

/**
 * Objeto de valor inmutable para resultados de elegibilidad.
 */
final class EligibilityResult implements EligibilityResultInterface
{
    /**
     * @param bool        $eligible        Indica si el afiliado tiene cobertura vigente
     * @param string      $statusMessage   Mensaje descriptivo del estado
     * @param string|null $planName        Nombre del plan activo según el financiador
     * @param string|null $coverageEndDate Fecha de vencimiento de cobertura (Y-m-d)
     * @param array       $rawResponse     Respuesta cruda del sistema externo
     * @param bool        $manual          true si fue carga manual (sin integración real)
     */
    public function __construct(
        private readonly bool    $eligible,
        private readonly string  $statusMessage,
        private readonly ?string $planName        = null,
        private readonly ?string $coverageEndDate = null,
        private readonly array   $rawResponse     = [],
        private readonly bool    $manual          = false,
    ) {
    }

    /**
     * Crea un resultado positivo (afiliado con cobertura vigente).
     */
    public static function eligible(
        string $statusMessage  = 'Afiliado con cobertura vigente',
        ?string $planName      = null,
        ?string $endDate       = null,
        array $rawResponse     = [],
        bool $manual           = false
    ): self {
        return new self(true, $statusMessage, $planName, $endDate, $rawResponse, $manual);
    }

    /**
     * Crea un resultado negativo (afiliado sin cobertura o cobertura vencida).
     */
    public static function notEligible(
        string $statusMessage = 'Afiliado sin cobertura vigente',
        array $rawResponse    = [],
        bool $manual          = false
    ): self {
        return new self(false, $statusMessage, null, null, $rawResponse, $manual);
    }

    public function isEligible(): bool
    {
        return $this->eligible;
    }

    public function getPlanName(): ?string
    {
        return $this->planName;
    }

    public function getCoverageEndDate(): ?string
    {
        return $this->coverageEndDate;
    }

    public function getStatusMessage(): string
    {
        return $this->statusMessage;
    }

    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }

    public function isManual(): bool
    {
        return $this->manual;
    }
}
