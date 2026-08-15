<?php

/**
 * oe-module-coverage-latam — Dashboard Principal & CRUD de Configuración
 *
 * Interfaz unificada de gestión de Coberturas LATAM:
 * - Tab 1: Dashboard / Métricas
 * - Tab 2: Autorizaciones Previas
 * - Tab 3: Lotes de Liquidación
 * - Tab 4: Convenios de Prestadores
 * - Tab 5: Reglas de Configuración (CRUD Reglas de Autorización y Frecuencia)
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

// ---------------------------------------------------------------------------
// Procesamiento de Acciones POST (CRUD)
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. Crear Regla de Autorización
    if ($action === 'add_auth_rule') {
        $insCompanyId = (int) ($_POST['insurance_company_id'] ?? 0);
        $planPattern  = trim($_POST['plan_pattern'] ?? '0');
        if ($planPattern === '') { $planPattern = '0'; }
        $codeType     = trim($_POST['code_type'] ?? 'NNAR');
        $code         = trim($_POST['code'] ?? '0');
        if ($code === '') { $code = '0'; }
        $authMode     = $_POST['auth_mode'] ?? 'requerida';
        $maxQty       = isset($_POST['max_quantity']) && $_POST['max_quantity'] !== '' ? (int)$_POST['max_quantity'] : null;
        $priority     = (int) ($_POST['priority'] ?? 100);
        $notes        = trim($_POST['notes'] ?? '');

        if ($insCompanyId > 0) {
            sqlStatement(
                "INSERT INTO covl_auth_rules 
                    (insurance_company_id, plan_pattern, code_type, code, auth_mode, max_quantity, priority, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$insCompanyId, $planPattern, $codeType, $code, $authMode, $maxQty, $priority, $notes]
            );
            $_SESSION['covl_msg'] = ['type' => 'success', 'text' => xlt('Regla de autorización creada exitosamente.')];
        }
        header('Location: dashboard.php?tab=config');
        exit;
    }

    // 2. Activar / Inactivar Regla de Autorización
    if ($action === 'toggle_auth_rule') {
        $ruleId = (int) ($_POST['rule_id'] ?? 0);
        if ($ruleId > 0) {
            sqlStatement("UPDATE covl_auth_rules SET active = 1 - active WHERE id = ?", [$ruleId]);
            $_SESSION['covl_msg'] = ['type' => 'info', 'text' => xlt('Estado de la regla de autorización actualizado.')];
        }
        header('Location: dashboard.php?tab=config');
        exit;
    }

    // 3. Eliminar Regla de Autorización
    if ($action === 'delete_auth_rule') {
        $ruleId = (int) ($_POST['rule_id'] ?? 0);
        if ($ruleId > 0) {
            sqlStatement("DELETE FROM covl_auth_rules WHERE id = ?", [$ruleId]);
            $_SESSION['covl_msg'] = ['type' => 'warning', 'text' => xlt('Regla de autorización eliminada.')];
        }
        header('Location: dashboard.php?tab=config');
        exit;
    }

    // 4. Crear Regla de Frecuencia
    if ($action === 'add_freq_rule') {
        $insCompanyId = (int) ($_POST['insurance_company_id'] ?? 0);
        $codeType     = trim($_POST['code_type'] ?? 'NNAR');
        $code         = trim($_POST['code'] ?? '');
        $minInterval  = (int) ($_POST['min_interval_days'] ?? 0);
        $maxPerYear   = isset($_POST['max_per_year']) && $_POST['max_per_year'] !== '' ? (int)$_POST['max_per_year'] : null;
        $severity     = $_POST['severity'] ?? 'alerta';
        $notes        = trim($_POST['notes'] ?? '');

        if ($insCompanyId > 0 && $code !== '' && $minInterval > 0) {
            sqlStatement(
                "INSERT INTO covl_frequency_rules 
                    (insurance_company_id, code_type, code, min_interval_days, max_per_year, severity, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$insCompanyId, $codeType, $code, $minInterval, $maxPerYear, $severity, $notes]
            );
            $_SESSION['covl_msg'] = ['type' => 'success', 'text' => xlt('Regla de frecuencia creada exitosamente.')];
        }
        header('Location: dashboard.php?tab=config');
        exit;
    }

    // 5. Activar / Inactivar Regla de Frecuencia
    if ($action === 'toggle_freq_rule') {
        $ruleId = (int) ($_POST['rule_id'] ?? 0);
        if ($ruleId > 0) {
            sqlStatement("UPDATE covl_frequency_rules SET active = 1 - active WHERE id = ?", [$ruleId]);
            $_SESSION['covl_msg'] = ['type' => 'info', 'text' => xlt('Estado de la regla de frecuencia actualizado.')];
        }
        header('Location: dashboard.php?tab=config');
        exit;
    }

    // 6. Eliminar Regla de Frecuencia
    if ($action === 'delete_freq_rule') {
        $ruleId = (int) ($_POST['rule_id'] ?? 0);
        if ($ruleId > 0) {
            sqlStatement("DELETE FROM covl_frequency_rules WHERE id = ?", [$ruleId]);
            $_SESSION['covl_msg'] = ['type' => 'warning', 'text' => xlt('Regla de frecuencia eliminada.')];
        }
        header('Location: dashboard.php?tab=config');
        exit;
    }
}

$activeTab = $_GET['tab'] ?? 'dashboard';
$allowedTabs = ['dashboard', 'authorizations', 'batches', 'providers', 'config'];
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
$resProf = sqlStatement("SELECT u.id, u.username, u.fname, u.lname, u.specialty FROM users u WHERE u.username IS NOT NULL AND u.username != '' ORDER BY u.lname, u.fname");
while ($rProf = sqlFetchArray($resProf)) {
    $professionalsList[] = $rProf;
}

$facilitiesList = [];
$resFac = sqlStatement("SELECT id, name FROM facility ORDER BY name ASC");
while ($rFac = sqlFetchArray($resFac)) {
    $facilitiesList[] = $rFac;
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
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo xlt('Coberturas LATAM — Gestión'); ?></title>
    <?php Header::setupHeader(['bootstrap', 'fontawesome']); ?>
    <link rel="stylesheet" href="<?php echo $moduleBase; ?>/assets/css/admin-rules.css">
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
                <a class="nav-link <?php echo $activeTab === 'config' ? 'active' : ''; ?>" 
                   href="dashboard.php?tab=config">
                    <i class="fa-solid fa-sliders me-1"></i><?php echo xlt('Configuración & Reglas'); ?>
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

        <?php elseif ($activeTab === 'config'): ?>

            <div class="row g-4">
                <!-- Seccion 1: Reglas de Autorizacion Previa -->
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h5 class="mb-0 fw-bold">
                                <i class="fa-solid fa-shield-halved text-primary me-2"></i><?php echo xlt('Reglas de Autorización Previa'); ?>
                            </h5>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNewAuthRule" data-toggle="modal" data-target="#modalNewAuthRule" onclick="openModal('modalNewAuthRule')">
                                <i class="fa-solid fa-plus me-1"></i><?php echo xlt('Nueva Regla de Autorización'); ?>
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                <?php echo xlt('Determina si una práctica solicitada ante un financiador requiere autorización previa (requerida), se aprueba automáticamente (automatica) o no la requiere (no_requerida). Evaluadas por prioridad (menor valor = mayor relevancia).'); ?>
                            </p>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th># ID</th>
                                            <th><?php echo xlt('Financiador'); ?></th>
                                            <th><?php echo xlt('Patrón de Plan'); ?></th>
                                            <th><?php echo xlt('Tipo Código'); ?></th>
                                            <th><?php echo xlt('Código Práctica'); ?></th>
                                            <th><?php echo xlt('Modo'); ?></th>
                                            <th><?php echo xlt('Máx. Auto'); ?></th>
                                            <th><?php echo xlt('Prioridad'); ?></th>
                                            <th><?php echo xlt('Estado'); ?></th>
                                            <th><?php echo xlt('Acciones'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $resAuthRules = sqlStatement(
                                            "SELECT r.*, ic.name AS insurer_name
                                             FROM covl_auth_rules r
                                             LEFT JOIN insurance_companies ic ON ic.id = r.insurance_company_id
                                             ORDER BY r.priority ASC, r.id ASC"
                                        );
                                        $hasAuthRules = false;
                                        while ($r = sqlFetchArray($resAuthRules)) {
                                            $hasAuthRules = true;
                                            $modeBadge = match ($r['auth_mode']) {
                                                'automatica'   => 'bg-success',
                                                'requerida'    => 'bg-warning text-dark',
                                                'no_requerida' => 'bg-secondary',
                                                default        => 'bg-light text-dark',
                                            };
                                            $planLabel = ($r['plan_pattern'] === '0' || $r['plan_pattern'] === null) ? '<em>Todos los planes</em>' : text($r['plan_pattern']);
                                            $codeLabel = ($r['code'] === '0' || $r['code'] === null) ? '<em>Todos los códigos</em>' : text($r['code']);
                                            ?>
                                            <tr>
                                                <td>#<?php echo text($r['id']); ?></td>
                                                <td><strong><?php echo text($r['insurer_name'] ?? 'Financiador ID ' . $r['insurance_company_id']); ?></strong></td>
                                                <td><code><?php echo $planLabel; ?></code></td>
                                                <td><span class="badge bg-light text-dark border"><?php echo text($r['code_type']); ?></span></td>
                                                <td><code><?php echo $codeLabel; ?></code></td>
                                                <td><span class="badge <?php echo $modeBadge; ?>"><?php echo text(strtoupper($r['auth_mode'])); ?></span></td>
                                                <td><?php echo $r['max_quantity'] !== null ? text($r['max_quantity']) : '-'; ?></td>
                                                <td><span class="badge bg-outline-secondary border text-dark"><?php echo text($r['priority']); ?></span></td>
                                                <td>
                                                    <?php if ((int)$r['active'] === 1): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success"><?php echo xlt('Activa'); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?php echo xlt('Inactiva'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <form method="post" action="dashboard.php?tab=config" class="d-inline">
                                                            <input type="hidden" name="action" value="toggle_auth_rule">
                                                            <input type="hidden" name="rule_id" value="<?php echo text($r['id']); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="<?php echo xlt('Activar/Inactivar'); ?>">
                                                                <i class="fa-solid fa-power-off"></i>
                                                            </button>
                                                        </form>
                                                        <form method="post" action="dashboard.php?tab=config" class="d-inline" onsubmit="return confirm('<?php echo xlt('¿Eliminar esta regla?'); ?>');">
                                                            <input type="hidden" name="action" value="delete_auth_rule">
                                                            <input type="hidden" name="rule_id" value="<?php echo text($r['id']); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="<?php echo xlt('Eliminar'); ?>">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        if (!$hasAuthRules): ?>
                                            <tr>
                                                <td colspan="10" class="text-center py-4 text-muted">
                                                    <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
                                                    <?php echo xlt('No hay reglas de autorización configuradas en la base de datos.'); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seccion 2: Reglas de Frecuencia y Periodicidad -->
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h5 class="mb-0 fw-bold">
                                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i><?php echo xlt('Reglas de Frecuencia y Periodicidad'); ?>
                            </h5>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNewFreqRule" data-toggle="modal" data-target="#modalNewFreqRule" onclick="openModal('modalNewFreqRule')">
                                <i class="fa-solid fa-plus me-1"></i><?php echo xlt('Nueva Regla de Frecuencia'); ?>
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                <?php echo xlt('Valida si una práctica puede realizarse evaluando la fecha de facturación previa en billing y solicitudes activas en covl_authorizations dentro del intervalo mínimo configurado.'); ?>
                            </p>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th># ID</th>
                                            <th><?php echo xlt('Financiador'); ?></th>
                                            <th><?php echo xlt('Tipo Código'); ?></th>
                                            <th><?php echo xlt('Código Práctica'); ?></th>
                                            <th><?php echo xlt('Intervalo Mínimo'); ?></th>
                                            <th><?php echo xlt('Máx. Anual'); ?></th>
                                            <th><?php echo xlt('Severidad'); ?></th>
                                            <th><?php echo xlt('Estado'); ?></th>
                                            <th><?php echo xlt('Acciones'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $resFreqRules = sqlStatement(
                                            "SELECT f.*, ic.name AS insurer_name
                                             FROM covl_frequency_rules f
                                             LEFT JOIN insurance_companies ic ON ic.id = f.insurance_company_id
                                             ORDER BY f.id ASC"
                                        );
                                        $hasFreqRules = false;
                                        while ($f = sqlFetchArray($resFreqRules)) {
                                            $hasFreqRules = true;
                                            $sevBadge = ($f['severity'] === 'bloqueo') ? 'bg-danger' : 'bg-warning text-dark';
                                            ?>
                                            <tr>
                                                <td>#<?php echo text($f['id']); ?></td>
                                                <td><strong><?php echo text($f['insurer_name'] ?? 'Financiador ID ' . $f['insurance_company_id']); ?></strong></td>
                                                <td><span class="badge bg-light text-dark border"><?php echo text($f['code_type']); ?></span></td>
                                                <td><code><?php echo text($f['code']); ?></code></td>
                                                <td><strong><?php echo text($f['min_interval_days']); ?> <?php echo xlt('días'); ?></strong></td>
                                                <td><?php echo $f['max_per_year'] !== null ? text($f['max_per_year']) . ' / año' : '<em>Sin límite</em>'; ?></td>
                                                <td><span class="badge <?php echo $sevBadge; ?>"><?php echo text(strtoupper($f['severity'])); ?></span></td>
                                                <td>
                                                    <?php if ((int)$f['active'] === 1): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success"><?php echo xlt('Activa'); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?php echo xlt('Inactiva'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <form method="post" action="dashboard.php?tab=config" class="d-inline">
                                                            <input type="hidden" name="action" value="toggle_freq_rule">
                                                            <input type="hidden" name="rule_id" value="<?php echo text($f['id']); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="<?php echo xlt('Activar/Inactivar'); ?>">
                                                                <i class="fa-solid fa-power-off"></i>
                                                            </button>
                                                        </form>
                                                        <form method="post" action="dashboard.php?tab=config" class="d-inline" onsubmit="return confirm('<?php echo xlt('¿Eliminar esta regla?'); ?>');">
                                                            <input type="hidden" name="action" value="delete_freq_rule">
                                                            <input type="hidden" name="rule_id" value="<?php echo text($f['id']); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="<?php echo xlt('Eliminar'); ?>">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        if (!$hasFreqRules): ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-4 text-muted">
                                                    <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
                                                    <?php echo xlt('No hay reglas de frecuencia configuradas en la base de datos.'); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL: Nueva Regla de Autorizacion -->
            <div class="modal fade" id="modalNewAuthRule" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="post" action="dashboard.php?tab=config">
                            <input type="hidden" name="action" value="add_auth_rule">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold"><i class="fa-solid fa-shield-halved text-primary me-2"></i><?php echo xlt('Nueva Regla de Autorización'); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" onclick="closeModal('modalNewAuthRule')"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold"><?php echo xlt('Financiador / Obra Social'); ?></label>
                                        <select name="insurance_company_id" class="form-select" required>
                                            <option value=""><?php echo xlt('-- Seleccionar Financiador --'); ?></option>
                                            <?php foreach ($insurersList as $ins): ?>
                                                <option value="<?php echo text($ins['id']); ?>"><?php echo text($ins['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold"><?php echo xlt('Modo de Autorización'); ?></label>
                                        <select name="auth_mode" class="form-select" required>
                                            <option value="requerida"><?php echo xlt('Requerida (Trámite pendiente)'); ?></option>
                                            <option value="automatica"><?php echo xlt('Automática (Auto-aprobación)'); ?></option>
                                            <option value="no_requerida"><?php echo xlt('No Requerida (Exenta)'); ?></option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold"><?php echo xlt('Patrón de Plan (0 = Todos los planes)'); ?></label>
                                        <input type="text" name="plan_pattern" class="form-control" value="0" placeholder="0 o ej: GOLD%">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold"><?php echo xlt('Tipo de Código'); ?></label>
                                        <input type="text" name="code_type" class="form-control" value="NNAR" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold"><?php echo xlt('Código de la Práctica (0 = Todos)'); ?></label>
                                        <input type="text" name="code" class="form-control" value="0" placeholder="0 o ej: 380601">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold"><?php echo xlt('Máx. Cant. Auto'); ?></label>
                                        <input type="number" name="max_quantity" class="form-control" placeholder="Ej: 2">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold"><?php echo xlt('Prioridad (Menor = Más Alta)'); ?></label>
                                        <input type="number" name="priority" class="form-control" value="100" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold"><?php echo xlt('Notas / Descripción'); ?></label>
                                        <input type="text" name="notes" class="form-control" placeholder="Justificación o normativa aplicable">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" onclick="closeModal('modalNewAuthRule')"><?php echo xlt('Cancelar'); ?></button>
                                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i><?php echo xlt('Guardar Regla'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- MODAL: Nueva Regla de Frecuencia -->
            <div class="modal fade" id="modalNewFreqRule" tabindex="-1" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="post" action="dashboard.php?tab=config">
                            <input type="hidden" name="action" value="add_freq_rule">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i><?php echo xlt('Nueva Regla de Frecuencia'); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" onclick="closeModal('modalNewFreqRule')"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold"><?php echo xlt('Financiador / Obra Social'); ?></label>
                                        <select name="insurance_company_id" class="form-select" required>
                                            <option value=""><?php echo xlt('-- Seleccionar Financiador --'); ?></option>
                                            <?php foreach ($insurersList as $ins): ?>
                                                <option value="<?php echo text($ins['id']); ?>"><?php echo text($ins['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold"><?php echo xlt('Severidad de Restricción'); ?></label>
                                        <select name="severity" class="form-select" required>
                                            <option value="alerta"><?php echo xlt('Alerta (Emite advertencia, permite continuar)'); ?></option>
                                            <option value="bloqueo"><?php echo xlt('Bloqueo (Impide generar la orden)'); ?></option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold"><?php echo xlt('Tipo de Código'); ?></label>
                                        <input type="text" name="code_type" class="form-control" value="NNAR" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold"><?php echo xlt('Código Práctica'); ?></label>
                                        <input type="text" name="code" class="form-control" placeholder="Ej: 380601" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold"><?php echo xlt('Intervalo Mínimo (Días)'); ?></label>
                                        <input type="number" name="min_interval_days" class="form-control" placeholder="Ej: 180" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold"><?php echo xlt('Máximo de Veces por Año (Opcional)'); ?></label>
                                        <input type="number" name="max_per_year" class="form-control" placeholder="Ej: 2 (dejar vacío si es sin límite)">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold"><?php echo xlt('Notas / Referencia Normativa'); ?></label>
                                        <input type="text" name="notes" class="form-control" placeholder="Ej: Resolución o norma de frecuencia">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" onclick="closeModal('modalNewFreqRule')"><?php echo xlt('Cancelar'); ?></button>
                                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i><?php echo xlt('Guardar Regla'); ?></button>
                            </div>
                        </form>
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
                                    <option value="ARS">ARS — <?php echo xlt('Peso argentino'); ?></option>
                                    <option value="USD">USD — <?php echo xlt('Dólar'); ?></option>
                                    <option value="EUR">EUR — <?php echo xlt('Euro'); ?></option>
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

    <!-- Configuración JS inyectada desde PHP -->
    <script>
    const covlConfig = {
        csrfToken:    <?= json_encode($csrfToken) ?>,
        baseApiUrl:   <?= json_encode($moduleBase . '/api') ?>,
        i18n:         <?= json_encode($covlI18n, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
    };
    </script>
    <script src="<?= attr($moduleBase) ?>/assets/js/providers-crud.js"></script>
    <script src="<?= attr($moduleBase) ?>/assets/js/batches-crud.js"></script>

    <!-- Script de soporte universal para modales (Bootstrap 4 / Bootstrap 5 / JS Nativo) -->
    <script>
        function openModal(id) {
            var el = document.getElementById(id);
            if (!el) return;
            if (window.bootstrap && window.bootstrap.Modal) {
                var modal = window.bootstrap.Modal.getOrCreateInstance(el);
                if (modal) { modal.show(); return; }
            }
            if (window.jQuery && typeof window.jQuery(el).modal === 'function') {
                window.jQuery(el).modal('show');
                return;
            }
            el.style.display = 'block';
            el.classList.add('show');
            document.body.classList.add('modal-open');
        }

        function closeModal(id) {
            var el = document.getElementById(id);
            if (!el) return;
            if (window.bootstrap && window.bootstrap.Modal) {
                var modal = window.bootstrap.Modal.getInstance(el);
                if (modal) { modal.hide(); return; }
            }
            if (window.jQuery && typeof window.jQuery(el).modal === 'function') {
                window.jQuery(el).modal('hide');
                return;
            }
            el.style.display = 'none';
            el.classList.remove('show');
            document.body.classList.remove('modal-open');
        }
    </script>
</body>
</html>

