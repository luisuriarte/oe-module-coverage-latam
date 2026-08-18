<?php

/**
 * oe-module-coverage-latam — API: Búsqueda de códigos médicos
 *
 * Endpoint de solo lectura para autocompletado de códigos
 * desde los modales de autorización y frecuencia.
 *
 * GET ?action=search&code_type=CPT4&q=texto&limit=15
 *
 * Tipos soportados: CPT4, CDT, ICD10-PCS, SNOMED-PR
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
// Whitelist de code_type → tabla/columnas (nunca construir desde input)
// ---------------------------------------------------------------------------
function getCodeTables(): array
{
    return [
        'CPT4' => [
            'table'     => 'cpt_codes_es',
            'colCode'   => 'code',
            'colShort'  => 'short_description',
            'colMedium' => 'medium_description',
            'hasMedium' => true,
            'where'     => null,
            'label'     => 'CPT4',
        ],
        // 'CPT4_EN' => [
        //     'table'     => 'cpt_codes',
        //     'colCode'   => 'cpt_code',
        //     'colShort'  => 'short_description',
        //     'colMedium' => null,
        //     'hasMedium' => false,
        //     'where'     => null,
        //     'label'     => 'CPT4 (EN)',
        // ],
        'CDT' => [
            'table'     => 'cdt_codes',
            'colCode'   => 'cdt_code',
            'colShort'  => 'description',
            'colMedium' => null,
            'hasMedium' => false,
            'where'     => null,
            'label'     => 'Dental CDT Codes',
        ],
        'ICD10-PCS' => [
            'table'     => 'icd10_pcs_order_code',
            'colCode'   => 'pcs_code',
            'colShort'  => 'long_desc',
            'colMedium' => 'short_desc',
            'hasMedium' => true,
            'where'     => 'active = 1',
            'label'     => 'ICD10 Procedure/Service',
        ],
        'SNOMED-PR' => [
            'table'     => 'sct2_description',
            'colCode'   => 'conceptId',
            'colShort'  => 'term',
            'colMedium' => null,
            'hasMedium' => false,
            'where'     => "active = '1' AND effectiveTime > '2003-10-31' AND term LIKE '%(procedimiento)'",
            'label'     => 'SNOMED Procedure',
        ],
        'ODONTO' => [
            'table'     => 'odontologico',
            'colCode'   => 'codigo',
            'colShort'  => 'descripcion',
            'colMedium' => null,
            'hasMedium' => false,
            'where'     => null,
            'label'     => 'Odontologico',
        ],
    ];
}

// ---------------------------------------------------------------------------
// Función principal de búsqueda
// ---------------------------------------------------------------------------
function searchCodes(string $codeType, string $query, int $limit): array
{
    $tables = getCodeTables();

    if (!isset($tables[$codeType])) {
        covl_cpt_json(['error' => xl('Tipo de código no soportado')], 400);
    }

    $tbl = $tables[$codeType];
    $q   = trim($query);

    if ($q === '') {
        return [];
    }

    $params = [];
    $limit  = max(1, min($limit, 25));

    // SELECT con alias normalizado
    $selectCols = [
        $tbl['colCode'] . ' AS code',
        $tbl['colShort'] . ' AS short_description',
    ];
    if ($tbl['hasMedium']) {
        $selectCols[] = $tbl['colMedium'] . ' AS medium_description';
    } else {
        $selectCols[] = 'NULL AS medium_description';
    }
    $selectCols[] = "'" . $tbl['label'] . "' AS code_type_label";

    // WHERE: búsqueda por código (prefijo) o descripción
    $prefix  = $q . '%';
    $pattern = '%' . $q . '%';

    $whereClauses = [
        $tbl['colCode']  . ' LIKE ?',
        $tbl['colShort'] . ' LIKE ?',
    ];
    $params[] = $prefix;
    $params[] = $pattern;

    if ($tbl['hasMedium']) {
        $whereClauses[] = $tbl['colMedium'] . ' LIKE ?';
        $params[] = $pattern;
    }

    // Filtro adicional de la tabla (ej: active, procedimiento SNOMED)
    $sql = "SELECT " . implode(', ', $selectCols) . "
            FROM " . $tbl['table'] . "
            WHERE (" . implode(' OR ', $whereClauses) . ")" .
            (!empty($tbl['where']) ? " AND (" . $tbl['where'] . ")" : "") . "
            ORDER BY
                CASE
                    WHEN CAST(" . $tbl['colCode'] . " AS CHAR) = ? THEN 0
                    WHEN CAST(" . $tbl['colCode'] . " AS CHAR) LIKE ? THEN 1
                    ELSE 2
                END,
                " . $tbl['colCode'] . "
            LIMIT ?";

    $params[] = $q;
    $params[] = $prefix;
    $params[] = $limit;

    $res = sqlStatement($sql, $params);

    $results = [];
    while ($row = sqlFetchArray($res)) {
        $results[] = [
            'code'              => (string) $row['code'],
            'short_description' => $row['short_description'],
            'medium_description' => $row['medium_description'],
            'code_type_label'   => $row['code_type_label'],
        ];
    }

    return $results;
}

// ---------------------------------------------------------------------------
// Router
// ---------------------------------------------------------------------------
$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'search':
            $codeType = $_GET['code_type'] ?? 'CPT4';
            $q        = $_GET['q'] ?? '';
            $limit    = isset($_GET['limit']) ? (int) $_GET['limit'] : 15;
            $data     = searchCodes($codeType, $q, $limit);
            covl_cpt_json(['data' => $data]);
            break;

        default:
            covl_cpt_json(['error' => xl('Acción desconocida')], 400);
    }
} catch (\Throwable $e) {
    error_log('[covl] api/cpt_search error: ' . $e->getMessage());
    covl_cpt_json(['error' => xl('Error interno del servidor')], 500);
}
