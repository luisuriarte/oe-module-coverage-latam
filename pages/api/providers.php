<?php

/**
 * oe-module-coverage-latam — API: Convenios de Prestadores
 *
 * Endpoint REST minimalista que sirve JSON para el CRUD de covl_provider_coverage.
 * Todas las mutaciones requieren un CSRF token válido de OpenEMR.
 *
 * Acciones:
 *   GET  ?action=list          [&user_id=] [&insurance_company_id=] [&facility_id=] [&active=1] [&search=] [&limit=50] [&offset=0]
 *   GET  ?action=get&id=N
 *   GET  ?action=professionals   → lista de profesionales activos
 *   GET  ?action=facilities      → lista de sedes activas
 *   POST action=create   (body JSON o form-data con los campos del convenio)
 *   POST action=update   &id=N
 *   POST action=toggle   &id=N  (alterna active)
 *   POST action=delete   &id=N
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
use OpenEMR\Modules\CoverageLatam\Repository\ProviderCoverageRepository;

if (empty($_SESSION['authUserID'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => xl('No autenticado')]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_REQUEST['action'] ?? '';
$repo   = new ProviderCoverageRepository();

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
if (in_array($action, ['create', 'update', 'toggle', 'delete'], true)) {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!CsrfCompat::verifyCsrfToken($token)) {
        covl_json(['error' => xl('Token CSRF inválido')], 403);
    }
    // ACL: solo admins pueden modificar convenios
    if (!AclMain::aclCheckCore('admin', 'docs')) {
        covl_json(['error' => xl('Sin permisos para modificar convenios')], 403);
    }
}

// ---------------------------------------------------------------------------
// Router
// ---------------------------------------------------------------------------
try {
    switch ($action) {

        // -----------------------------------------------------------------------
        case 'list':
            $filters = [
                'user_id'              => $_GET['user_id']              ?? '',
                'insurance_company_id' => $_GET['insurance_company_id'] ?? '',
                'facility_id'          => $_GET['facility_id']          ?? '',
                'active'               => $_GET['active']               ?? '',
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
                covl_json(['error' => xl('Convenio no encontrado')], 404);
            }
            covl_json($row);

        // -----------------------------------------------------------------------
        case 'professionals':
            covl_json(['data' => $repo->listProfessionals()]);

        // -----------------------------------------------------------------------
        case 'facilities':
            covl_json(['data' => $repo->listFacilities()]);

        // -----------------------------------------------------------------------
        case 'create':
            $data = array_merge($_POST, []); // form-data o JSON
            if (empty($data) || !isset($data['user_id'])) {
                $body = file_get_contents('php://input');
                $data = json_decode($body, true) ?: [];
            }
            // Validación mínima
            if (empty($data['user_id']) || empty($data['insurance_company_id']) || empty($data['date_from'])) {
                covl_json(['error' => xl('Campos requeridos: user_id, insurance_company_id, date_from')], 422);
            }
            $newId = $repo->create($data);
            covl_json(['success' => true, 'id' => $newId], 201);

        // -----------------------------------------------------------------------
        case 'update':
            $id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
            $row = $repo->findById($id);
            if ($row === null) {
                covl_json(['error' => xl('Convenio no encontrado')], 404);
            }
            $data = array_merge($_POST, []);
            if (empty($data) || !isset($data['user_id'])) {
                $body = file_get_contents('php://input');
                $data = json_decode($body, true) ?: [];
            }
            if (empty($data['user_id']) || empty($data['insurance_company_id']) || empty($data['date_from'])) {
                covl_json(['error' => xl('Campos requeridos: user_id, insurance_company_id, date_from')], 422);
            }
            $repo->update($id, $data);
            covl_json(['success' => true]);

        // -----------------------------------------------------------------------
        case 'toggle':
            $id     = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
            $active = $repo->toggleActive($id);
            covl_json(['success' => true, 'active' => $active]);

        // -----------------------------------------------------------------------
        case 'delete':
            $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
            if (!$repo->findById($id)) {
                covl_json(['error' => xl('Convenio no encontrado')], 404);
            }
            $repo->delete($id);
            covl_json(['success' => true]);

        // -----------------------------------------------------------------------
        default:
            covl_json(['error' => xl('Acción desconocida')], 400);
    }
} catch (\Throwable $e) {
    error_log('[covl] api/providers.php error: ' . $e->getMessage());
    covl_json(['error' => xl('Error interno del servidor')], 500);
}
