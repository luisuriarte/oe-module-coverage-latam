-- ===========================================================================
-- uninstall.sql  -  oe-module-coverage-latam
-- Descripción: Eliminación de tablas y datos del módulo de Coberturas LATAM.
-- ADVERTENCIA: Este script elimina permanentemente todos los datos del módulo.
-- ===========================================================================

-- Se eliminan primero las tablas con FK dependientes
DROP TABLE IF EXISTS `covl_settlement_items`;
DROP TABLE IF EXISTS `covl_settlement_batches`;
DROP TABLE IF EXISTS `covl_authorization_history`;
DROP TABLE IF EXISTS `covl_authorizations`;
DROP TABLE IF EXISTS `covl_auth_rules`;
DROP TABLE IF EXISTS `covl_frequency_rules`;
DROP TABLE IF EXISTS `covl_provider_coverage`;
DROP TABLE IF EXISTS `covl_integration_log`;
DROP TABLE IF EXISTS `covl_adapters`;
DROP TABLE IF EXISTS `covl_country_code_maps`;
DROP TABLE IF EXISTS `covl_country_packs`;
DROP TABLE IF EXISTS `covl_config`;

-- Eliminar el tipo de código del Nomenclador Nacional Argentino
DELETE FROM `code_types` WHERE `ct_key` = 'NNAR';

-- Eliminar los códigos cargados con el tipo NNAR (si hubiera)
-- DELETE FROM `codes` WHERE `code_type` = 200;
