<?php

/**
 * oe-module-coverage-latam — SettlementBatchRepository
 *
 * CRUD para covl_settlement_batches y covl_settlement_items:
 * lotes de liquidación periódica y sus prestaciones individuales.
 *
 * @package   OpenEMR\Modules\CoverageLatam\Repository
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\CoverageLatam\Repository;

/**
 * Repositorio de lotes de liquidación.
 * Todas las consultas usan las funciones nativas de OpenEMR (sqlStatement / sqlQuery / sqlInsert).
 */
class SettlementBatchRepository
{
    /**
     * Estados posibles de un lote.
     */
    public const STATUSES = [
        'borrador', 'armado', 'presentado', 'pagado_parcial', 'pagado_total', 'en_disputa', 'anulado',
    ];

    /**
     * Estados de ítem dentro de un lote.
     */
    public const ITEM_STATUSES = [
        'incluido', 'aprobado', 'rechazado', 'debitado',
    ];

    /**
     * Lista los lotes con filtros opcionales.
     *
     * @param array $filters  Claves soportadas:
     *                        - insurance_company_id int
     *                        - facility_id int
     *                        - status string
     *                        - period_from string (Y-m-d) — periodo mínimo
     *                        - period_to string   (Y-m-d) — periodo máximo
     *                        - limit int  (por defecto 100)
     *                        - offset int (por defecto 0)
     *
     * @return array[]
     */
    public function list(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (isset($filters['insurance_company_id']) && $filters['insurance_company_id'] !== '') {
            $where[]  = 'b.insurance_company_id = ?';
            $params[] = (int) $filters['insurance_company_id'];
        }
        if (isset($filters['facility_id']) && $filters['facility_id'] !== '') {
            $where[]  = 'b.facility_id = ?';
            $params[] = (int) $filters['facility_id'];
        }
        if (!empty($filters['status'])) {
            $where[]  = 'b.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['period_from'])) {
            $where[]  = 'b.period_to >= ?';
            $params[] = $filters['period_from'];
        }
        if (!empty($filters['period_to'])) {
            $where[]  = 'b.period_from <= ?';
            $params[] = $filters['period_to'];
        }
        if (!empty($filters['search'])) {
            $where[]  = 'b.batch_number LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        $limit  = isset($filters['limit'])  ? (int) $filters['limit']  : 100;
        $offset = isset($filters['offset']) ? (int) $filters['offset'] : 0;

        $sql = "SELECT b.*,
                       ic.name        AS insurer_name,
                       f.name         AS facility_name,
                       u.fname        AS creator_fname,
                       u.lname        AS creator_lname
                FROM covl_settlement_batches b
                LEFT JOIN insurance_companies ic ON ic.id = b.insurance_company_id
                LEFT JOIN facility f             ON f.id  = b.facility_id
                LEFT JOIN users u                ON u.id  = b.created_by
                WHERE " . implode(' AND ', $where) . "
                ORDER BY b.period_from DESC, b.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $res     = sqlStatement($sql, $params);
        $results = [];
        while ($row = sqlFetchArray($res)) {
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Cuenta el total de lotes con los mismos filtros.
     */
    public function count(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];

        if (isset($filters['insurance_company_id']) && $filters['insurance_company_id'] !== '') {
            $where[]  = 'b.insurance_company_id = ?';
            $params[] = (int) $filters['insurance_company_id'];
        }
        if (isset($filters['facility_id']) && $filters['facility_id'] !== '') {
            $where[]  = 'b.facility_id = ?';
            $params[] = (int) $filters['facility_id'];
        }
        if (!empty($filters['status'])) {
            $where[]  = 'b.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['period_from'])) {
            $where[]  = 'b.period_to >= ?';
            $params[] = $filters['period_from'];
        }
        if (!empty($filters['period_to'])) {
            $where[]  = 'b.period_from <= ?';
            $params[] = $filters['period_to'];
        }
        if (!empty($filters['search'])) {
            $where[]  = 'b.batch_number LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        $row = sqlQuery(
            "SELECT COUNT(*) AS total FROM covl_settlement_batches b WHERE " . implode(' AND ', $where),
            $params
        );
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Retorna un lote (sin ítems) por su ID.
     */
    public function findById(int $id): ?array
    {
        $row = sqlQuery(
            "SELECT b.*,
                    ic.name AS insurer_name,
                    f.name  AS facility_name
             FROM covl_settlement_batches b
             LEFT JOIN insurance_companies ic ON ic.id = b.insurance_company_id
             LEFT JOIN facility f             ON f.id  = b.facility_id
             WHERE b.id = ?
             LIMIT 1",
            [$id]
        );
        return $row ?: null;
    }

    /**
     * Crea un nuevo lote en estado borrador.
     *
     * @return int ID del nuevo lote
     */
    public function create(array $data): int
    {
        $batchNumber = $data['batch_number'] ?? $this->nextBatchNumber(
            (string) ($data['country_code'] ?? 'AR'),
            (int) $data['insurance_company_id']
        );

        $id = (int) sqlInsert(
            "INSERT INTO covl_settlement_batches
               (batch_number, insurance_company_id, facility_id,
                period_from, period_to, currency, status, created_by)
             VALUES (?, ?, ?, ?, ?, ?, 'borrador', ?)",
            [
                $batchNumber,
                (int) $data['insurance_company_id'],
                (int) ($data['facility_id'] ?? 0),
                        $data['period_from'],
                        $data['period_to'],
                (string) ($data['currency'] ?? 'ARS'),
                isset($data['created_by']) ? (int) $data['created_by'] : null,
            ]
        );
        return $id;
    }

    /**
     * Actualiza los datos maestros de un lote.
     * Solo se permite mientras el lote está en 'borrador' o 'armado'.
     */
    public function update(int $id, array $data): bool
    {
        $current = $this->findById($id);
        if ($current === null) {
            return false;
        }
        if (!in_array($current['status'], ['borrador', 'armado'], true)) {
            return false;
        }

        sqlStatement(
            "UPDATE covl_settlement_batches SET
               insurance_company_id = ?,
               facility_id          = ?,
               period_from          = ?,
               period_to            = ?,
               currency             = ?
             WHERE id = ?",
            [
                (int) $data['insurance_company_id'],
                (int) ($data['facility_id'] ?? 0),
                        $data['period_from'],
                        $data['period_to'],
                (string) ($data['currency'] ?? 'ARS'),
                $id,
            ]
        );
        return true;
    }

    /**
     * Transiciona el estado de un lote registrando los campos asociados.
     *
     * Mapa de transiciones soportadas (destino → campos opcionales):
     *   armado         cerrar el lote
     *   presentado     presentation_date (por defecto hoy)
     *   pagado_parcial paid_amount, payment_date, payment_reference
     *   pagado_total   paid_amount, payment_date, payment_reference
     *   en_disputa     dispute_notes
     *   anulado        —
     *
     * @return bool true si la transición se realizó
     */
    public function transition(int $id, string $status, array $data = []): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }
        $current = $this->findById($id);
        if ($current === null || $current['status'] === 'anulado') {
            return false;
        }

        switch ($status) {
            case 'armado':
                $sql = "UPDATE covl_settlement_batches SET status = 'armado' WHERE id = ?";
                break;
            case 'presentado':
                $sql = "UPDATE covl_settlement_batches
                        SET status = 'presentado', presentation_date = ?
                        WHERE id = ?";
                break;
            case 'pagado_parcial':
            case 'pagado_total':
                $sql = "UPDATE covl_settlement_batches
                        SET status = ?, paid_amount = ?, payment_date = ?, payment_reference = ?
                        WHERE id = ?";
                break;
            case 'en_disputa':
                $sql = "UPDATE covl_settlement_batches
                        SET status = 'en_disputa', dispute_notes = ?
                        WHERE id = ?";
                break;
            case 'anulado':
            default:
                $sql = "UPDATE covl_settlement_batches SET status = 'anulado' WHERE id = ?";
                break;
        }

        switch ($status) {
            case 'presentado':
                $params = [$data['presentation_date'] ?? date('Y-m-d'), $id];
                break;
            case 'pagado_parcial':
            case 'pagado_total':
                $params = [
                    $status,
                    $data['paid_amount']   ?? $current['total_amount'],
                    $data['payment_date']  ?? date('Y-m-d'),
                    $data['payment_reference'] ?? null,
                    $id,
                ];
                break;
            case 'en_disputa':
                $params = [$data['dispute_notes'] ?? null, $id];
                break;
            default:
                $params = [$id];
                break;
        }

        sqlStatement($sql, $params);
        return true;
    }

    /**
     * Elimina un lote y sus ítems.
     * Solo se permite mientras el lote está en 'borrador' o 'armado'.
     */
    public function delete(int $id): bool
    {
        $current = $this->findById($id);
        if ($current === null) {
            return false;
        }
        if (!in_array($current['status'], ['borrador', 'armado'], true)) {
            return false;
        }

        sqlStatement("DELETE FROM covl_settlement_items WHERE batch_id = ?", [$id]);
        sqlStatement("DELETE FROM covl_settlement_batches WHERE id = ?", [$id]);
        return true;
    }

    /**
     * Lista los ítems de un lote.
     *
     * @return array[]
     */
    public function items(int $batchId): array
    {
        $res     = sqlStatement(
            "SELECT i.*,
                    CONCAT(p.fname, ' ', p.lname) AS patient_name,
                    b.date                        AS billing_date,
                    b.code_text                   AS billing_code_text
             FROM covl_settlement_items i
             LEFT JOIN patient_data p ON p.pid = i.pid
             LEFT JOIN billing b      ON b.id  = i.billing_id
             WHERE i.batch_id = ?
             ORDER BY i.id ASC",
            [$batchId]
        );
        $results = [];
        while ($row = sqlFetchArray($res)) {
            $results[] = $row;
        }
        return $results;
    }

    /**
     * Agrega una prestación (billing) a un lote y recalcula totales.
     *
     * @return int ID del ítem creado
     * @throws \RuntimeException si el lote no está editable o la prestación ya está incluida
     */
    public function addItem(array $data): int
    {
        $batchId  = (int) $data['batch_id'];
        $billingId = (int) $data['billing_id'];

        $batch = $this->findById($batchId);
        if ($batch === null) {
            throw new \RuntimeException(xl('Lote no encontrado'));
        }
        if (!in_array($batch['status'], ['borrador', 'armado'], true)) {
            throw new \RuntimeException(xl('El lote no admite agregar ítems en su estado actual'));
        }

        $existing = sqlQuery(
            "SELECT id FROM covl_settlement_items WHERE batch_id = ? AND billing_id = ? AND attempt_number = 1 LIMIT 1",
            [$batchId, $billingId]
        );
        if ($existing) {
            throw new \RuntimeException(xl('La prestación ya está incluida en este lote'));
        }

        $billing = sqlQuery(
            "SELECT id, pid, encounter, code_type, code, code_text, fee, date
             FROM billing WHERE id = ? LIMIT 1",
            [$billingId]
        );
        if ($billing === null) {
            throw new \RuntimeException(xl('Prestación de facturación no encontrada'));
        }

        $itemId = (int) sqlInsert(
            "INSERT INTO covl_settlement_items
               (batch_id, billing_id, pid, encounter_id, code_type, code, fee, currency, attempt_number)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)",
            [
                $batchId,
                $billingId,
                (int) $billing['pid'],
                (int) $billing['encounter'],
                $billing['code_type'],
                $billing['code'],
                (float) $billing['fee'],
                $batch['currency'],
            ]
        );

        $this->recalcTotals($batchId);
        return $itemId;
    }

