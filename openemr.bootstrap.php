<?php

/**
 * oe-module-coverage-latam — Bootstrap
 * Registers menu items and event listeners in OpenEMR.
 *
 * @package   OpenEMR Module
 * @link      http://www.open-emr.org
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Menu\MenuEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @global OpenEMR\Core\ModulesClassLoader $classLoader
 */
$classLoader->registerNamespaceIfNotExists(
    'OpenEMR\\Modules\\CoverageLatam\\',
    __DIR__ . DIRECTORY_SEPARATOR . 'src'
);

/**
 * @var EventDispatcherInterface $eventDispatcher
 * @var array $module
 */
$dispatcher = $GLOBALS['kernel']->getEventDispatcher();

// ---------------------------------------------------------------------------
// Menu Registration
// ---------------------------------------------------------------------------
function oe_module_covlatam_add_menu_item(MenuEvent $event): MenuEvent
{
    $menu    = $event->getMenu();
    $base    = '/interface/modules/custom_modules/oe-module-coverage-latam/pages/dashboard.php';

    $menuDash = new stdClass();
    $menuDash->requirement = 0;
    $menuDash->target      = 'covl';
    $menuDash->menu_id     = 'covl_dashboard';
    $menuDash->label       = xlt('Coberturas LATAM');
    $menuDash->url         = $base . '?tab=dashboard';
    $menuDash->children    = [];
    $menuDash->acl_req     = ['patients', 'demo'];

    $menuAuth = new stdClass();
    $menuAuth->requirement = 0;
    $menuAuth->target      = 'covl';
    $menuAuth->menu_id     = 'covl_authorizations';
    $menuAuth->label       = xlt('Autorizaciones');
    $menuAuth->url         = $base . '?tab=authorizations';
    $menuAuth->children    = [];
    $menuAuth->acl_req     = ['patients', 'demo'];

    $menuBatch = new stdClass();
    $menuBatch->requirement = 0;
    $menuBatch->target      = 'covl';
    $menuBatch->menu_id     = 'covl_batches';
    $menuBatch->label       = xlt('Lotes de Liquidación');
    $menuBatch->url         = $base . '?tab=batches';
    $menuBatch->children    = [];
    $menuBatch->acl_req     = ['patients', 'demo'];

    $menuProviders = new stdClass();
    $menuProviders->requirement = 0;
    $menuProviders->target      = 'covl';
    $menuProviders->menu_id     = 'covl_providers';
    $menuProviders->label       = xlt('Convenios Prestadores');
    $menuProviders->url         = $base . '?tab=providers';
    $menuProviders->children    = [];
    $menuProviders->acl_req     = ['admin', 'docs'];

    $menuConfig = new stdClass();
    $menuConfig->requirement = 0;
    $menuConfig->target      = 'covl';
    $menuConfig->menu_id     = 'covl_config';
    $menuConfig->label       = xlt('Configuración');
    $menuConfig->url         = $base . '?tab=config';
    $menuConfig->children    = [];
    $menuConfig->acl_req     = ['admin', 'docs'];

    $menuRules = new stdClass();
    $menuRules->requirement = 0;
    $menuRules->target      = 'covl';
    $menuRules->menu_id     = 'covl_rules';
    $menuRules->label       = xlt('Reglas de Configuración');
    $menuRules->url         = '/interface/modules/custom_modules/oe-module-coverage-latam/pages/admin/rules.php';
    $menuRules->children    = [];
    $menuRules->acl_req     = ['admin', 'docs'];

    $subMenu = new stdClass();
    $subMenu->requirement = 0;
    $subMenu->target      = 'covl';
    $subMenu->menu_id     = 'covl_submenu';
    $subMenu->label       = xlt('Coberturas LATAM');
    $subMenu->children    = [$menuDash, $menuAuth, $menuBatch, $menuProviders, $menuRules, $menuConfig];
    $subMenu->acl_req     = ['patients', 'demo'];

    $inserted = false;
    foreach ($menu as $item) {
        if ($item->menu_id === 'feeimg' || $item->menu_id === 'service' || strcasecmp($item->label, 'fees') === 0 || strcasecmp($item->label, 'services') === 0 || strcasecmp($item->label, 'servicios') === 0) {
            $item->children[] = $subMenu;
            $inserted = true;
            break;
        }
    }

    if (!$inserted) {
        $menu[] = $subMenu;
    }

    $event->setMenu($menu);
    return $event;
}

$eventDispatcher->addListener(MenuEvent::MENU_UPDATE, 'oe_module_covlatam_add_menu_item');

