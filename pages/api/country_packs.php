<?php

/**
 * oe-module-coverage-latam — API: Paquetes de País
 *
 * Endpoint REST minimalista que sirve JSON para gestionar el catálogo
 * de paquetes de país del módulo (packs/*.json → covl_country_packs).
 *
 * Acciones:
 *   GET  ?action=catalog         → catálogo completo con estado instalado
 *   GET  ?action=list            → paquetes instalados (covl_country_packs)
 *   POST action=install&country_code=XX → instala/actualiza un paquete
 *
 * Las mutaciones requieren un CSRF token válido (OpenEMR).
 *
 * @package   OpenEMR\Modules\CoverageLatam
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$ignoreAuth = false;

require_once __DIR__ . '/../../../../../globals.php';
require_once $GLOBALS['srcdir'] . '/api.inc.php';

use OpenEMR\Modules\CoverageLatam\CsrfCompat;
use OpenEMR\Modules\CoverageLatam\Service\CountryPackCatalog;
use OpenEMR\Modules\CoverageLatam\Service\CountryPackImporter;

$authUserId = null;
if (is_object($session) && method_exists($session, 'get')) {
    $authUserId = $session->get('authUserID');
}
if (empty($authUserId)) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => xl('No autenticado')]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$action = $_REQUEST['action'] ?? '';

// ---------------------------------------------------------------------------
// Lectura del body — siempre parsear JSON primero, luego fallback a $_POST.
// Con Content-Type: application/json, $_POST suele estar vacío; al inverso
// (form-data), $_POST tiene los campos. Se unen para cubrir ambos casos.
// ---------------------------------------------------------------------------
$parsed = json_decode((string) file_get_contents('php://input'), true);
$body   = array_merge(is_array($parsed) ? $parsed : [], $_POST);

// Seguridad: verificar CSRF en mutaciones
if ($action === 'install' || $action === 'reimport') {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? ($body['csrf_token'] ?? '')
        ?? ($_POST['csrf_token'] ?? '');
    if (!CsrfCompat::verifyCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['error' => xl('Token CSRF inválido')], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$catalog = new CountryPackCatalog();

if ($action === 'catalog') {
    // Estado instalado por país (para marcar en la UI)
    $installedMap = [];
    $res = sqlStatement("SELECT country_code, version FROM covl_country_packs");
    while ($row = sqlFetchArray($res)) {
        $installedMap[$row['country_code']] = $row;
    }

    $items = [];
    foreach ($catalog->all() as $pack) {
        $code = strtoupper((string) $pack['country_code']);
        $items[] = [
            'country_code'      => $code,
            'name'              => $pack['name'] ?? $code,
            'version'           => $pack['version'] ?? '1.0.0',
            'description'       => $pack['description'] ?? '',
            'code_type'         => $pack['code_type'] ?? null,
            'auth_rules'        => count($pack['auth_rules'] ?? []),
            'frequency_rules'   => count($pack['frequency_rules'] ?? []),
            'code_maps'         => count($pack['code_maps'] ?? []),
            'installed'         => isset($installedMap[$code]),
            'installed_version' => $installedMap[$code]['version'] ?? null,
        ];
    }
    covl_json(['data' => $items]);
} elseif ($action === 'list') {
    $rows = [];
    $res  = sqlStatement("SELECT * FROM covl_country_packs ORDER BY name ASC");
    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
    }
    covl_json(['data' => $rows]);
} elseif ($action === 'install' || $action === 'reimport') {
    $code = strtoupper(trim((string) ($body['country_code'] ?? '')));
    if ($code === '' || $catalog->get($code) === null) {
        covl_json(['error' => xl('Paquete no encontrado en el catálogo') . ' (code=' . $code . ')'], 404);
    }
    try {
        $result = (new CountryPackImporter())->importCountryPack($code);
        covl_json(['ok' => true, 'data' => $result]);
    } catch (\Throwable $e) {
        covl_json(['error' => $e->getMessage()], 500);
    }
}

// helpers
function covl_json(mixed $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
