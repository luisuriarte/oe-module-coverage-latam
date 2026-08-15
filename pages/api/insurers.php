<?php

/**
 * oe-module-coverage-latam — API: Financiadores
 *
 * Endpoint auxiliar que devuelve la lista de financiadores (insurance_companies)
 * para poblar los selects de los formularios de reglas.
 *
 * GET ?country_code=AR   → [{id, name, cms_id}] financiadores activos
 * GET (sin filtro)       → todos los financiadores activos
 *
 * @package   OpenEMR\Modules\CoverageLatam
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$ignoreAuth = false;

require_once __DIR__ . '/../../../../globals.php';

if (!isset($_SESSION['authUserID'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// insurance_companies no tiene campo country_code propio en OpenEMR nativo.
// Si se filtra por país, se usa las reglas o adaptadores configurados.
// Por ahora retornamos todos los activos para simplicidad.
// El filtro por país queda para cuando se implemente el campo country en insurance_companies.
$sql = "SELECT id, name, cms_id, ins_type_code
        FROM insurance_companies
        WHERE inactive != 1
        ORDER BY name ASC
        LIMIT 500";

$res     = sqlStatement($sql);
$results = [['id' => 0, 'name' => '— Todos los financiadores (0 = genérico) —', 'cms_id' => '', 'ins_type_code' => '']];

while ($row = sqlFetchArray($res)) {
    $results[] = [
        'id'            => (int) $row['id'],
        'name'          => $row['name'],
        'cms_id'        => $row['cms_id'] ?? '',
        'ins_type_code' => $row['ins_type_code'] ?? '',
    ];
}

echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
