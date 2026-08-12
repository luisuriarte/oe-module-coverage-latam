<?php

/**
 * oe-module-coverage-latam — Dashboard Principal
 *
 * Interfaz unificada de gestión de Coberturas LATAM:
 * - Tab 1: Dashboard / Métricas
 * - Tab 2: Autorizaciones Previas
 * - Tab 3: Lotes de Liquidación
 * - Tab 4: Convenios de Prestadores
 * - Tab 5: Reglas de Configuración
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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo xlt('Coberturas LATAM — Gestión'); ?></title>
    <?php Header::setupHeader(['bootstrap', 'fontawesome']); ?>
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

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i><?php echo xlt('Lotes de Liquidación Periódica'); ?></h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo xlt('Presentaciones mensuales por lote hacia obras sociales y prepagas.'); ?></p>
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="fa-solid fa-info-circle me-2 fa-lg"></i>
                        <div><?php echo xlt('El módulo de liquidación por lotes permite agrupar prestaciones para cobro consolidado.'); ?></div>
                    </div>
                </div>
            </div>

        <?php elseif ($activeTab === 'providers'): ?>

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-user-doctor text-primary me-2"></i><?php echo xlt('Convenios Prestadores × Financiador'); ?></h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo xlt('Control de vigencia temporal de convenios por profesional y financiador.'); ?></p>
                </div>
            </div>

        <?php elseif ($activeTab === 'config'): ?>

            <div class="row g-4">
                <!-- Seccion 1: Reglas de Autorizacion Previa -->
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 fw-bold">
                                <i class="fa-solid fa-shield-halved text-primary me-2"></i><?php echo xlt('Reglas de Autorización Previa'); ?>
                            </h5>
                            <span class="badge bg-light text-dark border"><?php echo xlt('covl_auth_rules'); ?></span>
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
                                            <th><?php echo xlt('Modo de Autorización'); ?></th>
                                            <th><?php echo xlt('Máx. Auto'); ?></th>
                                            <th><?php echo xlt('Prioridad'); ?></th>
                                            <th><?php echo xlt('Estado'); ?></th>
                                            <th><?php echo xlt('Notas'); ?></th>
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
                                                <td><small class="text-muted"><?php echo text($r['notes'] ?? ''); ?></small></td>
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
                        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 fw-bold">
                                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i><?php echo xlt('Reglas de Frecuencia y Periodicidad'); ?>
                            </h5>
                            <span class="badge bg-light text-dark border"><?php echo xlt('covl_frequency_rules'); ?></span>
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
                                            <th><?php echo xlt('Intervalo Mínimo (Días)'); ?></th>
                                            <th><?php echo xlt('Máx. Anual'); ?></th>
                                            <th><?php echo xlt('Severidad'); ?></th>
                                            <th><?php echo xlt('Estado'); ?></th>
                                            <th><?php echo xlt('Notas'); ?></th>
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
                                                <td><small class="text-muted"><?php echo text($f['notes'] ?? ''); ?></small></td>
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

        <?php endif; ?>

    </div>
</body>
</html>
