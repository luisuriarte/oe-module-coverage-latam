<?php

/**
 * oe-module-coverage-latam — API: Reglas de Frecuencia
 *
 * Endpoint REST minimalista que sirve JSON para el CRUD de covl_frequency_rules.
 *
 * Acciones:
 *   GET  ?action=list    [&country_code=AR] [&insurance_company_id=0] [&code_type=] [&code=] [&severity=] [&active=1]
 *   GET  ?action=get&id=N
 *   POST action=create
 *   POST action=update&id=N
 *   POST action=toggle&id=N
 *   POST action=delete&id=N
 *
 * @package   OpenEMR\Modules\CoverageLatam
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$ignoreAuth = false;

require_once __DIR__ . '/../../../../../globals.php';
require_once $GLOBALS['srcdir'] . '/api.inc.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Modules\CoverageLatam\CsrfCompat;
use OpenEMR\Modules\CoverageLatam\Repository\FrequencyRulesRepository;

if (!isset($_SESSION['authUserID'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$action = $_REQUEST['action'] ?? '';
$repo   = new FrequencyRulesRepository();

function covl_freq_json(mixed $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (in_array($action, ['create', 'update', 'toggle', 'delete'], true)) {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!CsrfCompat::verifyCsrfToken($token)) {
        covl_freq_json(['error' => 'Token CSRF inválido'], 403);
    }
    if (!AclMain::aclCheckCore('admin', 'docs')) {
        covl_freq_json(['error' => 'Sin permisos para modificar reglas'], 403);
    }
}

try {
    switch ($action) {

        case 'list':
            $filters = [
                'country_code'         => $_GET['country_code']         ?? '',
                'insurance_company_id' => $_GET['insurance_company_id'] ?? '',
                'code_type'            => $_GET['code_type']            ?? '',
                'code'                 => $_GET['code']                 ?? '',
                'severity'             => $_GET['severity']             ?? '',
                'active'               => $_GET['active']               ?? '',
                'limit'                => min((int) ($_GET['limit']  ?? 50), 500),
                'offset'               => max((int) ($_GET['offset'] ?? 0), 0),
            ];
            $rows  = $repo->list($filters);
            $total = $repo->count($filters);
            covl_freq_json(['data' => $rows, 'total' => $total, 'filters' => $filters]);

        case 'get':
            $id  = (int) ($_GET['id'] ?? 0);
            $row = $repo->findById($id);
            if ($row === null) {
                covl_freq_json(['error' => "Regla {$id} no encontrada"], 404);
            }
            covl_freq_json($row);

        case 'create':
            $data = array_merge($_POST, []);
            if (empty($data) || !isset($data['code_type'])) {
                $body = file_get_contents('php://input');
                $data = json_decode($body, true) ?: [];
            }
            if (empty($data['code_type']) || empty($data['code']) || empty($data['insurance_company_id']) || !isset($data['min_interval_days'])) {
                covl_freq_json(['error' => 'Campos requeridos: insurance_company_id, code_type, code, min_interval_days'], 422);
            }
            $newId = $repo->create($data);
            covl_freq_json(['success' => true, 'id' => $newId], 201);

        case 'update':
            $id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
            $row = $repo->findById($id);
            if ($row === null) {
                covl_freq_json(['error' => "Regla {$id} no encontrada"], 404);
            }
            $data = array_merge($_POST, []);
            if (empty($data) || !isset($data['code_type'])) {
                $body = file_get_contents('php://input');
                $data = json_decode($body, true) ?: [];
            }
            if (empty($data['code_type']) || empty($data['code']) || empty($data['insurance_company_id']) || !isset($data['min_interval_days'])) {
                covl_freq_json(['error' => 'Campos requeridos: insurance_company_id, code_type, code, min_interval_days'], 422);
            }
            $repo->update($id, $data);
            covl_freq_json(['success' => true]);

        case 'toggle':
            $id     = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
            $active = $repo->toggleActive($id);
            covl_freq_json(['success' => true, 'active' => $active]);

        case 'delete':
            $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
            if (!$repo->findById($id)) {
                covl_freq_json(['error' => "Regla {$id} no encontrada"], 404);
            }
            $repo->delete($id);
            covl_freq_json(['success' => true]);

        default:
            covl_freq_json(['error' => "Acción desconocida: {$action}"], 400);
    }
} catch (\Throwable $e) {
    error_log('[covl] api/frequency_rules.php error: ' . $e->getMessage());
    covl_freq_json(['error' => 'Error interno del servidor'], 500);
}
