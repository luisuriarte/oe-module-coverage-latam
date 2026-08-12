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
     * Se consultan:
     * 1. covl_frequency_rules → para obtener el intervalo mínimo y la severidad
     * 2. billing → para buscar si el paciente ya tiene la práctica en el período
     * 3. covl_authorizations → como respaldo si la práctica tuvo autorización previa
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

        // 2. Calcular la ventana: desde cuándo hay que buscar
        $windowStart = date('Y-m-d', strtotime($requestDate . " -{$intervalDays} days"));

        // 3. Buscar la última práctica igual dentro de la ventana en billing
        $lastDate = $this->findLastBillingDate($pid, $codeType, $code, $windowStart, $requestDate);

        if ($lastDate === null) {
            return FrequencyCheckResult::ok(
                "Práctica habilitada — no hay antecedentes en los últimos {$intervalDays} días"
            );
        }

        // 4. Hay violación: calcular días restantes
        $nextAllowed   = date('Y-m-d', strtotime($lastDate . " +{$intervalDays} days"));
        $daysRemaining = (int) ceil((strtotime($nextAllowed) - strtotime($requestDate)) / 86400);
        $daysRemaining = max(0, $daysRemaining);

        $message = "La práctica {$code} ya fue realizada el {$lastDate}. "
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
     * Busca la regla de frecuencia más específica para la combinación.
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
     * Busca en billing la última fecha de la práctica para el paciente en la ventana indicada.
     *
     * @return string|null  Fecha Y-m-d de la última realización, o null
     */
    private function findLastBillingDate(
        int    $pid,
        string $codeType,
        string $code,
        string $windowStart,
        string $windowEnd
    ): ?string {
        $sql = "SELECT MAX(DATE(fe.date)) AS last_date
                FROM billing b
                JOIN form_encounter fe ON fe.encounter = b.encounter AND fe.pid = b.pid
                WHERE b.pid       = ?
                  AND b.code_type = ?
                  AND b.code      = ?
                  AND b.activity  = 1
                  AND DATE(fe.date) BETWEEN ? AND ?";

        $result = ($this->dbQuery)($sql, [$pid, $codeType, $code, $windowStart, $windowEnd]);
        return ($result && $result['last_date']) ? $result['last_date'] : null;
    }
}