    /**
     * Quita un ítem de un lote y recalcula totales.
     */
    public function removeItem(int $itemId, int $batchId): bool
    {
        $batch = $this->findById($batchId);
        if ($batch === null) {
            return false;
        }
        if (!in_array($batch['status'], ['borrador', 'armado'], true)) {
            return false;
        }

        sqlStatement("DELETE FROM covl_settlement_items WHERE id = ? AND batch_id = ?", [$itemId, $batchId]);
        $this->recalcTotals($batchId);
        return true;
    }

    /**
     * Actualiza el estado de un ítem dentro del lote.
     *
     * @param array $data  item_status, debit_reason, debit_amount, batch_id
     */
    public function updateItemStatus(int $itemId, array $data): bool
    {
        $status = $data['item_status'] ?? '';
        if (!in_array($status, self::ITEM_STATUSES, true)) {
            return false;
        }

        $batchId = (int) ($data['batch_id'] ?? 0);
        $batch   = $this->findById($batchId);
        if ($batch === null) {
            return false;
        }

        sqlStatement(
            "UPDATE covl_settlement_items SET
               item_status   = ?,
               debit_reason  = ?,
               debit_amount  = ?
             WHERE id = ? AND batch_id = ?",
            [
                $status,
                $data['debit_reason'] ?? null,
                isset($data['debit_amount']) && $data['debit_amount'] !== '' ? (float) $data['debit_amount'] : null,
                $itemId,
                $batchId,
            ]
        );
        return true;
    }

