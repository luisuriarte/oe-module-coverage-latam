<?php

/**
 * oe-module-coverage-latam — API: Reglas de Autorización
 *
 * Endpoint REST minimalista que sirve JSON para el CRUD de covl_auth_rules.
 * Todas las mutaciones requieren un CSRF token válido de OpenEMR.
 *
 * Acciones:
 *   GET  ?action=list    [&country_code=AR] [&insurance_company_id=0] [&code_type=] [&code=] [&active=1] [&limit=50] [&offset=0]
 *   GET  ?action=get&id=N
 *   POST action=create   (body JSON o form-data con los campos de la regla)
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
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Modules\CoverageLatam\CsrfCompat;
use OpenEMR\Modules\CoverageLatam\Repository\AuthRulesRepository;

$session = SessionWrapperFactory::getInstance()->getWrapper();
if (empty($session->get('authUserID'))) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => xl('No autenticado')]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_REQUEST['action'] ?? '';
$repo   = new AuthRulesRepository();

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
    // ACL: solo admins pueden modificar reglas
    if (!AclMain::aclCheckCore('admin', 'docs')) {
        covl_json(['error' => xl('Sin permisos para modificar reglas')], 403);
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
                'country_code'         => $_GET['country_code']         ?? '',
                'insurance_company_id' => $_GET['insurance_company_id'] ?? '',
                'code_type'            => $_GET['code_type']            ?? '',
                'code'                 => $_GET['code']                 ?? '',
                'active'               => $_GET['active']               ?? '',
                'limit'                => min((int) ($_GET['limit']  ?? 50), 500),
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
                covl_json(['error' => xl('Regla no encontrada')], 404);
            }
            covl_json($row);

        // -----------------------------------------------------------------------
        case 'create':
            $data = array_merge($_POST, []); // form-data o JSON
            // Aceptar también body JSON
            if (empty($data) || !isset($data['code_type'])) {
                $body = file_get_contents('php://input');
                $data = json_decode($body, true) ?: [];
            }
            // Validación mínima
            if (empty($data['code_type']) || empty($data['auth_mode']) || empty($data['insurance_company_id'])) {
                covl_json(['error' => xl('Campos requeridos: insurance_company_id, code_type, auth_mode')], 422);
            }
            $newId = $repo->create($data);
            covl_json(['success' => true, 'id' => $newId], 201);

        // -----------------------------------------------------------------------
        case 'update':
            $id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
            $row = $repo->findById($id);
            if ($row === null) {
                covl_json(['error' => xl('Regla no encontrada')], 404);
            }
            $data = array_merge($_POST, []);
            if (empty($data) || !isset($data['code_type'])) {
                $body = file_get_contents('php://input');
                $data = json_decode($body, true) ?: [];
            }
            if (empty($data['code_type']) || empty($data['auth_mode']) || empty($data['insurance_company_id'])) {
                covl_json(['error' => xl('Campos requeridos: insurance_company_id, code_type, auth_mode')], 422);
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
                covl_json(['error' => xl('Regla no encontrada')], 404);
            }
            $repo->delete($id);
            covl_json(['success' => true]);

        // -----------------------------------------------------------------------
        default:
            covl_json(['error' => xl('Acción desconocida')], 400);
    }
} catch (\Throwable $e) {
    error_log('[covl] api/auth_rules.php error: ' . $e->getMessage());
    covl_json(['error' => xl('Error interno del servidor')], 500);
}
