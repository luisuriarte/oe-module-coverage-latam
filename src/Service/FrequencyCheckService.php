<?php

/**
 * oe-module-coverage-latam — FrequencyCheckService
 *
 * Servicio de validación de frecuencia y periodicidad de prácticas.
 * Consulta covl_frequency_rules y verifica en billing + covl_authorizations
 * si ya existe la misma práctica dentro del intervalo mínimo configurado.
 *
 * @package   OpenEMR\Modules\CoverageLatam\Service
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Service;

/**
 * Resultado de la validación de frecuencia.
 */
final class FrequencyCheckResult
{
    public function __construct(
        /** true si la práctica se puede realizar (sin violación o severity=alerta) */
        public readonly bool    $allowed,
        /** true si se detectó una violación de frecuencia */
        public readonly bool    $violation,
        /** Nivel de severidad: 'alerta' o 'bloqueo' */
        public readonly string  $severity,
        /** Mensaje descriptivo para mostrar al usuario */
        public readonly string  $message,
        /** Fecha en que se realizó la última práctica similar (Y-m-d), null si no hubo */
        public readonly ?string $lastPerformedDate,
        /** Días restantes hasta que se cumpla el intervalo mínimo */
        public readonly ?int    $daysRemaining,
    ) {
    }

    public static function ok(string $message = 'Sin restricciones de frecuencia para esta práctica'): self
    {
        return new self(true, false, 'alerta', $message, null, null);
    }
}

/**
 * Servicio de validación de frecuencia/periodicidad de prácticas.
 */
class FrequencyCheckService
{
    public function __construct(
        private readonly \Closure $dbQuery, // Función que ejecuta consultas SQL (compatibilidad con sqlStatement de OpenEMR)
    ) {
    }

    /**
     * Valida si una práctica puede realizarse según las reglas de frecuencia configuradas.
     *
     * Fuentes de antecedentes consultadas:
     *   1. billing              — prestaciones ya facturadas en un encuentro
     *   2. covl_authorizations  — autorizaciones activas SIN encounter_id
     *                            (práctica agendada/aprobada pero no realizada aún;
     *                             también debe contar como antecedente)
     *
     * Restricciones evaluadas (independientes):
     *   A. max_per_year    — conteo en el año calendario de $requestDate
     *   B. min_interval_days — ventana de intervalo mínimo desde la última ocurrencia
     *
     * @param int    $pid          ID del paciente
     * @param int    $insCompanyId ID del financiador
     * @param string $codeType     Tipo de código (ct_key)
     * @param string $code         Código de la práctica
     * @param string $requestDate  Fecha de la práctica solicitada (Y-m-d; por defecto hoy)
     *
     * @return FrequencyCheckResult
     */
    public function check(
        int    $pid,
        int    $insCompanyId,
        string $codeType,
        string $code,
        string $requestDate = ''
    ): FrequencyCheckResult {
        if ($requestDate === '') {
            $requestDate = date('Y-m-d');
        }

        // 1. Buscar la regla de frecuencia
        $rule = $this->findRule($insCompanyId, $codeType, $code);
        if ($rule === null) {
            return FrequencyCheckResult::ok();
        }

        $intervalDays = (int) $rule['min_interval_days'];
        $severity     = $rule['severity'];
        $maxPerYear   = $rule['max_per_year'] !== null ? (int) $rule['max_per_year'] : null;

        // 2. Restricción A: max_per_year — conteo en el año calendario de $requestDate
        if ($maxPerYear !== null) {
            $yearCount = $this->countInYear($pid, $codeType, $code, $requestDate);
            if ($yearCount >= $maxPerYear) {
                $year    = substr($requestDate, 0, 4);
                $message = "La práctica {$code} ya fue realizada {$yearCount} vez/veces en {$year}. "
                         . "El máximo permitido por año calendario es {$maxPerYear}.";
                $allowed = ($severity === 'alerta');
                return new FrequencyCheckResult(
                    allowed:           $allowed,
                    violation:         true,
                    severity:          $severity,
                    message:           $message,
                    lastPerformedDate: null,
                    daysRemaining:     null,
                );
            }
        }

        // 3. Restricción B: min_interval_days — buscar antecedente en la ventana temporal
        $windowStart = date('Y-m-d', strtotime($requestDate . " -{$intervalDays} days"));

        // Consultar billing + covl_authorizations y tomar la fecha más reciente
        $lastDate = $this->findLastRelevantDate($pid, $codeType, $code, $windowStart, $requestDate);

        if ($lastDate === null) {
            return FrequencyCheckResult::ok(
                "Práctica habilitada — no hay antecedentes en los últimos {$intervalDays} días"
            );
        }

        // 4. Hay violación de intervalo: calcular días restantes
        $nextAllowed   = date('Y-m-d', strtotime($lastDate . " +{$intervalDays} days"));
        $daysRemaining = (int) ceil((strtotime($nextAllowed) - strtotime($requestDate)) / 86400);
        $daysRemaining = max(0, $daysRemaining);

        $message = "La práctica {$code} ya tiene un antecedente del {$lastDate}. "
            . "El intervalo mínimo es de {$intervalDays} días. "
            . "Próxima fecha habilitada: {$nextAllowed}.";

        $allowed = ($severity === 'alerta'); // alerta = permite continuar; bloqueo = no permite

        return new FrequencyCheckResult(
            allowed:           $allowed,
            violation:         true,
            severity:          $severity,
            message:           $message,
            lastPerformedDate: $lastDate,
            daysRemaining:     $daysRemaining,
        );
    }