    /**
     * Recalcula los totales denormalizados de un lote (items y monto).
     */
    public function recalcTotals(int $batchId): void
    {
        $row = sqlQuery(
            "SELECT COUNT(*) AS total_items, COALESCE(SUM(fee), 0) AS total_amount
             FROM covl_settlement_items
             WHERE batch_id = ?",
            [$batchId]
        );
        sqlStatement(
            "UPDATE covl_settlement_batches
             SET total_items = ?, total_amount = ?
             WHERE id = ?",
            [
                (int) ($row['total_items'] ?? 0),
                (float) ($row['total_amount'] ?? 0),
                $batchId,
            ]
        );
    }

    /**
     * Genera el próximo número de lote.
     *
     * Formato: {PAIS}-{ID_FINANCIADOR}-{YYYYMM}-{NNN}
     * Ejemplo:  AR-5-202608-001
     */
    public function nextBatchNumber(string $countryCode, int $insurerId): string
    {
        $country = strtoupper($countryCode) ?: 'AR';
        $ym      = date('Ym');
        $pattern = $country . '-' . $insurerId . '-' . $ym . '-%';

        $row = sqlQuery(
            "SELECT COUNT(*) AS total FROM covl_settlement_batches WHERE batch_number LIKE ?",
            [$pattern]
        );
        $seq = (int) ($row['total'] ?? 0) + 1;
        return sprintf('%s-%d-%s-%03d', $country, $insurerId, $ym, $seq);
    }

