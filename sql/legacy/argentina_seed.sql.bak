-- ===========================================================================
-- argentina_seed.sql  -  oe-module-coverage-latam / Paquete Argentina
-- Descripción: Datos iniciales del paquete de configuración para Argentina.
--              Carga el adaptador manual, las reglas de autorización base
--              y las reglas de frecuencia mínima comunes.
-- Ejecutar DESPUÉS de install.sql
-- ===========================================================================

-- ---------------------------------------------------------------------------
-- Marcar el paquete Argentina como cargado
-- ---------------------------------------------------------------------------
UPDATE `covl_country_packs`
SET `default_rules_loaded` = 1
WHERE `country_code` = 'AR';

-- ===========================================================================
-- REGLAS DE AUTORIZACIÓN POR DEFECTO PARA ARGENTINA
--
-- Estas reglas son genéricas (insurance_company_id = 0 no existe, por eso
-- se usan como plantilla por país_code; el financiador específico puede
-- sobrescribir con reglas más prioritarias).
--
-- IMPORTANTE: Acá se cargan reglas de EJEMPLO orientativas.
-- Cada implementación deberá ajustar según el contrato con cada financiador.
-- ===========================================================================

-- ---------------------------------------------------------------------------
-- Prácticas de ALTA COMPLEJIDAD que siempre requieren autorización
-- Catálogo Nomenclador Nacional Argentino (NNAR) — códigos ilustrativos
-- ---------------------------------------------------------------------------

-- Tomografía computada (TAC)
INSERT IGNORE INTO `covl_auth_rules`
  (`insurance_company_id`, `plan_pattern`, `code_type`, `code`, `auth_mode`, `priority`, `active`, `country_code`, `notes`)
VALUES
  (0, NULL, 'NNAR', '380601', 'requerida', 100, 1, 'AR', 'TAC de cráneo sin contraste — requiere autorización previa'),
  (0, NULL, 'NNAR', '380602', 'requerida', 100, 1, 'AR', 'TAC de cráneo con contraste — requiere autorización previa'),
  (0, NULL, 'NNAR', '380701', 'requerida', 100, 1, 'AR', 'TAC de tórax — requiere autorización previa'),
  (0, NULL, 'NNAR', '380801', 'requerida', 100, 1, 'AR', 'TAC de abdomen — requiere autorización previa');

-- Resonancia magnética (RMN)
INSERT IGNORE INTO `covl_auth_rules`
  (`insurance_company_id`, `plan_pattern`, `code_type`, `code`, `auth_mode`, `priority`, `active`, `country_code`, `notes`)
VALUES
  (0, NULL, 'NNAR', '390101', 'requerida', 100, 1, 'AR', 'RMN de cerebro — requiere autorización previa'),
  (0, NULL, 'NNAR', '390201', 'requerida', 100, 1, 'AR', 'RMN de columna lumbar — requiere autorización previa'),
  (0, NULL, 'NNAR', '390301', 'requerida', 100, 1, 'AR', 'RMN de rodilla — requiere autorización previa');

-- Cirugías programadas
INSERT IGNORE INTO `covl_auth_rules`
  (`insurance_company_id`, `plan_pattern`, `code_type`, `code`, `auth_mode`, `priority`, `active`, `country_code`, `notes`)
VALUES
  (0, NULL, 'NNAR', '420101', 'requerida', 100, 1, 'AR', 'Colecistectomía laparoscópica — requiere autorización previa'),
  (0, NULL, 'NNAR', '420201', 'requerida', 100, 1, 'AR', 'Apendicectomía — requiere autorización previa');

-- Consultas de especialidad — generalmente automáticas o sin requerimiento
INSERT IGNORE INTO `covl_auth_rules`
  (`insurance_company_id`, `plan_pattern`, `code_type`, `code`, `auth_mode`, `priority`, `active`, `country_code`, `notes`)
VALUES
  (0, NULL, 'NNAR', '010101', 'automatica', 200, 1, 'AR', 'Consulta médica ambulatoria — autorización automática'),
  (0, NULL, 'NNAR', '010201', 'automatica', 200, 1, 'AR', 'Control de salud anual — autorización automática');

-- ===========================================================================
-- REGLAS DE FRECUENCIA POR DEFECTO PARA ARGENTINA
-- ===========================================================================

-- Tomografía computada — mínimo 6 meses entre estudios del mismo tipo
INSERT IGNORE INTO `covl_frequency_rules`
  (`insurance_company_id`, `code_type`, `code`, `min_interval_days`, `max_per_year`, `severity`, `active`, `country_code`, `notes`)
VALUES
  (0, 'NNAR', '380601', 180, 2, 'alerta', 1, 'AR', 'TAC de cráneo sin contraste — intervalo mínimo 180 días (6 meses)'),
  (0, 'NNAR', '380602', 180, 2, 'alerta', 1, 'AR', 'TAC de cráneo con contraste — intervalo mínimo 180 días'),
  (0, 'NNAR', '380701', 180, 2, 'alerta', 1, 'AR', 'TAC de tórax — intervalo mínimo 180 días'),
  (0, 'NNAR', '380801', 180, 2, 'alerta', 1, 'AR', 'TAC de abdomen — intervalo mínimo 180 días');

-- Resonancia magnética — mínimo 6 meses
INSERT IGNORE INTO `covl_frequency_rules`
  (`insurance_company_id`, `code_type`, `code`, `min_interval_days`, `max_per_year`, `severity`, `active`, `country_code`, `notes`)
VALUES
  (0, 'NNAR', '390101', 180, 2, 'alerta', 1, 'AR', 'RMN de cerebro — intervalo mínimo 180 días'),
  (0, 'NNAR', '390201', 180, 2, 'alerta', 1, 'AR', 'RMN de columna lumbar — intervalo mínimo 180 días'),
  (0, 'NNAR', '390301', 180, 2, 'alerta', 1, 'AR', 'RMN de rodilla — intervalo mínimo 180 días');

-- Ecocardiograma — mínimo 12 meses
INSERT IGNORE INTO `covl_frequency_rules`
  (`insurance_company_id`, `code_type`, `code`, `min_interval_days`, `max_per_year`, `severity`, `active`, `country_code`, `notes`)
VALUES
  (0, 'NNAR', '270101', 365, 1, 'alerta', 1, 'AR', 'Ecocardiograma doppler — intervalo mínimo 365 días (1 año)');

-- Mamografía bilateral — mínimo 12 meses (plan de prevención)
INSERT IGNORE INTO `covl_frequency_rules`
  (`insurance_company_id`, `code_type`, `code`, `min_interval_days`, `max_per_year`, `severity`, `active`, `country_code`, `notes`)
VALUES
  (0, 'NNAR', '340101', 365, 1, 'alerta', 1, 'AR', 'Mamografía bilateral — intervalo mínimo 365 días según plan preventivo');
