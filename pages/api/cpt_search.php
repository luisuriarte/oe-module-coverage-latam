<?php

/**
 * oe-module-coverage-latam — API: Búsqueda de códigos CPT4
 *
 * Endpoint de solo lectura para autocompletado de códigos CPT/HCPCS
 * desde los modales de autorización y frecuencia.
 *
 * GET ?action=search&q=texto&limit=15
 *
 * @package   OpenEMR\Modules\CoverageLatam
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// ---------------------------------------------------------------------------
// Bootstrap OpenEMR
// ---------------------------------------------------------------------------
$ignoreAuth = false; // Requiere sesión activa

require_once __DIR__ . '/../../../../../globals.php';
require_once $GLOBALS['srcdir'] . '/api.inc.php';

// ---------------------------------------------------------------------------
// Verificar sesión
// ---------------------------------------------------------------------------
$authUserId = null;
if (is_object($session) && method_exists($session, 'get')) {
    $authUserId = $session->get('authUserID');
}
if (empty($authUserId)) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => xl('No autenticado')]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// ---------------------------------------------------------------------------
// Helper: salida JSON y fin
// ---------------------------------------------------------------------------
function covl_cpt_json(mixed $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ---------------------------------------------------------------------------
// Whitelist de idiomas → tabla/columnas (nunca construir nombres desde input)
// 'es' => cpt_codes_es  (code, short_description, medium_description)
// 'en' => cpt_codes     (cpt_code AS code, short_description, NULL AS medium_description)
// 'pt' => cpt_codes_pt  (futuro)
// ---------------------------------------------------------------------------
function getCptTables(): array
{
    return [
        'es' => [
            'table'       => 'cpt_codes_es',
            'colCode'     => 'code',
            'colShort'    => 'short_description',
            'colMedium'   => 'medium_description',
            'hasMedium'   => true,
        ],
        // 'en' => [
        //     'table'     => 'cpt_codes',
        //     'colCode'   => 'cpt_code',
        //     'colShort'  => 'short_description',
        //     'colMedium' => null,           // cpt_codes no tiene medium_description
        //     'hasMedium' => false,
        // ],
        // 'pt' => [
        //     'table'     => 'cpt_codes_pt',
        //     'colCode'   => 'code',
        //     'colShort'  => 'short_description',
        //     'colMedium' => 'medium_description',
        //     'hasMedium' => true,
        // ],
    ];
}

// ---------------------------------------------------------------------------
// Función principal de búsqueda
// ---------------------------------------------------------------------------
function searchCptCodes(string $lang, string $query, int $limit): array
{
    $tables = getCptTables();

    if (!isset($tables[$lang])) {
        covl_cpt_json(['error' => xl('Idioma no soportado')], 400);
    }

    $tbl  = $tables[$lang];
    $q    = trim($query);

    if ($q === '') {
        return [];
    }

    // Parámetros seguros
    $params = [];
    $limit  = max(1, min($limit, 25));

    // Construir SELECT con alias normalizado
    $selectCols = [
        $tbl['colCode'] . ' AS code',
        $tbl['colShort'] . ' AS short_description',
    ];
    if ($tbl['hasMedium']) {
        $selectCols[] = $tbl['colMedium'] . ' AS medium_description';
    } else {
        $selectCols[] = 'NULL AS medium_description';
    }

    $selectCols[] = "'" . $tbl['table'] . "' AS source_table";

    // Coincidencia por código (prefijo) y por descripción
    // Prioridad: código exacto > código con prefijo > descripción
    $prefix  = $q . '%';
    $pattern = '%' . $q . '%';

    $whereClauses = [
        $tbl['colCode']  . ' LIKE ?',
        $tbl['colShort'] . ' LIKE ?',
    ];
    $params[] = $prefix;   // code LIKE 'q%'
    $params[] = $pattern;  // short_description LIKE '%q%'

    // Solo incluir medium_description si la tabla la tiene
    if ($tbl['hasMedium']) {
        $whereClauses[] = $tbl['colMedium'] . ' LIKE ?';
        $params[] = $pattern;  // medium_description LIKE '%q%'
    }

    $sql = "SELECT " . implode(', ', $selectCols) . "
            FROM " . $tbl['table'] . "
            WHERE (" . implode(' OR ', $whereClauses) . ")
            ORDER BY
                CASE
                    WHEN " . $tbl['colCode'] . " = ? THEN 0
                    WHEN " . $tbl['colCode'] . " LIKE ? THEN 1
                    ELSE 2
                END,
                " . $tbl['colCode'] . "
            LIMIT ?";

    $params[] = $q;      // code = 'q' (orden exacto)
    $params[] = $prefix; // code LIKE 'q%' (orden prefijo)
    $params[] = $limit;

    $res = sqlStatement($sql, $params);

    $results = [];
    while ($row = sqlFetchArray($res)) {
        $results[] = [
            'code'               => $row['code'],
            'short_description'  => $row['short_description'],
            'medium_description' => $row['medium_description'],
            'source_table'       => $row['source_table'],
        ];
    }

    return $results;
}

// ---------------------------------------------------------------------------
// Router (solo acción search)
// ---------------------------------------------------------------------------
$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'search':
            $q     = $_GET['q'] ?? '';
            $lang  = $_GET['lang'] ?? 'es';
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 15;
            $data  = searchCptCodes($lang, $q, $limit);
            covl_cpt_json(['data' => $data]);
            break;

        default:
            covl_cpt_json(['error' => xl('Acción desconocida')], 400);
    }
} catch (\Throwable $e) {
    error_log('[covl] api/cpt_search error: ' . $e->getMessage());
    covl_cpt_json(['error' => xl('Error interno del servidor')], 500);
}
