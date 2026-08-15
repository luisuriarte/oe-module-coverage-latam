<?php

/**
 * oe-module-coverage-latam — ProviderCoverageService
 *
 * Servicio para verificar la vigencia del convenio entre un prestador
 * y un financiador a una fecha dada.
 *
 * Se usa en dos puntos del flujo:
 *   1. Agenda (turnos): filtrar disponibilidad de profesionales según financiador del paciente.
 *   2. Check-in: alertar si el profesional ya no tiene convenio vigente con el financiador.
 *
 * @package   OpenEMR\Modules\CoverageLatam\Service
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Service;

/**
 * Resultado de la verificación de convenio prestador × financiador.
 */
final class ProviderCoverageResult
{
    public function __construct(
        /** true si el prestador tiene convenio vigente con el financiador a la fecha indicada */
        public readonly bool    $hasCoverage,
        /** Número de prestador ante el financiador (matrícula de convenio) */
        public readonly ?string $providerNumber,
        /** Especialidades cubiertas por el convenio */
        public readonly ?string $specialties,
        /** Fecha de vencimiento del convenio (Y-m-d), null si no tiene vencimiento definido */
        public readonly ?string $dateEnd,
        /** Mensaje descriptivo del resultado */
        public readonly string  $message,
    ) {
    }

    public static function notFound(int $userId, int $insCompanyId): self
    {
        return new self(
            hasCoverage:    false,
            providerNumber: null,
            specialties:    null,
            dateEnd:        null,
            message:        xl('El profesional') . " (ID: {$userId}) " . xl('no tiene convenio registrado con el financiador') . " (ID: {$insCompanyId})"
        );
    }
}

/**
 * Servicio de verificación de vigencia de convenio prestador × financiador.
 */
class ProviderCoverageService
{
    public function __construct(
        private readonly \Closure $dbQuery,
    ) {
    }

    /**
     * Verifica si un prestador tiene convenio vigente con un financiador a una fecha.
     *
     * @param int      $userId        ID del profesional (users.id)
     * @param int      $insCompanyId  ID del financiador (insurance_companies.id)
     * @param string   $checkDate     Fecha de verificación (Y-m-d; por defecto hoy)
     * @param int|null $facilityId    Sede específica (null = cualquier sede)
     *
     * @return ProviderCoverageResult
     */
    public function check(
        int    $userId,
        int    $insCompanyId,
        string $checkDate  = '',
        ?int   $facilityId = null
    ): ProviderCoverageResult {
        if ($checkDate === '') {
            $checkDate = date('Y-m-d');
        }

        $params = [$userId, $insCompanyId, $checkDate, $checkDate];

        $facilityCondition = '';
        if ($facilityId !== null) {
            // Buscar convenio para la sede específica o para todas las sedes (NULL)
            $facilityCondition = ' AND (facility_id = ? OR facility_id IS NULL)';
            $params[] = $facilityId;
        }

        $sql = "SELECT pc.*
                FROM covl_provider_coverage pc
                WHERE pc.active = 1
                  AND pc.user_id = ?
                  AND pc.insurance_company_id = ?
                  AND pc.date_from <= ?
                  AND (pc.date_to IS NULL OR pc.date_to >= ?)
                  {$facilityCondition}
                ORDER BY pc.facility_id DESC  -- Prioriza convenio específico de sede sobre genérico
                LIMIT 1";

        $row = ($this->dbQuery)($sql, $params);

        if ($row === null) {
            return ProviderCoverageResult::notFound($userId, $insCompanyId);
        }

        return new ProviderCoverageResult(
            hasCoverage:    true,
            providerNumber: $row['provider_number'],
            specialties:    $row['specialties'],
            dateEnd:        $row['date_to'],
            message:        'Convenio vigente encontrado'
        );
    }

    /**
     * Retorna todos los financiadores con los que un prestador tiene convenio vigente
     * en una fecha determinada.
     *
     * Útil para filtrar disponibilidad en la agenda según el financiador del paciente.
     *
     * @param int    $userId     ID del profesional
     * @param string $checkDate  Fecha de verificación (Y-m-d; por defecto hoy)
     *
     * @return int[]  Lista de IDs de financiadores con convenio vigente
     */
    public function getActiveInsurerIds(int $userId, string $checkDate = ''): array
    {
        if ($checkDate === '') {
            $checkDate = date('Y-m-d');
        }

        $sql = "SELECT DISTINCT insurance_company_id
                FROM covl_provider_coverage
                WHERE active = 1
                  AND user_id = ?
                  AND date_from <= ?
                  AND (date_to IS NULL OR date_to >= ?)";

        $results = ($this->dbQuery)($sql, [$userId, $checkDate, $checkDate], true);
        return array_column($results ?? [], 'insurance_company_id');
    }
}
