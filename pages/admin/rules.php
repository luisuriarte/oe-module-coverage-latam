<?php

/**
 * oe-module-coverage-latam — Página de Administración de Reglas
 *
 * Interfaz CRUD para covl_auth_rules y covl_frequency_rules.
 * Utiliza flag-icons (lipis/flag-icons via jsDelivr CDN) para mostrar
 * banderas de país en pills, filtros y formularios modales.
 *
 * @package   OpenEMR\Modules\CoverageLatam
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/../../../globals.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'docs')) {
    die(xlt('Acceso denegado'));
}

// Cargar paquetes de país instalados
$countryPacks = [];
$res = sqlStatement("SELECT country_code, name FROM covl_country_packs ORDER BY name");
while ($row = sqlFetchArray($res)) {
    $countryPacks[] = $row;
}

// Cargar tipos de código disponibles
$codeTypes = [];
$res2 = sqlStatement("SELECT ct_key, ct_label FROM code_types WHERE ct_active = 1 ORDER BY ct_label");
while ($row = sqlFetchArray($res2)) {
    $codeTypes[] = $row;
}

$csrfToken  = CsrfUtils::collectCsrfToken();
$moduleBase = $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-coverage-latam/pages';

// ---------------------------------------------------------------------------
// Helper: genera el markup de un FlagSelect personalizado con banderas.
// Reemplaza el <select> nativo para poder mostrar iconos dentro de las opciones.
//
// @param string $inputId     ID del <input type="hidden"> interno (que el JS lee)
// @param array  $options     [['value'=>'', 'label'=>'— Todos —', 'code'=>''], ...]
// @param bool   $required    Si el campo es obligatorio
// @param string $extraClass  Clases CSS adicionales para el wrapper
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= xlt('Reglas de Configuración') ?> — Coberturas LATAM</title>
    <meta name="description" content="<?= xla('Gestión CRUD de reglas de autorización y frecuencia por financiador y país') ?>">

    <?php Header::setupHeader(['opener']); ?>

    <!-- flag-icons: Local vendor (lipis/flag-icons) -->
    <link rel="stylesheet" href="<?= attr($moduleBase) ?>/assets/vendor/flag-icons/css/flag-icons.min.css">

    <link rel="stylesheet" href="<?= attr($moduleBase) ?>/assets/css/admin-rules.css">
</head>
<body>
<div class="container-fluid py-3">

    <!-- ================================================================
         Encabezado de página
    ================================================================= -->
    <div class="covl-page-header">
        <span style="font-size:1.75rem">📋</span>
        <div>
            <h1><?= xlt('Reglas de Configuración') ?></h1>
            <div class="covl-subtitle"><?= xlt('Autorizaciones previas · Frecuencia · Gestión por financiador y país') ?></div>
        </div>
    </div>

    <!-- ================================================================
         Selector de país (pills con bandera)
    ================================================================= -->
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

    <!-- ================================================================
         Tabs de navegación
    ================================================================= -->
    <ul class="nav nav-tabs mb-0" id="covl-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="tab-auth-link" data-toggle="tab" href="#tab-auth"
               role="tab" data-covl-tab="auth">
                ⚑ <?= xlt('Autorizaciones') ?>
                <span class="badge badge-secondary ml-1" id="covl-auth-total"></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab-freq-link" data-toggle="tab" href="#tab-freq"
               role="tab" data-covl-tab="freq">
                ⏱ <?= xlt('Frecuencia') ?>
                <span class="badge badge-secondary ml-1" id="covl-freq-total"></span>
            </a>
        </li>
    </ul>

    <div class="tab-content border border-top-0 bg-white p-0 rounded-bottom" id="covl-tab-content">

        <!-- ============================================================
             TAB: REGLAS DE AUTORIZACIÓN
        ============================================================= -->
        <div class="tab-pane fade show active" id="tab-auth" role="tabpanel">

            <div class="covl-filters">
                <!-- País con FlagSelect -->
                <div class="filter-group">
                    <label><?= xlt('País') ?></label>
                    <?= covl_flag_select('flt-auth-country', $allCountryOpts, false, 'fs-compact') ?>
                </div>
                <!-- Financiador -->
                <div class="filter-group" style="min-width:200px">
                    <label><?= xlt('Financiador') ?></label>
                    <select id="flt-auth-insurer" class="form-control form-control-sm" data-covl-filter-auth>
                        <option value=""><?= xlt('— Todos —') ?></option>
                    </select>
                </div>
                <!-- Tipo de código -->
                <div class="filter-group">
                    <label><?= xlt('Tipo cód.') ?></label>
                    <select id="flt-auth-codetype" class="form-control form-control-sm" data-covl-filter-auth>
                        <option value=""><?= xlt('— Todos —') ?></option>
                        <?php foreach ($codeTypes as $ct): ?>
                        <option value="<?= attr($ct['ct_key']) ?>"><?= text($ct['ct_key']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><?= xlt('Código') ?></label>
                    <input type="text" id="flt-auth-code" data-covl-filter-auth
                           placeholder="<?= xla('ej: 380601') ?>" style="width:110px">
                </div>
                <div class="filter-group">
                    <label><?= xlt('Estado') ?></label>
                    <select id="flt-auth-active" class="form-control form-control-sm" data-covl-filter-auth>
                        <option value=""><?= xlt('— Todos —') ?></option>
                        <option value="1"><?= xlt('Activas') ?></option>
                        <option value="0"><?= xlt('Inactivas') ?></option>
                    </select>
                </div>
                <div class="ml-auto d-flex align-items-end">
                    <button class="btn btn-primary btn-sm" onclick="COVL.Auth.openCreate()">
                        + <?= xlt('Nueva Regla') ?>
                    </button>
                </div>
            </div>

            <div class="covl-table-wrapper">
                <table class="covl-table">
                    <thead>
                        <tr>
                            <th><?= xlt('País') ?></th>
                            <th><?= xlt('Financiador') ?></th>
                            <th><?= xlt('Patrón de plan') ?></th>
                            <th><?= xlt('Tipo cód.') ?></th>
                            <th><?= xlt('Código') ?></th>
                            <th><?= xlt('Modo') ?></th>
                            <th><?= xlt('Máx. cant.') ?></th>
                            <th><?= xlt('Estado') ?></th>
                            <th><?= xlt('Acciones') ?></th>
                        </tr>
                    </thead>
                    <tbody id="covl-auth-tbody">
                        <tr><td colspan="9"><div class="covl-loading"><div class="covl-spinner"></div> <?= xlt('Cargando...') ?></div></td></tr>
                    </tbody>
                </table>
                <div class="covl-pagination" id="covl-auth-pager"></div>
            </div>
        </div>

        <!-- ============================================================
             TAB: REGLAS DE FRECUENCIA
        ============================================================= -->
        <div class="tab-pane fade" id="tab-freq" role="tabpanel">

            <div class="covl-filters">
                <div class="filter-group">
                    <label><?= xlt('País') ?></label>
                    <?= covl_flag_select('flt-freq-country', $allCountryOpts, false, 'fs-compact') ?>
                </div>
                <div class="filter-group" style="min-width:200px">
                    <label><?= xlt('Financiador') ?></label>
                    <select id="flt-freq-insurer" class="form-control form-control-sm" data-covl-filter-freq>
                        <option value=""><?= xlt('— Todos —') ?></option>
                    </select>
                </div>
                <div class="filter-group">
                    <label><?= xlt('Tipo cód.') ?></label>
                    <select id="flt-freq-codetype" class="form-control form-control-sm" data-covl-filter-freq>
                        <option value=""><?= xlt('— Todos —') ?></option>
                        <?php foreach ($codeTypes as $ct): ?>
                        <option value="<?= attr($ct['ct_key']) ?>"><?= text($ct['ct_key']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><?= xlt('Código') ?></label>
                    <input type="text" id="flt-freq-code" data-covl-filter-freq
                           placeholder="<?= xla('ej: 380601') ?>" style="width:110px">
                </div>
                <div class="filter-group">
                    <label><?= xlt('Severidad') ?></label>
                    <select id="flt-freq-severity" class="form-control form-control-sm" data-covl-filter-freq>
                        <option value=""><?= xlt('— Todas —') ?></option>
                        <option value="alerta"><?= xlt('Alerta') ?></option>
                        <option value="bloqueo"><?= xlt('Bloqueo') ?></option>
                    </select>
                </div>
                <div class="filter-group">
                    <label><?= xlt('Estado') ?></label>
                    <select id="flt-freq-active" class="form-control form-control-sm" data-covl-filter-freq>
                        <option value=""><?= xlt('— Todos —') ?></option>
                        <option value="1"><?= xlt('Activas') ?></option>
                        <option value="0"><?= xlt('Inactivas') ?></option>
                    </select>
                </div>
                <div class="ml-auto d-flex align-items-end">
                    <button class="btn btn-primary btn-sm" onclick="COVL.Freq.openCreate()">
                        + <?= xlt('Nueva Regla') ?>
                    </button>
                </div>
            </div>

            <div class="covl-table-wrapper">
                <table class="covl-table">
                    <thead>
                        <tr>
                            <th><?= xlt('País') ?></th>
                            <th><?= xlt('Financiador') ?></th>
                            <th><?= xlt('Tipo cód.') ?></th>
                            <th><?= xlt('Código') ?></th>
                            <th><?= xlt('Intervalo mín.') ?></th>
                            <th><?= xlt('Máx/año') ?></th>
                            <th><?= xlt('Severidad') ?></th>
                            <th><?= xlt('Estado') ?></th>
                            <th><?= xlt('Acciones') ?></th>
                        </tr>
                    </thead>
                    <tbody id="covl-freq-tbody">
                        <tr><td colspan="9"><div class="covl-loading"><div class="covl-spinner"></div> <?= xlt('Cargando...') ?></div></td></tr>
                    </tbody>
                </table>
                <div class="covl-pagination" id="covl-freq-pager"></div>
            </div>
        </div>

    </div><!-- /tab-content -->

</div><!-- /container-fluid -->

<!-- ================================================================
     MODAL: Regla de Autorización
================================================================= -->
<div class="modal fade" id="covlAuthModal" tabindex="-1" role="dialog" aria-labelledby="covlAuthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header covl-modal-header">
                <h5 class="modal-title" id="covlAuthModalLabel"><?= xlt('Regla de Autorización') ?></h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="covl-auth-form" onsubmit="event.preventDefault(); COVL.Auth.save();">
            <div class="modal-body">
                <input type="hidden" id="fld-auth-id">

                <div class="form-row">
                    <!-- País con FlagSelect grande -->
                    <div class="form-group col-md-4">
                        <label><?= xlt('País') ?> <span class="text-danger">*</span></label>
                        <?= covl_flag_select('fld-auth-country', $reqCountryOpts, true, 'w-100') ?>
                        <small class="form-text text-muted"><?= xlt('País del paquete de configuración') ?></small>
                    </div>
                    <div class="form-group col-md-8">
                        <label for="fld-auth-insurer"><?= xlt('Financiador') ?> <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="fld-auth-insurer" required>
                            <option value=""><?= xlt('Cargando...') ?></option>
                        </select>
                        <small class="form-text text-muted"><?= xlt('Seleccioná "Todos" (0) para regla genérica del país') ?></small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fld-auth-plan-pattern"><?= xlt('Patrón de plan') ?></label>
                        <input type="text" class="form-control form-control-sm" id="fld-auth-plan-pattern"
                               placeholder="<?= xla('Vacío = todos los planes; soporta %') ?>">
                        <small class="form-text text-muted"><?= xlt('Dejalo vacío para aplicar a todos los planes (se guarda como 0)') ?></small>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fld-auth-codetype"><?= xlt('Tipo de código') ?> <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="fld-auth-codetype" required>
                            <option value=""><?= xlt('— Seleccioná —') ?></option>
                            <?php foreach ($codeTypes as $ct): ?>
                            <option value="<?= attr($ct['ct_key']) ?>"><?= text($ct['ct_key']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fld-auth-code"><?= xlt('Código') ?></label>
                        <input type="text" class="form-control form-control-sm" id="fld-auth-code"
                               placeholder="<?= xla('Vacío = todos') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="fld-auth-mode"><?= xlt('Modo de autorización') ?> <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="fld-auth-mode" required>
                            <option value="requerida"><?= xlt('Requerida') ?></option>
                            <option value="automatica"><?= xlt('Automática') ?></option>
                            <option value="no_requerida"><?= xlt('No requerida') ?></option>
                        </select>
                    </div>
                    <div class="form-group col-md-3" id="grp-auth-max-qty" style="display:none">
                        <label for="fld-auth-max-qty"><?= xlt('Cant. máxima automática') ?></label>
                        <input type="number" class="form-control form-control-sm" id="fld-auth-max-qty"
                               min="1" placeholder="<?= xla('ej: 6') ?>">
                        <small class="form-text text-muted"><?= xlt('Si se supera → escala a requerida') ?></small>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="fld-auth-priority"><?= xlt('Prioridad') ?></label>
                        <input type="number" class="form-control form-control-sm" id="fld-auth-priority"
                               min="1" max="999" value="100">
                        <small class="form-text text-muted"><?= xlt('Menor = más prioritario') ?></small>
                    </div>
                    <div class="form-group col-md-3 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="fld-auth-active" checked>
                            <label class="form-check-label" for="fld-auth-active"><?= xlt('Regla activa') ?></label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="fld-auth-notes"><?= xlt('Notas / Justificación') ?></label>
                    <textarea class="form-control form-control-sm" id="fld-auth-notes" rows="2"
                              placeholder="<?= xla('Ej: TAC de cráneo — requiere autorización previa según RES. 925/2000') ?>"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?= xlt('Cancelar') ?></button>
                <button type="submit" class="btn btn-primary btn-sm">💾 <?= xlt('Guardar regla') ?></button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: Regla de Frecuencia
================================================================= -->
<div class="modal fade" id="covlFreqModal" tabindex="-1" role="dialog" aria-labelledby="covlFreqModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header covl-modal-header">
                <h5 class="modal-title" id="covlFreqModalLabel"><?= xlt('Regla de Frecuencia') ?></h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="covl-freq-form" onsubmit="event.preventDefault(); COVL.Freq.save();">
            <div class="modal-body">
                <input type="hidden" id="fld-freq-id">

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label><?= xlt('País') ?> <span class="text-danger">*</span></label>
                        <?= covl_flag_select('fld-freq-country', $reqCountryOpts, true, 'w-100') ?>
                    </div>
                    <div class="form-group col-md-8">
                        <label for="fld-freq-insurer"><?= xlt('Financiador') ?> <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="fld-freq-insurer" required>
                            <option value=""><?= xlt('Cargando...') ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="fld-freq-codetype"><?= xlt('Tipo de código') ?> <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="fld-freq-codetype" required>
                            <option value=""><?= xlt('— Seleccioná —') ?></option>
                            <?php foreach ($codeTypes as $ct): ?>
                            <option value="<?= attr($ct['ct_key']) ?>"><?= text($ct['ct_key']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="fld-freq-code"><?= xlt('Código') ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="fld-freq-code"
                               required placeholder="<?= xla('ej: 380601') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="fld-freq-max-year"><?= xlt('Máximo por año') ?></label>
                        <input type="number" class="form-control form-control-sm" id="fld-freq-max-year"
                               min="1" placeholder="<?= xla('Vacío = sin límite anual') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="fld-freq-interval"><?= xlt('Intervalo mínimo (días)') ?> <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" id="fld-freq-interval"
                               required min="1" placeholder="<?= xla('ej: 180') ?>">
                        <div class="covl-interval-hint" id="freq-interval-hint"></div>
                    </div>
                    <div class="form-group col-md-8">
                        <label><?= xlt('Severidad al detectar violación') ?> <span class="text-danger">*</span></label>
                        <div class="covl-severity-group">
                            <label class="covl-severity-opt sev-alerta">
                                <input type="radio" name="fld-freq-severity" id="fld-freq-severity-alerta" value="alerta" checked>
                                ⚠️ <?= xlt('Alerta') ?>
                                <small class="d-block text-muted" style="font-size:.7rem"><?= xlt('Avisa pero permite continuar') ?></small>
                            </label>
                            <label class="covl-severity-opt sev-bloqueo">
                                <input type="radio" name="fld-freq-severity" id="fld-freq-severity-bloqueo" value="bloqueo">
                                🚫 <?= xlt('Bloqueo') ?>
                                <small class="d-block text-muted" style="font-size:.7rem"><?= xlt('Impide generar la orden') ?></small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-9">
                        <label for="fld-freq-notes"><?= xlt('Notas / Referencia normativa') ?></label>
                        <textarea class="form-control form-control-sm" id="fld-freq-notes" rows="2"
                                  placeholder="<?= xla('Ej: TAC de cráneo — intervalo mínimo 180 días según normativa PAMI') ?>"></textarea>
                    </div>
                    <div class="form-group col-md-3 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="fld-freq-active" checked>
                            <label class="form-check-label" for="fld-freq-active"><?= xlt('Regla activa') ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?= xlt('Cancelar') ?></button>
                <button type="submit" class="btn btn-primary btn-sm">💾 <?= xlt('Guardar regla') ?></button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- ================================================================
     Configuración JS inyectada desde PHP
================================================================= -->
<script>
const covlConfig = {
    csrfToken:    <?= json_encode($csrfToken) ?>,
    baseApiUrl:   <?= json_encode($moduleBase . '/api') ?>,
    countryPacks: <?= json_encode($countryPacks) ?>,
};
</script>
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

</body>
</html>
