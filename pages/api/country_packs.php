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

// Session null-safety: verificar que $session esté disponible antes de usarlo
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
// El JS envía el token tanto en el header X-CSRF-Token como en el body csrf_token.
// Nos fijamos primero en el body (más confiable para JSON), luego en el header.
if ($action === 'install' || $action === 'reimport') {
    $receivedBodyToken = $body['csrf_token'] ?? '';
    $receivedPostToken = $_POST['csrf_token'] ?? '';
    $receivedHeaderToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    // Loguear valores de depuración en error_log
    error_log('[covl] CSRF debug - body: "' . $receivedBodyToken . '", post: "' . $receivedPostToken . '", header: "' . $receivedHeaderToken . '"');

    $csrfToken = $receivedBodyToken
        ?? $receivedPostToken
        ?? $receivedHeaderToken;

    // Validar token CSRF
    if (!CsrfCompat::verifyCsrfToken($csrfToken)) {
        // Respuesta de diagnóstico para depurar
        $diag = [
            'csrf_error' => 'Token CSRF inválido',
            'received_body_token' => $receivedBodyToken ? 'PRESENT' : 'MISSING',
            'received_post_token' => $receivedPostToken ? 'PRESENT' : 'MISSING',
            'received_header_token' => $receivedHeaderToken ? 'PRESENT' : 'MISSING',
            'note' => 'Si received_body_token es MISSING, el JSON body no se parsea correctamente'
        ];
        http_response_code(403);
        echo json_encode($diag, JSON_UNESCAPED_UNICODE);
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
        // Loguear éxito con detalles del resultado para depuración
        error_log('[covl] Import success: code=' . $code . ' result=' . print_r($result, true));
        covl_json(['ok' => true, 'data' => $result]);
    } catch (\Throwable $e) {
        // Capturar error completo incluyendo el de MySQL
        $errorMsg = 'Error al importar el paquete ' . $code . ': ' . $e->getMessage();
        if (method_exists($e, 'getPrevious')) {
            $prev = $e->getPrevious();
            if ($prev !== null) {
                $errorMsg .= ' - Caused by: ' . $prev->getMessage();
            }
        }
        error_log('[covl] ERROR importing country pack ' . $code . ': ' . $errorMsg . ' in ' . $e->getFile() . ':' . $e->getLine());
        // Respuesta de error detallada para diagnóstico
        $errorDetail = [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];
        $prev = $e->getPrevious();
        if ($prev !== null && method_exists($prev, 'getCode')) {
            $errorDetail['errno'] = $prev->getCode();
        }
        covl_json(['error' => $errorDetail], 500);
    }
}

// helpers
function covl_json(mixed $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}