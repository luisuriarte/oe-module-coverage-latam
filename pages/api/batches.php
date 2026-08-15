<?php

/**
 * oe-module-coverage-latam — API: Lotes de Liquidación
 *
 * Endpoint REST minimalista que sirve JSON para el CRUD de
 * covl_settlement_batches y covl_settlement_items.
 * Todas las mutaciones requieren un CSRF token válido de OpenEMR.
 *
 * Acciones:
 *   GET  ?action=list          [&insurance_company_id=] [&facility_id=] [&status=] [&period_from=] [&period_to=] [&search=] [&limit=50] [&offset=0]
 *   GET  ?action=get&id=N        → lote + ítems
 *   GET  ?action=facilities      → lista de sedes activas
 *   GET  ?action=billings&batch_id=N[&period_from=][&period_to=][&q=] → prestaciones candidatas
 *   POST action=create   (body JSON o form-data con los campos del lote)
 *   POST action=update   &id=N   (solo estados borrador/armado)
 *   POST action=transition&id=N  {status, paid_amount, payment_date, payment_reference, dispute_notes}
 *   POST action=delete   &id=N   (solo estados borrador/armado)
 *   POST action=add_item         {batch_id, billing_id}
 *   POST action=remove_item      {item_id, batch_id}
 *   POST action=item_status      {item_id, batch_id, item_status, debit_reason, debit_amount}
 *
 * @package   OpenEMR\Modules\CoverageLatam
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// ---------------------------------------------------------------------------
// Bootstrap OpenEMR — camino estándar para páginas de módulos custom
// ---------------------------------------------------------------------------
$ignoreAuth = false; // Requiere sesión activa

require_once __DIR__ . '/../../../../../globals.php';
require_once $GLOBALS['srcdir'] . '/api.inc.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Modules\CoverageLatam\CsrfCompat;
use OpenEMR\Modules\CoverageLatam\Repository\SettlementBatchRepository;

// Seguridad: verificar sesión activa
if (!isset($_SESSION['authUserID'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => xl('No autenticado')]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_REQUEST['action'] ?? '';
$repo   = new SettlementBatchRepository();

// ---------------------------------------------------------------------------
// Helper: salida JSON y fin
// ---------------------------------------------------------------------------
function covl_json(mixed $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ---------------------------------------------------------------------------
// Verificar CSRF en toda operación de escritura
// ---------------------------------------------------------------------------
if (in_array($action, ['create', 'update', 'transition', 'delete', 'add_item', 'remove_item', 'item_status'], true)) {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!CsrfCompat::verifyCsrfToken($token)) {
        covl_json(['error' => xl('Token CSRF inválido')], 403);
    }
    // ACL: solo admins pueden modificar lotes
    if (!AclMain::aclCheckCore('admin', 'docs')) {
        covl_json(['error' => xl('Sin permisos para modificar lotes')], 403);
    }
}

// ---------------------------------------------------------------------------
// Lectura del body (form-data o JSON)
// ---------------------------------------------------------------------------
function covl_body(): array
{
    $data = array_merge($_POST, []);
    if (empty($data)) {
        $body = file_get_contents('php://input');
        $json = json_decode($body, true);
        if (is_array($json)) {
            $data = $json;
        }
    }
    return $data;
}

// ---------------------------------------------------------------------------
// Router
// ---------------------------------------------------------------------------
try {
    switch ($action) {

        // -----------------------------------------------------------------------
        case 'list':
            $filters = [
                'insurance_company_id' => $_GET['insurance_company_id'] ?? '',
                'facility_id'          => $_GET['facility_id']          ?? '',
                'status'               => $_GET['status']               ?? '',
                'period_from'          => $_GET['period_from']          ?? '',
                'period_to'            => $_GET['period_to']            ?? '',
                'search'               => $_GET['search']               ?? '',
                'limit'                => min((int) ($_GET['limit']  ?? 100), 500),
                'offset'               => max((int) ($_GET['offset'] ?? 0), 0),
            ];
            $rows  = $repo->list($filters);
            $total = $repo->count($filters);
            covl_json(['data' => $rows, 'total' => $total, 'filters' => $filters]);

        // -----------------------------------------------------------------------
        case 'get':
            $id  = (int) ($_GET['id'] ?? 0);
            $row = $repo->findById($id);
            if ($row === null) {
                covl_json(['error' => xl('Lote no encontrado')], 404);
            }
            $row['items'] = $repo->items($id);
            covl_json($row);

        // -----------------------------------------------------------------------
        case 'facilities':
            $facilities = sqlStatement("SELECT id, name FROM facility ORDER BY name ASC");
            $list       = [];
            while ($f = sqlFetchArray($facilities)) {
                $list[] = ['id' => (int) $f['id'], 'name' => $f['name']];
            }
            covl_json(['data' => $list]);

        // -----------------------------------------------------------------------
        case 'billings':
            $filters = [
                'period_from' => $_GET['period_from'] ?? '',
                'period_to'   => $_GET['period_to']   ?? '',
                'q'           => $_GET['q']           ?? '',
                'batch_id'    => $_GET['batch_id']    ?? '',
                'limit'       => min((int) ($_GET['limit'] ?? 50), 200),
            ];
            covl_json(['data' => $repo->candidateBillings($filters)]);

        // -----------------------------------------------------------------------
        case 'create':
            $data = covl_body();
            if (empty($data['insurance_company_id']) || empty($data['period_from']) || empty($data['period_to'])) {
                covl_json(['error' => xl('Campos requeridos: insurance_company_id, period_from, period_to')], 422);
            }
            $data['created_by'] = (int) ($_SESSION['authUserID'] ?? 0) ?: null;
            // País activo de la configuración (por defecto AR)
            $conf = sqlQuery("SELECT country_code FROM covl_config WHERE facility_id = 0 LIMIT 1");
            $data['country_code'] = $conf['country_code'] ?? 'AR';
            $newId = $repo->create($data);
            covl_json(['success' => true, 'id' => $newId], 201);

        // -----------------------------------------------------------------------
        case 'update':
            $id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
            if (!$repo->findById($id)) {
                covl_json(['error' => xl('Lote no encontrado')], 404);
            }
            $data = covl_body();
            if (empty($data['insurance_company_id']) || empty($data['period_from']) || empty($data['period_to'])) {
                covl_json(['error' => xl('Campos requeridos: insurance_company_id, period_from, period_to')], 422);
            }
            if (!$repo->update($id, $data)) {
                covl_json(['error' => xl('El lote no admite edición en su estado actual')], 409);
            }
            covl_json(['success' => true]);

        // -----------------------------------------------------------------------
        case 'transition':
            $id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
            if (!$repo->findById($id)) {
                covl_json(['error' => xl('Lote no encontrado')], 404);
            }
            $data   = covl_body();
            $status = $data['status'] ?? '';
            if (!in_array($status, SettlementBatchRepository::STATUSES, true)) {
                covl_json(['error' => xl('Estado inválido')], 422);
            }
            if (!$repo->transition($id, $status, $data)) {
                covl_json(['error' => xl('Transición de estado no permitida')], 409);
            }
            covl_json(['success' => true]);

        // -----------------------------------------------------------------------
        case 'delete':
            $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
            if (!$repo->findById($id)) {
                covl_json(['error' => xl('Lote no encontrado')], 404);
            }
            if (!$repo->delete($id)) {
                covl_json(['error' => xl('El lote no admite eliminación en su estado actual')], 409);
            }
            covl_json(['success' => true]);

        // -----------------------------------------------------------------------
        case 'add_item':
            $data = covl_body();
            if (empty($data['batch_id']) || empty($data['billing_id'])) {
                covl_json(['error' => xl('Campos requeridos: batch_id, billing_id')], 422);
            }
            $itemId = $repo->addItem($data);
            covl_json(['success' => true, 'id' => $itemId], 201);

        // -----------------------------------------------------------------------
        case 'remove_item':
            $data = covl_body();
            if (empty($data['item_id']) || empty($data['batch_id'])) {
                covl_json(['error' => xl('Campos requeridos: item_id, batch_id')], 422);
            }
            if (!$repo->removeItem((int) $data['item_id'], (int) $data['batch_id'])) {
                covl_json(['error' => xl('El lote no admite quitar ítems en su estado actual')], 409);
            }
            covl_json(['success' => true]);

        // -----------------------------------------------------------------------
        case 'item_status':
            $data = covl_body();
            if (empty($data['item_id']) || empty($data['batch_id'])) {
                covl_json(['error' => xl('Campos requeridos: item_id, batch_id')], 422);
            }
            if (!$repo->updateItemStatus((int) $data['item_id'], $data)) {
                covl_json(['error' => xl('Estado de ítem inválido')], 422);
            }
            covl_json(['success' => true]);

        // -----------------------------------------------------------------------
        default:
            covl_json(['error' => xl('Acción desconocida')], 400);
    }
} catch (\RuntimeException $e) {
    covl_json(['error' => $e->getMessage()], 409);
} catch (\Throwable $e) {
    error_log('[covl] api/batches.php error: ' . $e->getMessage());
    covl_json(['error' => xl('Error interno del servidor')], 500);
}
