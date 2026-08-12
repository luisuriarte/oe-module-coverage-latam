<?php

/**
 * oe-module-coverage-latam — AuthorizationResult
 *
 * Implementación concreta del resultado de solicitud/consulta de autorización.
 * Usada tanto por adaptadores en línea como por el ManualFallbackAdapter.
 *
 * @package   OpenEMR\Modules\CoverageLatam\Dto
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Dto;

use OpenEMR\Modules\CoverageLatam\Contracts\AuthorizationResultInterface;

/**
 * Objeto de valor inmutable para resultados de autorización.
 */
final class AuthorizationResult implements AuthorizationResultInterface
{
    /**
     * @param string      $status              Estado en términos del módulo
     * @param string|null $authNumber          Número de autorización del financiador
     * @param string|null $validFrom           Inicio de validez (Y-m-d)
     * @param string|null $validUntil          Vencimiento de la autorización (Y-m-d)
     * @param int|null    $authorizedQuantity  Cantidad autorizada
     * @param string|null $rejectReason        Motivo de rechazo
     * @param array       $rawResponse         Respuesta cruda del sistema externo
     * @param bool        $manual              true si fue carga manual
     */
    public function __construct(
        private readonly string  $status,
        private readonly ?string $authNumber          = null,
        private readonly ?string $validFrom           = null,
        private readonly ?string $validUntil          = null,
        private readonly ?int    $authorizedQuantity  = null,
        private readonly ?string $rejectReason        = null,
        private readonly array   $rawResponse         = [],
        private readonly bool    $manual              = true,
    ) {
    }

    /**
     * Crea un resultado de autorización aprobada.
     */
    public static function approved(
        string $authNumber,
        string $validFrom,
        string $validUntil,
        int $quantity        = 1,
        array $rawResponse   = [],
        bool $manual         = false
    ): self {
        return new self('aprobada', $authNumber, $validFrom, $validUntil, $quantity, null, $rawResponse, $manual);
    }

    /**
     * Crea un resultado de autorización pendiente de resolución.
     */
    public static function pending(
        array $rawResponse = [],
        bool $manual       = false
    ): self {
        return new self('pendiente', null, null, null, null, null, $rawResponse, $manual);
    }

    /**
     * Crea un resultado de autorización en auditoría por el financiador.
     */
    public static function underAudit(
        array $rawResponse = [],
        bool $manual       = false
    ): self {
        return new self('en_auditoria', null, null, null, null, null, $rawResponse, $manual);
    }

    /**
     * Crea un resultado de autorización rechazada.
     */
    public static function rejected(
        string $rejectReason,
        array $rawResponse = [],
        bool $manual       = false
    ): self {
        return new self('rechazada', null, null, null, null, $rejectReason, $rawResponse, $manual);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAuthNumber(): ?string
    {
        return $this->authNumber;
    }

    public function getValidFrom(): ?string
    {
        return $this->validFrom;
    }

    public function getValidUntil(): ?string
    {
        return $this->validUntil;
    }

    public function getAuthorizedQuantity(): ?int
    {
        return $this->authorizedQuantity;
    }

    public function getRejectReason(): ?string
    {
        return $this->rejectReason;
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
