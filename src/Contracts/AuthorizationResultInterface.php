<?php

/**
 * oe-module-coverage-latam — AuthorizationResultInterface
 *
 * Contrato del objeto de resultado para solicitudes y consultas de autorización.
 *
 * @package   OpenEMR\Modules\CoverageLatam\Contracts
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Contracts;

/**
 * Resultado de una solicitud o consulta de autorización previa.
 */
interface AuthorizationResultInterface
{
    /**
     * Retorna el estado de la autorización en términos del módulo.
     * Valores posibles: pendiente, en_auditoria, aprobada, rechazada, vencida, cancelada
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * Retorna el número de autorización asignado por el financiador.
     *
     * @return string|null  Null si la autorización aún no fue aprobada
     */
    public function getAuthNumber(): ?string;

    /**
     * Retorna la fecha de inicio de validez de la autorización.
     *
     * @return string|null  Formato Y-m-d
     */
    public function getValidFrom(): ?string;

    /**
     * Retorna la fecha de vencimiento de la autorización.
     *
     * @return string|null  Formato Y-m-d
     */
    public function getValidUntil(): ?string;

    /**
     * Retorna la cantidad de unidades autorizadas por el financiador.
     *
     * @return int|null
     */
    public function getAuthorizedQuantity(): ?int;

    /**
     * Retorna el motivo de rechazo si la autorización fue denegada.
     *
     * @return string|null
     */
    public function getRejectReason(): ?string;

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
