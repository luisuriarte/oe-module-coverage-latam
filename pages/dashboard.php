<?php

/**
 * oe-module-coverage-latam — Dashboard Principal & CRUD de Configuración
 *
 * Interfaz unificada de gestión de Coberturas LATAM:
 * - Tab 1: Dashboard / Métricas
 * - Tab 2: Autorizaciones Previas
 * - Tab 3: Lotes de Liquidación
 * - Tab 4: Convenios de Prestadores
 * - Tab 5: Pack Paises
 * - Tab 6: Autorizaciones
 * - Tab 7: Frecuencias
 *
 * @package   OpenEMR\Modules\CoverageLatam
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/../../../../globals.php';
require_once $GLOBALS['srcdir'] . '/formatting.inc.php';

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Modules\CoverageLatam\CsrfCompat;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$activeTab = $_GET['tab'] ?? 'dashboard';
$allowedTabs = ['dashboard', 'authorizations', 'batches', 'providers', 'countries', 'auth_rules', 'freq_rules'];
if (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'dashboard';
}

$currentUserIsAdmin = AclMain::aclCheckCore('admin', 'docs');

// Consultas iniciales para las métricas del Dashboard
$pendingAuthsCount = 0;
$approvedAuthsCount = 0;
$activeBatchesCount = 0;
$activeProvidersCount = 0;

$resAuthPending = sqlQuery("SELECT COUNT(*) AS total FROM covl_authorizations WHERE status IN ('pendiente', 'en_auditoria')");
if ($resAuthPending) {
    $pendingAuthsCount = (int) $resAuthPending['total'];
}

$resAuthApproved = sqlQuery("SELECT COUNT(*) AS total FROM covl_authorizations WHERE status = 'aprobada'");
if ($resAuthApproved) {
    $approvedAuthsCount = (int) $resAuthApproved['total'];
}

$resBatches = sqlQuery("SELECT COUNT(*) AS total FROM covl_settlement_batches WHERE status IN ('borrador', 'armado', 'presentado')");
if ($resBatches) {
    $activeBatchesCount = (int) $resBatches['total'];
}

$resProviders = sqlQuery("SELECT COUNT(*) AS total FROM covl_provider_coverage WHERE active = 1");
if ($resProviders) {
    $activeProvidersCount = (int) $resProviders['total'];
}

// Lista de financiadores para los selectores modal
$insurersList = [];
$resIns = sqlStatement("SELECT id, name FROM insurance_companies WHERE id > 0 ORDER BY name ASC");
while ($rIns = sqlFetchArray($resIns)) {
    $insurersList[] = $rIns;
}

// Token CSRF y URL base del módulo (para los CRUD de Convenios y Lotes)
$csrfToken  = CsrfCompat::collectCsrfToken();
$moduleBase = $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-coverage-latam/pages';

// Profesionales y sedes para los filtros y formularios de Convenios / Lotes
$professionalsList = [];
$resProf = sqlStatement("SELECT u.id, u.username, u.fname, u.lname, u.specialty FROM users u WHERE u.authorized = 1 AND u.username IS NOT NULL AND u.username != '' ORDER BY u.lname, u.fname");
while ($rProf = sqlFetchArray($resProf)) {
    $professionalsList[] = $rProf;
}

$facilitiesList = [];
$resFac = sqlStatement("SELECT id, name FROM facility ORDER BY name ASC");
while ($rFac = sqlFetchArray($resFac)) {
    $facilitiesList[] = $rFac;
}

// Cargar paquetes de país instalados (para pills, FlagSelects y moneda por país)
$countryPacks = [];
$resPk = sqlStatement("SELECT country_code, name, currency_code, currency_name, currency_symbol FROM covl_country_packs ORDER BY name");
while ($rowPk = sqlFetchArray($resPk)) {
    $countryPacks[] = $rowPk;
}

// Cargar tipos de código disponibles (para los formularios de reglas)
$codeTypes = [];
$resCt = sqlStatement("SELECT ct_key, ct_label FROM code_types WHERE ct_active = 1 ORDER BY ct_label");
while ($rowCt = sqlFetchArray($resCt)) {
    $codeTypes[] = $rowCt;
}

// Moneda activa de la configuración (para precargar el lote)
$activeCountryCode = 'AR';
$resConf = sqlQuery("SELECT country_code FROM covl_config WHERE facility_id = 0 LIMIT 1");
if ($resConf && !empty($resConf['country_code'])) {
    $activeCountryCode = $resConf['country_code'];
}
$activeCurrency = [
    'code'   => 'USD',
    'name'   => 'Dólar',
    'symbol' => '$',
];
$resCur = sqlQuery(
    "SELECT currency_code, currency_name, currency_symbol FROM covl_country_packs WHERE country_code = ? LIMIT 1",
    [$activeCountryCode]
);
if ($resCur && !empty($resCur['currency_code'])) {
    $activeCurrency = [
        'code'   => $resCur['currency_code'],
        'name'   => $resCur['currency_name'] ?? $resCur['currency_code'],
        'symbol' => $resCur['currency_symbol'] ?? '',
    ];
}

// ---------------------------------------------------------------------------
// Helper: genera el markup de un FlagSelect personalizado con banderas.
// ---------------------------------------------------------------------------
function covl_flag_select(string $inputId, array $options, bool $required = false, string $extraClass = ''): string
{
    $html  = '<div class="covl-flag-select' . ($extraClass ? ' ' . $extraClass : '') . '" data-input-id="' . attr($inputId) . '">';
    $html .= '<button type="button" class="fs-trigger" aria-haspopup="listbox" aria-expanded="false">';
    $html .= '<span class="fs-flag fi"></span>';
    $html .= '<span class="fs-label text-muted">' . xlt('Seleccioná...') . '</span>';
    $html .= '<span class="fs-caret">▾</span>';
    $html .= '</button>';
    $html .= '<div class="fs-dropdown" role="listbox">';
    foreach ($options as $opt) {
        $code    = $opt['code'] ?? '';
        $fiClass = $code ? 'fi fi-' . strtolower($code) : 'fi';
        $html   .= '<div class="fs-option" role="option" data-value="' . attr($opt['value']) . '" data-code="' . attr($code) . '">';
        $html   .= '<span class="' . attr($fiClass) . '"></span>';
        $html   .= '<span class="fs-option-label">' . text($opt['label']) . '</span>';
        $html   .= '</div>';
    }
    $html .= '</div>';
    $html .= '<input type="hidden" id="' . attr($inputId) . '" value=""' . ($required ? ' data-required="1"' : '') . '>';
    $html .= '</div>';
    return $html;
}

// Opciones de país para los selects
$allCountryOpts = [['value' => '', 'label' => xlt('— Todos los países —'), 'code' => '']];
$reqCountryOpts = [['value' => '', 'label' => xlt('— Seleccioná —'),       'code' => '']];
foreach ($countryPacks as $p) {
    $opt = ['value' => $p['country_code'], 'label' => $p['country_code'] . ' — ' . $p['name'], 'code' => $p['country_code']];
    $allCountryOpts[] = $opt;
    $reqCountryOpts[] = $opt;
}

// Traducciones inyectadas en covlConfig.i18n (usadas por providers-crud.js y batches-crud.js)
$covlI18n = [
    // Comunes
    'loading'         => xlt('Cargando…'),
    'error_loading'   => xlt('Error al cargar los datos'),
    'records'         => xlt('registros'),
    'no_results'      => xlt('No se encontraron resultados'),
    'showing'         => xlt('Mostrando'),
    'of'              => xlt('de'),
    'edit'            => xlt('Editar'),
    'delete'          => xlt('Eliminar'),
    'error_fetch'     => xlt('Error al obtener el registro'),
    'error_save'      => xlt('Error al guardar'),
    'required_fields' => xlt('Complete los campos obligatorios'),
    'updated'         => xlt('Guardado correctamente'),
    'created'         => xlt('Creado correctamente'),
    'confirm_delete'  => xlt('¿Eliminar este registro?'),
    'deleted'         => xlt('Eliminado correctamente'),
    // Convenios
    'active'          => xlt('Activo'),
    'inactive'        => xlt('Inactivo'),
    'activated'       => xlt('Convenio activado'),
    'deactivated'     => xlt('Convenio desactivado'),
    'no_expiry'       => xlt('Sin vencimiento'),
    'expired'         => xlt('Vencido'),
    'expiring'        => xlt('Por vencer'),
    'current'         => xlt('Vigente'),
    'all_facilities'  => xlt('Todas las sedes'),
    'new_provider'    => xlt('Nuevo Convenio'),
    'edit_provider'   => xlt('Editar Convenio'),
    // Lotes: estados
    'status_borrador'       => xlt('Borrador'),
    'status_armado'         => xlt('Armado'),
    'status_presentado'     => xlt('Presentado'),
    'status_pagado_parcial' => xlt('Pagado parcial'),
    'status_pagado_total'   => xlt('Pagado total'),
    'status_en_disputa'     => xlt('En disputa'),
    'status_anulado'        => xlt('Anulado'),
    'item_incluido'         => xlt('Incluido'),
    'item_aprobado'         => xlt('Aprobado'),
    'item_rechazado'        => xlt('Rechazado'),
    'item_debitado'         => xlt('Debitado'),
    // Lotes: general
    'new_batch'             => xlt('Nuevo Lote'),
    'edit_batch'            => xlt('Editar Lote'),
    'batch'                 => xlt('Lote'),
    'items'                 => xlt('ítems'),
    'no_items'              => xlt('Sin prestaciones en el lote'),
    'attempt'               => xlt('Intento'),
    'approve'               => xlt('Aprobar'),
    'debit'                 => xlt('Debitar'),
    'reject'                => xlt('Rechazar'),
    'remove'                => xlt('Quitar'),
    'arm'                   => xlt('Armar'),
    'annul'                 => xlt('Anular'),
    'present'               => xlt('Presentar'),
    'register_payment'      => xlt('Registrar pago'),
    'dispute'               => xlt('Disputa'),
    'actions'               => xlt('Acciones'),
    'billing_added'         => xlt('Prestación agregada al lote'),
    'confirm_remove_item'   => xlt('¿Quitar esta prestación del lote?'),
    'item_updated'          => xlt('Ítem actualizado'),
    'prompt_reject_reason'  => xlt('Motivo del rechazo:'),
    'prompt_debit_reason'   => xlt('Motivo del débito:'),
    'prompt_debit_amount'   => xlt('Monto a debitar (0 para el monto total):'),
    'confirm_annul'         => xlt('¿Anular este lote? Esta acción no se puede revertir.'),
    'transition_armado'         => xlt('Lote armado'),
    'transition_presentado'     => xlt('Lote presentado'),
    'transition_anulado'        => xlt('Lote anulado'),
    'transition_pagado_parcial' => xlt('Pago parcial registrado'),
    'transition_pagado_total'   => xlt('Pago total registrado'),
    'transition_en_disputa'     => xlt('Lote marcado en disputa'),
    'paid_label'            => xlt('Pagado'),
    'add'                   => xlt('Agregar'),
    'no_billings'           => xlt('No se encontraron prestaciones'),
    // Países
    'countries_empty'   => xlt('No hay paquetes de país instalados. Agregá uno desde el catálogo.'),
    'rules_loaded'      => xlt('Cargadas'),
    'rules_pending'     => xlt('Sin reglas'),
    'update'            => xlt('Actualizar'),
    'installed'         => xlt('Instalado'),
    'not_installed'     => xlt('No instalado'),
    'auth_rules_short'  => xlt('aut.'),
    'freq_rules_short'  => xlt('frec.'),
    'code_maps_short'   => xlt('mapas'),
    'country_installed' => xlt('Paquete de país instalado'),
    'error_install'     => xlt('No se pudo instalar el paquete de país'),
    'reimport'          => xlt('Reimportar'),
    'reimport_title'    => xlt('Reimportar paquete de país'),
    'country_reimported'=> xlt('Paquete de país reimportado'),
    'error_reimport'    => xlt('No se pudo reimportar el paquete de país'),
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo xlt('Coberturas LATAM — Gestión'); ?></title>
    <?php Header::setupHeader(['bootstrap', 'fontawesome']); ?>
<link rel="stylesheet" href="<?php echo $moduleBase; ?>/assets/css/admin-rules.css">
    <link rel="stylesheet" href="<?php echo $moduleBase; ?>/assets/vendor/flag-icons/css/flag-icons.min.css">
    <link rel="stylesheet" href="<?php echo $moduleBase; ?>/assets/css/dashboard.css">
    <style>
        .covl-header-banner {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: #fff;
            border-radius: 0.5rem;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        .covl-card-stat {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: transform 0.15s ease-in-out;
        }
        .covl-card-stat:hover {
            transform: translateY(-2px);
        }
        .covl-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .nav-tabs .nav-link {
            font-weight: 500;
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            font-weight: 600;
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-3">

        <!-- Banner Superior -->
        <div class="covl-header-banner d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h3 class="mb-1 fw-bold">
                    <i class="fa-solid fa-hospital-user me-2"></i><?php echo xlt('Coberturas LATAM'); ?>
                </h3>
                <p class="mb-0 text-white-50">
                    <?php echo xlt('Gestión integral de autorizaciones previas, lotes de liquidación y convenios de prestadores'); ?>
                </p>
            </div>
            <div class="mt-2 mt-md-0">
                <span class="badge bg-light text-primary px-3 py-2 fw-semibold">OpenEMR 8+ Module</span>
            </div>
        </div>

        <!-- Mensajes de Notificación Session -->
        <?php if (isset($_SESSION['covl_msg'])): ?>
            <div class="alert alert-<?php echo text($_SESSION['covl_msg']['type']); ?> alert-dismissible fade show mb-4" role="alert">
                <i class="fa-solid fa-circle-info me-2"></i><?php echo text($_SESSION['covl_msg']['text']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['covl_msg']); ?>
        <?php endif; ?>

        <!-- Pestañas de Navegación Principal -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'dashboard' ? 'active' : ''; ?>" 
                   href="dashboard.php?tab=dashboard">
                    <i class="fa-solid fa-chart-line me-1"></i><?php echo xlt('Dashboard'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'authorizations' ? 'active' : ''; ?>" 
                   href="dashboard.php?tab=authorizations">
                    <i class="fa-solid fa-file-signature me-1"></i><?php echo xlt('Autorizaciones Previas'); ?>
                    <?php if ($pendingAuthsCount > 0): ?>
                        <span class="badge bg-warning text-dark ms-1"><?php echo $pendingAuthsCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'batches' ? 'active' : ''; ?>" 
                   href="dashboard.php?tab=batches">
                    <i class="fa-solid fa-boxes-stacked me-1"></i><?php echo xlt('Lotes de Liquidación'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'providers' ? 'active' : ''; ?>" 
                   href="dashboard.php?tab=providers">
                    <i class="fa-solid fa-user-doctor me-1"></i><?php echo xlt('Convenios Prestadores'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'countries' ? 'active' : ''; ?>" 
                   href="dashboard.php?tab=countries">
                    <i class="fa-solid fa-earth-americas me-1"></i><?php echo xlt('Países'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'auth_rules' ? 'active' : ''; ?>" 
                   href="dashboard.php?tab=auth_rules">
                    <i class="fa-solid fa-shield-halved me-1"></i><?php echo xlt('Autorizaciones'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $activeTab === 'freq_rules' ? 'active' : ''; ?>" 
                   href="dashboard.php?tab=freq_rules">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i><?php echo xlt('Frecuencia'); ?>
                </a>
            </li>
        </ul>

        <!-- Contenido según la pestaña activa -->
        <?php if ($activeTab === 'dashboard'): ?>

            <!-- Tarjetas de Métricas -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card covl-card-stat p-3">
                        <div class="d-flex align-items-center">
                            <div class="covl-stat-icon bg-warning bg-opacity-10 text-warning me-3">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <div class="text-muted small"><?php echo xlt('Autorizaciones Pendientes'); ?></div>
                                <div class="h4 mb-0 fw-bold"><?php echo $pendingAuthsCount; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card covl-card-stat p-3">
                        <div class="d-flex align-items-center">
                            <div class="covl-stat-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div>
                                <div class="text-muted small"><?php echo xlt('Autorizaciones Aprobadas'); ?></div>
                                <div class="h4 mb-0 fw-bold"><?php echo $approvedAuthsCount; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card covl-card-stat p-3">
                        <div class="d-flex align-items-center">
                            <div class="covl-stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <div>
                                <div class="text-muted small"><?php echo xlt('Lotes Activos'); ?></div>
                                <div class="h4 mb-0 fw-bold"><?php echo $activeBatchesCount; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card covl-card-stat p-3">
                        <div class="d-flex align-items-center">
                            <div class="covl-stat-icon bg-info bg-opacity-10 text-info me-3">
                                <i class="fa-solid fa-handshake"></i>
                            </div>
                            <div>
                                <div class="text-muted small"><?php echo xlt('Convenios Vigentes'); ?></div>
                                <div class="h4 mb-0 fw-bold"><?php echo $activeProvidersCount; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Últimas Autorizaciones Registradas -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fa-solid fa-list-check me-2 text-primary"></i><?php echo xlt('Últimas Solicitudes de Autorización'); ?>
                    </h5>
                    <a href="dashboard.php?tab=authorizations" class="btn btn-sm btn-outline-primary">
                        <?php echo xlt('Ver Todas'); ?>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th># ID</th>
                                <th><?php echo xlt('Paciente (PID)'); ?></th>
                                <th><?php echo xlt('Práctica / Código'); ?></th>
                                <th><?php echo xlt('Financiador'); ?></th>
                                <th><?php echo xlt('Estado'); ?></th>
                                <th><?php echo xlt('N° Autorización'); ?></th>
                                <th><?php echo xlt('Fecha Solicitud'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $resRecent = sqlStatement(
                                "SELECT a.*, p.fname, p.lname, ic.name AS insurer_name
                                 FROM covl_authorizations a
                                 LEFT JOIN patient_data p ON p.pid = a.pid
                                 LEFT JOIN insurance_companies ic ON ic.id = a.insurance_company_id
                                 ORDER BY a.request_date DESC
                                 LIMIT 10"
                            );
                            $hasRows = false;
                            while ($row = sqlFetchArray($resRecent)) {
                                $hasRows = true;
                                $badgeClass = match ($row['status']) {
                                    'aprobada'    => 'bg-success',
                                    'pendiente'   => 'bg-warning text-dark',
                                    'en_auditoria'=> 'bg-info text-dark',
                                    'rechazada'   => 'bg-danger',
                                    'vencida'     => 'bg-secondary',
                                    'cancelada'   => 'bg-dark',
                                    default       => 'bg-secondary',
                                };
                                ?>
                                <tr>
                                    <td><strong>#<?php echo text($row['id']); ?></strong></td>
                                    <td>
                                        <?php echo text($row['fname'] . ' ' . $row['lname']); ?> 
                                        <span class="text-muted small">(PID: <?php echo text($row['pid']); ?>)</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?php echo text($row['code_type']); ?></span>
                                        <strong><?php echo text($row['code']); ?></strong>
                                        <br><small class="text-muted"><?php echo text($row['code_text'] ?? ''); ?></small>
                                    </td>
                                    <td><?php echo text($row['insurer_name'] ?? 'N/A'); ?></td>
                                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo text(strtoupper($row['status'])); ?></span></td>
                                    <td><code><?php echo text($row['auth_number'] ?? '-'); ?></code></td>
                                    <td><small><?php echo text($row['request_date']); ?></small></td>
                                </tr>
                                <?php
                            }
                            if (!$hasRows): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
                                        <?php echo xlt('No hay solicitudes de autorización registradas aún.'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($activeTab === 'authorizations'): ?>

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-file-signature text-primary me-2"></i><?php echo xlt('Gestión de Autorizaciones Previas'); ?></h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo xlt('Lista completa de trámites de autorización previa registradas en el sistema.'); ?></p>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th># ID</th>
                                    <th><?php echo xlt('Paciente'); ?></th>
                                    <th><?php echo xlt('Código'); ?></th>
                                    <th><?php echo xlt('Estado'); ?></th>
                                    <th><?php echo xlt('N° Trámite'); ?></th>
                                    <th><?php echo xlt('Validez'); ?></th>
                                    <th><?php echo xlt('Encuentro'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $resAuth = sqlStatement(
                                    "SELECT a.*, p.fname, p.lname
                                     FROM covl_authorizations a
                                     LEFT JOIN patient_data p ON p.pid = a.pid
                                     ORDER BY a.request_date DESC LIMIT 50"
                                );
                                $found = false;
                                while ($r = sqlFetchArray($resAuth)) {
                                    $found = true;
                                    ?>
                                    <tr>
                                        <td>#<?php echo text($r['id']); ?></td>
                                        <td><?php echo text($r['fname'] . ' ' . $r['lname']); ?></td>
                                        <td><?php echo text($r['code_type'] . ':' . $r['code']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo text($r['status']); ?></span></td>
                                        <td><?php echo text($r['auth_number'] ?? '-'); ?></td>
                                        <td><small><?php echo text(($r['valid_from'] ?? '') . ' al ' . ($r['valid_until'] ?? '')); ?></small></td>
                                        <td>
                                            <?php if ($r['encounter_id']): ?>
                                                <span class="badge bg-info text-dark">Enc #<?php echo text($r['encounter_id']); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted">Sin Vincular</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                if (!$found): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4"><?php echo xlt('Sin autorizaciones.'); ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($activeTab === 'batches'): ?>

            <div class="covl-tab-header">
                <h5><i class="fa-solid fa-boxes-stacked text-primary me-2"></i><?php echo xlt('Lotes de Liquidación'); ?></h5>
                <button class="btn btn-sm btn-primary" onclick="window.__COVL_Batch.openCreate()">
                    <i class="fa-solid fa-plus me-1"></i><?php echo xlt('Nuevo Lote'); ?>
                </button>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="covl-filters">
                        <div>
                            <label class="form-label small text-muted mb-1"><?php echo xlt('Financiador'); ?></label>
                            <select class="form-select" id="flt-batch-insurer" data-covl-filter-batch>
                                <option value=""><?php echo xlt('— Todos —'); ?></option>
                                <?php foreach ($insurersList as $ins): ?>
                                    <option value="<?php echo attr($ins['id']); ?>"><?php echo text($ins['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small text-muted mb-1"><?php echo xlt('Sede'); ?></label>
                            <select class="form-select" id="flt-batch-facility" data-covl-filter-batch>
                                <option value=""><?php echo xlt('— Todas —'); ?></option>
                                <option value="0"><?php echo xlt('Todas las sedes'); ?></option>
                                <?php foreach ($facilitiesList as $fac): ?>
                                    <option value="<?php echo attr($fac['id']); ?>"><?php echo text($fac['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small text-muted mb-1"><?php echo xlt('Estado'); ?></label>
                            <select class="form-select" id="flt-batch-status" data-covl-filter-batch>
                                <option value=""><?php echo xlt('— Todos —'); ?></option>
                                <option value="borrador"><?php echo xlt('Borrador'); ?></option>
                                <option value="armado"><?php echo xlt('Armado'); ?></option>
                                <option value="presentado"><?php echo xlt('Presentado'); ?></option>
                                <option value="pagado_parcial"><?php echo xlt('Pagado parcial'); ?></option>
                                <option value="pagado_total"><?php echo xlt('Pagado total'); ?></option>
                                <option value="en_disputa"><?php echo xlt('En disputa'); ?></option>
                                <option value="anulado"><?php echo xlt('Anulado'); ?></option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small text-muted mb-1"><?php echo xlt('Período desde'); ?></label>
                            <input type="date" class="form-control" id="flt-batch-from" data-covl-filter-batch>
                        </div>
                        <div>
                            <label class="form-label small text-muted mb-1"><?php echo xlt('Período hasta'); ?></label>
                            <input type="date" class="form-control" id="flt-batch-to" data-covl-filter-batch>
                        </div>
                        <div class="covl-search">
                            <label class="form-label small text-muted mb-1"><?php echo xlt('Búsqueda'); ?></label>
                            <input type="text" class="form-control" id="flt-batch-search" data-covl-filter-batch placeholder="<?php echo xla('N° de lote'); ?>">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="covl-table">
                            <thead>
                                <tr>
                                    <th><?php echo xlt('N° Lote'); ?></th>
                                    <th><?php echo xlt('Financiador'); ?></th>
                                    <th><?php echo xlt('Sede'); ?></th>
                                    <th><?php echo xlt('Período'); ?></th>
                                    <th><?php echo xlt('Estado'); ?></th>
                                    <th class="text-center"><?php echo xlt('Ítems'); ?></th>
                                    <th class="text-end"><?php echo xlt('Total'); ?></th>
                                    <th class="text-end"><?php echo xlt('Acciones'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="covl-batch-tbody">
                                <tr><td colspan="8"><div class="covl-loading"><div class="covl-spinner"></div></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                        <span class="text-muted small" id="covl-batch-total"></span>
                        <div class="covl-pagination mb-0" id="covl-batch-pager"></div>
                    </div>
                </div>
            </div>

        <?php elseif ($activeTab === 'providers'): ?>

            <div class="covl-tab-header">
                <h5><i class="fa-solid fa-user-doctor text-primary me-2"></i><?php echo xlt('Convenios de Prestadores'); ?></h5>
                <button class="btn btn-sm btn-primary" onclick="window.__COVL_Prov.openCreate()">
                    <i class="fa-solid fa-plus me-1"></i><?php echo xlt('Nuevo Convenio'); ?>
                </button>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="covl-filters">
                        <div>
                            <label class="form-label small text-muted mb-1"><?php echo xlt('Profesional'); ?></label>
                            <select class="form-select" id="flt-prov-user" data-covl-filter-prov>
                                <option value=""><?php echo xlt('— Todos —'); ?></option>
                                <?php foreach ($professionalsList as $prof): ?>
                                    <option value="<?php echo attr($prof['id']); ?>"><?php echo text($prof['lname'] . ', ' . $prof['fname']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small text-muted mb-1"><?php echo xlt('Financiador'); ?></label>
                            <select class="form-select" id="flt-prov-insurer" data-covl-filter-prov>
                                <option value=""><?php echo xlt('— Todos —'); ?></option>
                                <?php foreach ($insurersList as $ins): ?>
                                    <option value="<?php echo attr($ins['id']); ?>"><?php echo text($ins['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small text-muted mb-1"><?php echo xlt('Sede'); ?></label>
                            <select class="form-select" id="flt-prov-facility" data-covl-filter-prov>
                                <option value=""><?php echo xlt('— Todas —'); ?></option>
                                <option value="0"><?php echo xlt('Todas las sedes'); ?></option>
                                <?php foreach ($facilitiesList as $fac): ?>
                                    <option value="<?php echo attr($fac['id']); ?>"><?php echo text($fac['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small text-muted mb-1"><?php echo xlt('Estado'); ?></label>
                            <select class="form-select" id="flt-prov-active" data-covl-filter-prov>
                                <option value=""><?php echo xlt('— Todos —'); ?></option>
                                <option value="1"><?php echo xlt('Activo'); ?></option>
                                <option value="0"><?php echo xlt('Inactivo'); ?></option>
                            </select>
                        </div>
                        <div class="covl-search">
                            <label class="form-label small text-muted mb-1"><?php echo xlt('Búsqueda'); ?></label>
                            <input type="text" class="form-control" id="flt-prov-search" data-covl-filter-prov placeholder="<?php echo xla('Profesional, n° de prestador o especialidad'); ?>">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="covl-table">
                            <thead>
                                <tr>
                                    <th><?php echo xlt('Profesional'); ?></th>
                                    <th><?php echo xlt('Financiador'); ?></th>
                                    <th><?php echo xlt('Sede'); ?></th>
                                    <th><?php echo xlt('N° Prestador'); ?></th>
                                    <th><?php echo xlt('Vigencia'); ?></th>
                                    <th><?php echo xlt('Especialidades'); ?></th>
                                    <th><?php echo xlt('Estado'); ?></th>
                                    <th class="text-end"><?php echo xlt('Acciones'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="covl-prov-tbody">
                                <tr><td colspan="8"><div class="covl-loading"><div class="covl-spinner"></div></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                        <span class="text-muted small" id="covl-prov-total"></span>
                        <div class="covl-pagination mb-0" id="covl-prov-pager"></div>
                    </div>
                </div>
            </div>

        <?php elseif ($activeTab === 'countries'): ?>

            <div class="row g-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h5 class="mb-0 fw-bold">
                                <i class="fa-solid fa-earth-americas text-primary me-2"></i><?php echo xlt('Paquetes de País'); ?>
                            </h5>
                            <button type="button" id="btn-country-open" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-plus me-1"></i><?php echo xlt('Agregar País'); ?>
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                <?php echo xlt('Los paquetes de país registran el nomenclador nacional (tipo de código), las reglas base de autorización y frecuencia, y las equivalencias de códigos a estándares. Elige un país del catálogo para instalarlo o actualizarlo.'); ?>
                            </p>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th><?php echo xlt('País'); ?></th>
                                            <th><?php echo xlt('Nombre'); ?></th>
                                            <th><?php echo xlt('Nomenclador'); ?></th>
                                            <th><?php echo xlt('Versión'); ?></th>
                                            <th><?php echo xlt('Moneda'); ?></th>
                                            <th><?php echo xlt('Reglas Base'); ?></th>
                                            <th class="text-end"><?php echo xlt('Acciones'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="covl-country-tbody">
                                        <tr><td colspan="7" class="text-center py-4">
                                            <div class="covl-spinner spinner-border text-primary"></div>
                                        </td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($activeTab === 'auth_rules'): ?>

            <div class="covl-tab-header">
                <h5><i class="fa-solid fa-shield-halved text-primary me-2"></i><?php echo xlt('Reglas de Autorización'); ?></h5>
                <button class="btn btn-sm btn-primary" onclick="COVL.Auth.openCreate()">
                    <i class="fa-solid fa-plus me-1"></i><?php echo xlt('Nueva Regla'); ?>
                </button>
            </div>

            <?php if (!empty($countryPacks)): ?>
            <div class="covl-country-pills" id="covl-country-pills">
                <a href="#" class="covl-country-pill active" data-covl-country="">
                    🌎 <?= xlt('Todos') ?>
                </a>
                <?php foreach ($countryPacks as $pack):
                    $code = $pack['country_code'];
                ?>
                <a href="#" class="covl-country-pill" data-covl-country="<?= attr($code) ?>">
                    <span class="fi fi-<?= strtolower(attr($code)) ?> fs-pill-flag"></span>
                    <?= text($code) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="covl-filters">
                        <div class="filter-group">
                            <label><?php echo xlt('País'); ?></label>
                            <?= covl_flag_select('flt-auth-country', $allCountryOpts, false, 'fs-compact') ?>
                        </div>
                        <div class="filter-group" style="min-width:200px">
                            <label><?php echo xlt('Financiador'); ?></label>
                            <select id="flt-auth-insurer" class="form-control form-control-sm" data-covl-filter-auth>
                                <option value=""><?php echo xlt('— Todos —'); ?></option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label><?php echo xlt('Tipo cód.'); ?></label>
                            <select id="flt-auth-codetype" class="form-control form-control-sm" data-covl-filter-auth>
                                <option value=""><?php echo xlt('— Todos —'); ?></option>
                                <?php foreach ($codeTypes as $ct): ?>
                                <option value="<?php echo attr($ct['ct_key']); ?>"><?php echo text($ct['ct_key']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label><?php echo xlt('Código'); ?></label>
                            <input type="text" id="flt-auth-code" data-covl-filter-auth
                                   placeholder="<?php echo xla('ej: 380601'); ?>" style="width:110px">
                        </div>
                        <div class="filter-group">
                            <label><?php echo xlt('Estado'); ?></label>
                            <select id="flt-auth-active" class="form-control form-control-sm" data-covl-filter-auth>
                                <option value=""><?php echo xlt('— Todos —'); ?></option>
                                <option value="1"><?php echo xlt('Activas'); ?></option>
                                <option value="0"><?php echo xlt('Inactivas'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="covl-table-wrapper">
                        <table class="covl-table">
                            <thead>
                                <tr>
                                    <th><?php echo xlt('País'); ?></th>
                                    <th><?php echo xlt('Financiador'); ?></th>
                                    <th><?php echo xlt('Patrón de plan'); ?></th>
                                    <th><?php echo xlt('Tipo cód.'); ?></th>
                                    <th><?php echo xlt('Código'); ?></th>
                                    <th><?php echo xlt('Modo'); ?></th>
                                    <th><?php echo xlt('Máx. cant.'); ?></th>
                                    <th><?php echo xlt('Estado'); ?></th>
                                    <th><?php echo xlt('Acciones'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="covl-auth-tbody">
                                <tr><td colspan="9"><div class="covl-loading"><div class="covl-spinner"></div> <?php echo xlt('Cargando...'); ?></div></td></tr>
                            </tbody>
                        </table>
                        <div class="covl-pagination" id="covl-auth-pager"></div>
                    </div>
                </div>
            </div>

        <?php elseif ($activeTab === 'freq_rules'): ?>

            <div class="covl-tab-header">
                <h5><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i><?php echo xlt('Reglas de Frecuencia'); ?></h5>
                <button class="btn btn-sm btn-primary" onclick="COVL.Freq.openCreate()">
                    <i class="fa-solid fa-plus me-1"></i><?php echo xlt('Nueva Regla'); ?>
                </button>
            </div>

            <?php if (!empty($countryPacks)): ?>
            <div class="covl-country-pills" id="covl-country-pills">
                <a href="#" class="covl-country-pill active" data-covl-country="">
                    🌎 <?= xlt('Todos') ?>
                </a>
                <?php foreach ($countryPacks as $pack):
                    $code = $pack['country_code'];
                ?>
                <a href="#" class="covl-country-pill" data-covl-country="<?= attr($code) ?>">
                    <span class="fi fi-<?= strtolower(attr($code)) ?> fs-pill-flag"></span>
                    <?= text($code) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="covl-filters">
                        <div class="filter-group">
                            <label><?php echo xlt('País'); ?></label>
                            <?= covl_flag_select('flt-freq-country', $allCountryOpts, false, 'fs-compact') ?>
                        </div>
                        <div class="filter-group" style="min-width:200px">
                            <label><?php echo xlt('Financiador'); ?></label>
                            <select id="flt-freq-insurer" class="form-control form-control-sm" data-covl-filter-freq>
                                <option value=""><?php echo xlt('— Todos —'); ?></option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label><?php echo xlt('Tipo cód.'); ?></label>
                            <select id="flt-freq-codetype" class="form-control form-control-sm" data-covl-filter-freq>
                                <option value=""><?php echo xlt('— Todos —'); ?></option>
                                <?php foreach ($codeTypes as $ct): ?>
                                <option value="<?php echo attr($ct['ct_key']); ?>"><?php echo text($ct['ct_key']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label><?php echo xlt('Código'); ?></label>
                            <input type="text" id="flt-freq-code" data-covl-filter-freq
                                   placeholder="<?php echo xla('ej: 380601'); ?>" style="width:110px">
                        </div>
                        <div class="filter-group">
                            <label><?php echo xlt('Severidad'); ?></label>
                            <select id="flt-freq-severity" class="form-control form-control-sm" data-covl-filter-freq>
                                <option value=""><?php echo xlt('— Todas —'); ?></option>
                                <option value="alerta"><?php echo xlt('Alerta'); ?></option>
                                <option value="bloqueo"><?php echo xlt('Bloqueo'); ?></option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label><?php echo xlt('Estado'); ?></label>
                            <select id="flt-freq-active" class="form-control form-control-sm" data-covl-filter-freq>
                                <option value=""><?php echo xlt('— Todos —'); ?></option>
                                <option value="1"><?php echo xlt('Activas'); ?></option>
                                <option value="0"><?php echo xlt('Inactivas'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="covl-table-wrapper">
                        <table class="covl-table">
                            <thead>
                                <tr>
                                    <th><?php echo xlt('País'); ?></th>
                                    <th><?php echo xlt('Financiador'); ?></th>
                                    <th><?php echo xlt('Tipo cód.'); ?></th>
                                    <th><?php echo xlt('Código'); ?></th>
                                    <th><?php echo xlt('Intervalo mín.'); ?></th>
                                    <th><?php echo xlt('Máx/año'); ?></th>
                                    <th><?php echo xlt('Severidad'); ?></th>
                                    <th><?php echo xlt('Estado'); ?></th>
                                    <th><?php echo xlt('Acciones'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="covl-freq-tbody">
                                <tr><td colspan="9"><div class="covl-loading"><div class="covl-spinner"></div> <?php echo xlt('Cargando...'); ?></div></td></tr>
                            </tbody>
                        </table>
                        <div class="covl-pagination" id="covl-freq-pager"></div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <!-- MODAL: Convenio de Prestador -->
    <div class="modal fade" id="covlProvModal" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="covl-prov-form">
                    <input type="hidden" id="fld-prov-id">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="covlProvModalLabel"><i class="fa-solid fa-handshake text-primary me-2"></i><?php echo xlt('Convenio de Prestador'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeModal('covlProvModal')"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo xlt('Profesional'); ?> *</label>
                                <select id="fld-prov-user" class="form-select" required>
                                    <option value=""><?php echo xlt('-- Seleccionar --'); ?></option>
                                    <?php foreach ($professionalsList as $prof): ?>
                                        <option value="<?php echo attr($prof['id']); ?>"><?php echo text($prof['lname'] . ', ' . $prof['fname'] . ($prof['username'] ? ' (' . $prof['username'] . ')' : '')); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo xlt('Financiador / Obra Social'); ?> *</label>
                                <select id="fld-prov-insurer" class="form-select" required>
                                    <option value=""><?php echo xlt('-- Seleccionar --'); ?></option>
                                    <?php foreach ($insurersList as $ins): ?>
                                        <option value="<?php echo attr($ins['id']); ?>"><?php echo text($ins['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo xlt('Sede'); ?></label>
                                <select id="fld-prov-facility" class="form-select">
                                    <option value="0"><?php echo xlt('Todas las sedes'); ?></option>
                                    <?php foreach ($facilitiesList as $fac): ?>
                                        <option value="<?php echo attr($fac['id']); ?>"><?php echo text($fac['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo xlt('N° Prestador'); ?></label>
                                <input type="text" id="fld-prov-number" class="form-control" placeholder="Ej: MAT-12345">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo xlt('Vigencia desde'); ?></label>
                                <input type="date" id="fld-prov-from" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo xlt('Vigencia hasta'); ?> <span class="text-muted small">(<?php echo xlt('vacío = sin vencimiento'); ?>)</span></label>
                                <input type="date" id="fld-prov-to" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold"><?php echo xlt('Especialidades'); ?></label>
                                <input type="text" id="fld-prov-specialties" class="form-control" placeholder="Ej: Cardiología, Clínica Médica">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold"><?php echo xlt('Notas'); ?></label>
                                <textarea id="fld-prov-notes" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="fld-prov-active" checked>
                                    <label class="form-check-label" for="fld-prov-active"><?php echo xlt('Convenio activo'); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('covlProvModal')"><?php echo xlt('Cancelar'); ?></button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i><?php echo xlt('Guardar'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Lote de Liquidación -->
    <div class="modal fade" id="covlBatchModal" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="covl-batch-form">
                    <input type="hidden" id="fld-batch-id">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="covlBatchModalLabel"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i><?php echo xlt('Lote de Liquidación'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeModal('covlBatchModal')"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo xlt('Financiador / Obra Social'); ?> *</label>
                                <select id="fld-batch-insurer" class="form-select" required></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo xlt('Sede'); ?></label>
                                <select id="fld-batch-facility" class="form-select">
                                    <option value="0"><?php echo xlt('Todas las sedes'); ?></option>
                                    <?php foreach ($facilitiesList as $fac): ?>
                                        <option value="<?php echo attr($fac['id']); ?>"><?php echo text($fac['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo xlt('Período desde'); ?> *</label>
                                <input type="date" id="fld-batch-from" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo xlt('Período hasta'); ?> *</label>
                                <input type="date" id="fld-batch-to" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo xlt('Moneda'); ?></label>
                                <select id="fld-batch-currency" class="form-select">
                                    <?php
                                    $batchCurrencies = [];
                                    foreach ($countryPacks as $p) {
                                        if (!empty($p['currency_code']) && !isset($batchCurrencies[$p['currency_code']])) {
                                            $batchCurrencies[$p['currency_code']] = $p['currency_name'] ?? $p['currency_code'];
                                        }
                                    }
                                    foreach ($batchCurrencies as $code => $cname): ?>
                                        <option value="<?php echo attr($code); ?>"><?php echo text($code); ?> — <?php echo text($cname); ?></option>
                                    <?php endforeach; ?>
                                    <?php if (!isset($batchCurrencies['USD'])): ?>
                                        <option value="USD">USD — <?php echo xlt('Dólar'); ?></option>
                                    <?php endif; ?>
                                    <?php if (!isset($batchCurrencies['EUR'])): ?>
                                        <option value="EUR">EUR — <?php echo xlt('Euro'); ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('covlBatchModal')"><?php echo xlt('Cancelar'); ?></button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i><?php echo xlt('Guardar'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Ítems del Lote -->
    <div class="modal fade" id="covlBatchItemsModal" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="covlBatchItemsTitle"><i class="fa-solid fa-list-check text-primary me-2"></i><?php echo xlt('Lote'); ?></h5>
                    <span id="covl-batch-items-status" class="ms-auto"></span>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close" onclick="closeModal('covlBatchItemsModal')"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <strong id="covl-batch-items-total"></strong>
                        <button class="btn btn-sm btn-primary" id="btn-batch-add-billing">
                            <i class="fa-solid fa-plus me-1"></i><?php echo xlt('Agregar prestación'); ?>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="covl-table">
                            <thead>
                                <tr>
                                    <th><?php echo xlt('Paciente'); ?></th>
                                    <th><?php echo xlt('Práctica'); ?></th>
                                    <th><?php echo xlt('Encuentro'); ?></th>
                                    <th class="text-end"><?php echo xlt('Monto'); ?></th>
                                    <th><?php echo xlt('Estado'); ?></th>
                                    <th class="text-center"><?php echo xlt('Intento'); ?></th>
                                    <th class="text-end"><?php echo xlt('Acciones'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="covl-items-tbody"></tbody>
                        </table>
                    </div>
                    <div id="covl-batch-transitions" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('covlBatchItemsModal')"><?php echo xlt('Cerrar'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Agregar prestación al lote -->
    <div class="modal fade" id="covlBillingModal" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-receipt text-primary me-2"></i><?php echo xlt('Agregar prestación al lote'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeModal('covlBillingModal')"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="flt-billing-q" placeholder="<?php echo xla('Buscar por paciente, código o n° de factura…'); ?>">
                    </div>
                    <div class="table-responsive">
                        <table class="covl-table">
                            <thead>
                                <tr>
                                    <th><?php echo xlt('Paciente'); ?></th>
                                    <th><?php echo xlt('Práctica'); ?></th>
                                    <th><?php echo xlt('Fecha'); ?></th>
                                    <th><?php echo xlt('Encuentro'); ?></th>
                                    <th class="text-end"><?php echo xlt('Monto'); ?></th>
                                    <th class="text-end"><?php echo xlt('Acción'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="covl-billing-tbody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('covlBillingModal')"><?php echo xlt('Cerrar'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Registrar Pago -->
    <div class="modal fade" id="covlPayModal" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="covl-pay-form">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-money-bill-wave text-success me-2"></i><?php echo xlt('Registrar Pago'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeModal('covlPayModal')"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo xlt('Monto'); ?> *</label>
                            <input type="number" step="0.01" min="0" id="fld-pay-amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo xlt('Fecha de pago'); ?> *</label>
                            <input type="date" id="fld-pay-date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo xlt('Referencia'); ?></label>
                            <input type="text" id="fld-pay-reference" class="form-control" placeholder="Ej: Transferencia N° 12345">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('covlPayModal')"><?php echo xlt('Cancelar'); ?></button>
                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i><?php echo xlt('Confirmar Pago'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Marcar en disputa -->
    <div class="modal fade" id="covlDisputeModal" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="covl-dispute-form">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i><?php echo xlt('Marcar lote en disputa'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeModal('covlDisputeModal')"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-bold"><?php echo xlt('Motivo de la disputa'); ?></label>
                        <textarea id="fld-dispute-notes" class="form-control" rows="3" required placeholder="<?php echo xla('Detalle el motivo de la disputa'); ?>"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('covlDisputeModal')"><?php echo xlt('Cancelar'); ?></button>
                        <button type="submit" class="btn btn-warning"><i class="fa-solid fa-check me-1"></i><?php echo xlt('Confirmar Disputa'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Paquetes de País -->
    <div class="modal fade" id="covlCountryModal" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-earth-americas text-primary me-2"></i><?php echo xlt('Catálogo de Países'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeModal('covlCountryModal')"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small"><?php echo xlt('Seleccioná un país del catálogo para instalar o actualizar su paquete.'); ?></p>
                    <div class="covl-search mb-3">
                        <label class="form-label small text-muted mb-1"><?php echo xlt('Búsqueda'); ?></label>
                        <input type="text" class="form-control" id="flt-country-search" placeholder="<?php echo xla('Buscar por nombre o código de país…'); ?>">
                    </div>
                    <div id="covl-country-list" class="covl-country-list">
                        <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('covlCountryModal')"><?php echo xlt('Cerrar'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Regla de Autorización -->
    <div class="modal fade" id="covlAuthModal" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-shield-halved text-primary me-2"></i><?php echo xlt('Regla de Autorización'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeModal('covlAuthModal')"></button>
                </div>
                <form id="covl-auth-form" onsubmit="event.preventDefault(); COVL.Auth.save();">
                    <div class="modal-body">
                        <input type="hidden" id="fld-auth-id">

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo xlt('País'); ?> *</label>
                                <?= covl_flag_select('fld-auth-country', $reqCountryOpts, true, 'w-100') ?>
                                <small class="text-muted"><?php echo xlt('País del paquete de configuración'); ?></small>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold"><?php echo xlt('Financiador'); ?> *</label>
                                <select class="form-control form-control-sm" id="fld-auth-insurer" required>
                                    <option value=""><?php echo xlt('Cargando...'); ?></option>
                                </select>
                                <small class="text-muted"><?php echo xlt('Seleccioná "Todos" (0) para regla genérica del país'); ?></small>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo xlt('Patrón de plan'); ?></label>
                                <input type="text" class="form-control form-control-sm" id="fld-auth-plan-pattern"
                                       placeholder="<?php echo xla('Vacío = todos los planes; soporta %'); ?>">
                                <small class="text-muted"><?php echo xlt('Dejalo vacío para aplicar a todos los planes (se guarda como 0)'); ?></small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold"><?php echo xlt('Tipo de código'); ?> *</label>
                                <select class="form-control form-control-sm" id="fld-auth-codetype" required>
                                    <option value=""><?php echo xlt('— Seleccioná —'); ?></option>
                                    <?php foreach ($codeTypes as $ct): ?>
                                    <option value="<?php echo attr($ct['ct_key']); ?>"><?php echo text($ct['ct_key']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold"><?php echo xlt('Código'); ?></label>
                                <input type="text" class="form-control form-control-sm" id="fld-auth-code"
                                       placeholder="<?php echo xla('Vacío = todos'); ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo xlt('Modo de autorización'); ?> *</label>
                                <select class="form-control form-control-sm" id="fld-auth-mode" required>
                                    <option value="requerida"><?php echo xlt('Requerida'); ?></option>
                                    <option value="automatica"><?php echo xlt('Automática'); ?></option>
                                    <option value="no_requerida"><?php echo xlt('No requerida'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-3" id="grp-auth-max-qty" style="display:none">
                                <label class="form-label fw-bold"><?php echo xlt('Cant. máxima automática'); ?></label>
                                <input type="number" class="form-control form-control-sm" id="fld-auth-max-qty"
                                       min="1" placeholder="<?php echo xla('ej: 6'); ?>">
                                <small class="text-muted"><?php echo xlt('Si se supera → escala a requerida'); ?></small>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold"><?php echo xlt('Prioridad'); ?></label>
                                <input type="number" class="form-control form-control-sm" id="fld-auth-priority"
                                       min="1" max="999" value="100">
                                <small class="text-muted"><?php echo xlt('Menor = más prioritario'); ?></small>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="fld-auth-active" checked>
                                    <label class="form-check-label" for="fld-auth-active"><?php echo xlt('Regla activa'); ?></label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo xlt('Notas / Justificación'); ?></label>
                            <textarea class="form-control form-control-sm" id="fld-auth-notes" rows="2"
                                      placeholder="<?php echo xla('Ej: TAC de cráneo — requiere autorización previa según RES. 925/2000'); ?>"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('covlAuthModal')"><?php echo xlt('Cancelar'); ?></button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk me-1"></i><?php echo xlt('Guardar regla'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Regla de Frecuencia -->
    <div class="modal fade" id="covlFreqModal" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i><?php echo xlt('Regla de Frecuencia'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeModal('covlFreqModal')"></button>
                </div>
                <form id="covl-freq-form" onsubmit="event.preventDefault(); COVL.Freq.save();">
                    <div class="modal-body">
                        <input type="hidden" id="fld-freq-id">

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo xlt('País'); ?> *</label>
                                <?= covl_flag_select('fld-freq-country', $reqCountryOpts, true, 'w-100') ?>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold"><?php echo xlt('Financiador'); ?> *</label>
                                <select class="form-control form-control-sm" id="fld-freq-insurer" required>
                                    <option value=""><?php echo xlt('Cargando...'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo xlt('Tipo de código'); ?> *</label>
                                <select class="form-control form-control-sm" id="fld-freq-codetype" required>
                                    <option value=""><?php echo xlt('— Seleccioná —'); ?></option>
                                    <?php foreach ($codeTypes as $ct): ?>
                                    <option value="<?php echo attr($ct['ct_key']); ?>"><?php echo text($ct['ct_key']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo xlt('Código'); ?> *</label>
                                <input type="text" class="form-control form-control-sm" id="fld-freq-code"
                                       required placeholder="<?php echo xla('ej: 380601'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo xlt('Máximo por año'); ?></label>
                                <input type="number" class="form-control form-control-sm" id="fld-freq-max-year"
                                       min="1" placeholder="<?php echo xla('Vacío = sin límite anual'); ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo xlt('Intervalo mínimo (días)'); ?> *</label>
                                <input type="number" class="form-control form-control-sm" id="fld-freq-interval"
                                       required min="1" placeholder="<?php echo xla('ej: 180'); ?>">
                                <div class="covl-interval-hint" id="freq-interval-hint"></div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold"><?php echo xlt('Severidad al detectar violación'); ?> *</label>
                                <div class="covl-severity-group">
                                    <label class="covl-severity-opt sev-alerta">
                                        <input type="radio" name="fld-freq-severity" id="fld-freq-severity-alerta" value="alerta" checked>
                                        <span>⚠️ <?php echo xlt('Alerta'); ?></span>
                                        <small class="d-block text-muted" style="font-size:.7rem"><?php echo xlt('Avisa pero permite continuar'); ?></small>
                                    </label>
                                    <label class="covl-severity-opt sev-bloqueo">
                                        <input type="radio" name="fld-freq-severity" id="fld-freq-severity-bloqueo" value="bloqueo">
                                        <span>🚫 <?php echo xlt('Bloqueo'); ?></span>
                                        <small class="d-block text-muted" style="font-size:.7rem"><?php echo xlt('Impide generar la orden'); ?></small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-9">
                                <label class="form-label fw-bold"><?php echo xlt('Notas / Referencia normativa'); ?></label>
                                <textarea class="form-control form-control-sm" id="fld-freq-notes" rows="2"
                                          placeholder="<?php echo xla('Ej: TAC de cráneo — intervalo mínimo 180 días según normativa PAMI'); ?>"></textarea>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="fld-freq-active" checked>
                                    <label class="form-check-label" for="fld-freq-active"><?php echo xlt('Regla activa'); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('covlFreqModal')"><?php echo xlt('Cancelar'); ?></button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk me-1"></i><?php echo xlt('Guardar regla'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Configuración JS inyectada desde PHP -->
    <script>
    const covlConfig = {
        csrfToken:    <?= json_encode($csrfToken) ?>,
        baseApiUrl:   <?= json_encode($moduleBase . '/api') ?>,
        i18n:         <?= json_encode($covlI18n, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        countryPacks: <?= json_encode($countryPacks) ?>,
        activeCurrency: <?= json_encode($activeCurrency, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
    };
    </script>
    <script src="<?= attr($moduleBase) ?>/assets/js/providers-crud.js"></script>
    <script src="<?= attr($moduleBase) ?>/assets/js/batches-crud.js"></script>
    <script src="<?= attr($moduleBase) ?>/assets/js/countries-crud.js"></script>
    <script src="<?= attr($moduleBase) ?>/assets/js/rules-crud.js"></script>

    <!-- Poblar selects de financiadores en los filtros al arrancar -->
    <script>
    document.addEventListener('DOMContentLoaded', async () => {
        const res      = await fetch(covlConfig.baseApiUrl + '/insurers.php');
        const insurers = await res.json();
        if (!insurers) return;

        ['flt-auth-insurer', 'flt-freq-insurer'].forEach(id => {
            const sel = document.getElementById(id);
            if (!sel) return;
            sel.innerHTML = '<option value=""><?= xlt("— Todos —") ?></option>';
            insurers.filter(i => i.id !== 0).forEach(ins => {
                const opt      = document.createElement('option');
                opt.value      = ins.id;
                opt.textContent = `[${ins.id}] ${ins.name}`;
                sel.appendChild(opt);
            });
        });
    });
    </script>

    <!-- Script de soporte universal para modales (Bootstrap 4 / Bootstrap 5 / JS Nativo) -->
    <script>
        function openModal(id) {
            var el = document.getElementById(id);
            if (!el) return;
            if (window.jQuery && window.jQuery(el) && typeof window.jQuery(el).modal === 'function') {
                window.jQuery(el).modal('show');
                return;
            }
            if (window.bootstrap && window.bootstrap.Modal && typeof window.bootstrap.Modal.getOrCreateInstance === 'function') {
                var modal = window.bootstrap.Modal.getOrCreateInstance(el);
                if (modal) { modal.show(); return; }
            }
            el.style.display = 'block';
            el.classList.add('show');
            document.body.classList.add('modal-open');
        }

        function closeModal(id) {
            var el = document.getElementById(id);
            if (!el) return;
            if (window.jQuery && window.jQuery(el) && typeof window.jQuery(el).modal === 'function') {
                window.jQuery(el).modal('hide');
                return;
            }
            if (window.bootstrap && window.bootstrap.Modal && typeof window.bootstrap.Modal.getInstance === 'function') {
                var modal = window.bootstrap.Modal.getInstance(el);
                if (modal) { modal.hide(); return; }
            }
            el.style.display = 'none';
            el.classList.remove('show');
            document.body.classList.remove('modal-open');
        }
    </script>
</body>
</html>