    /**
     * Lista prestaciones facturadas (billing) candidatas para incluir en un lote.
     *
     * @param array $filters  period_from, period_to (Y-m-d), q (paciente/código),
     *                        batch_id (excluye ítems ya incluidos), limit
     *
     * @return array[]
     */
    public function candidateBillings(array $filters = []): array
    {
        $where  = ['b.activity = 1', 'b.fee > 0'];
        $params = [];

        if (!empty($filters['period_from'])) {
            $where[]  = 'DATE(b.date) >= ?';
            $params[] = $filters['period_from'];
        }
        if (!empty($filters['period_to'])) {
            $where[]  = 'DATE(b.date) <= ?';
            $params[] = $filters['period_to'];
        }
        if (!empty($filters['q'])) {
            $where[]  = '(b.code LIKE ? OR b.code_text LIKE ? OR CONCAT(p.fname, " ", p.lname) LIKE ?)';
            $like     = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if (!empty($filters['batch_id'])) {
            $where[]  = 'b.id NOT IN (SELECT billing_id FROM covl_settlement_items WHERE batch_id = ?)';
            $params[] = (int) $filters['batch_id'];
        }

        $limit = isset($filters['limit']) ? (int) $filters['limit'] : 50;

        $sql = "SELECT b.id,
                       b.date,
                       b.pid,
                       b.encounter,
                       b.code_type,
                       b.code,
                       b.code_text,
                       b.fee,
                       b.modifier,
                       CONCAT(p.fname, ' ', p.lname) AS patient_name
                FROM billing b
                LEFT JOIN patient_data p ON p.pid = b.pid
                WHERE " . implode(' AND ', $where) . "
                ORDER BY b.date DESC
                LIMIT {$limit}";

        $res     = sqlStatement($sql, $params);
        $results = [];
        while ($row = sqlFetchArray($res)) {
            $results[] = $row;
        }
        return $results;
    }
}