    /**
     * Busca la regla de frecuencia más específica para la combinación financiador+código.
     *
     * @return array|null
     */
    private function findRule(int $insCompanyId, string $codeType, string $code): ?array
    {
        $sql = "SELECT fr.*
                FROM covl_frequency_rules fr
                WHERE fr.active = 1
                  AND fr.insurance_company_id = ?
                  AND fr.code_type = ?
                  AND fr.code = ?
                LIMIT 1";

        $result = ($this->dbQuery)($sql, [$insCompanyId, $codeType, $code]);
        return $result ?: null;
    }

    /**
     * Cuenta cuántas veces se realizó (o autorizó activamente) la práctica
     * en el año calendario de $requestDate.
     *
     * Combina dos fuentes para evitar doble conteo:
     *   - billing: prestaciones ya facturadas en un encounter
     *   - covl_authorizations: autorizaciones activas SIN encounter_id
     *     (las que tienen encounter_id ya están en billing)
     *
     * @param int    $pid         ID del paciente
     * @param string $codeType    Tipo de código
     * @param string $code        Código de la práctica
     * @param string $requestDate Fecha de referencia para extraer el año (Y-m-d)
     *
     * @return int Suma de ocurrencias en el año
     */
    private function countInYear(int $pid, string $codeType, string $code, string $requestDate): int
    {
        $year = (int) substr($requestDate, 0, 4);

        // Fuente 1: billing — prácticas ya facturadas en encounters
        $sqlBilling = "SELECT COUNT(*) AS total
                       FROM billing b
                       JOIN form_encounter fe ON fe.encounter = b.encounter AND fe.pid = b.pid
                       WHERE b.pid       = ?
                         AND b.code_type = ?
                         AND b.code      = ?
                         AND b.activity  = 1
                         AND YEAR(fe.date) = ?";
        $rowB        = ($this->dbQuery)($sqlBilling, [$pid, $codeType, $code, $year]);
        $fromBilling = isset($rowB['total']) ? (int) $rowB['total'] : 0;

        // Fuente 2: autorizaciones activas SIN encounter_id para no contar doble con billing
        $sqlAuth  = "SELECT COUNT(*) AS total
                     FROM covl_authorizations
                     WHERE pid          = ?
                       AND code_type    = ?
                       AND code         = ?
                       AND status       IN ('pendiente', 'en_auditoria', 'aprobada')
                       AND encounter_id IS NULL
                       AND YEAR(request_date) = ?";
        $rowA     = ($this->dbQuery)($sqlAuth, [$pid, $codeType, $code, $year]);
        $fromAuth = isset($rowA['total']) ? (int) $rowA['total'] : 0;

        return $fromBilling + $fromAuth;
    }

    /**
     * Devuelve la fecha más reciente (Y-m-d) entre billing y covl_authorizations
     * dentro de la ventana temporal indicada.
     *
     * Fuente 1 (billing): prácticas ya facturadas en un encounter.
     * Fuente 2 (covl_authorizations): autorizaciones activas SIN encounter_id.
     *   - Práctica agendada y aprobada pero aún no realizada → igualmente cuenta
     *     para el intervalo mínimo.
     *   - Se excluyen las que tienen encounter_id para no contar doble con billing.
     *
     * @return string|null  Fecha Y-m-d del antecedente más reciente, o null si no hay
     */
    private function findLastRelevantDate(
        int    $pid,
        string $codeType,
        string $code,
        string $windowStart,
        string $windowEnd
    ): ?string {
        // Fuente 1: billing
        $sqlBilling = "SELECT MAX(DATE(fe.date)) AS last_date
                       FROM billing b
                       JOIN form_encounter fe ON fe.encounter = b.encounter AND fe.pid = b.pid
                       WHERE b.pid       = ?
                         AND b.code_type = ?
                         AND b.code      = ?
                         AND b.activity  = 1
                         AND DATE(fe.date) BETWEEN ? AND ?";
        $rowB            = ($this->dbQuery)($sqlBilling, [$pid, $codeType, $code, $windowStart, $windowEnd]);
        $dateFromBilling = ($rowB && $rowB['last_date']) ? $rowB['last_date'] : null;

        // Fuente 2: autorizaciones activas SIN encounter_id (agendadas, aún no facturadas)
        $sqlAuth      = "SELECT MAX(DATE(request_date)) AS last_date
                         FROM covl_authorizations
                         WHERE pid          = ?
                           AND code_type    = ?
                           AND code         = ?
                           AND status       IN ('pendiente', 'en_auditoria', 'aprobada')
                           AND encounter_id IS NULL
                           AND DATE(request_date) BETWEEN ? AND ?";
        $rowA         = ($this->dbQuery)($sqlAuth, [$pid, $codeType, $code, $windowStart, $windowEnd]);
        $dateFromAuth = ($rowA && $rowA['last_date']) ? $rowA['last_date'] : null;

        // Retornar la más reciente de las dos fuentes
        if ($dateFromBilling === null && $dateFromAuth === null) {
            return null;
        }
        if ($dateFromBilling === null) {
            return $dateFromAuth;
        }
        if ($dateFromAuth === null) {
            return $dateFromBilling;
        }

        return max($dateFromBilling, $dateFromAuth);
    }
}
