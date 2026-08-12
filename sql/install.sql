-- ===========================================================================
-- install.sql  -  oe-module-coverage-latam
-- Descripción: Inicialización de base de datos para el módulo de
--              Gestión de Coberturas LATAM.
-- Target:      OpenEMR 8.0+
-- Prefijo:     covl_ (coverage-latam)
-- ===========================================================================

-- ---------------------------------------------------------------------------
-- 1. covl_config — Configuración general del módulo por sede
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `covl_config` (
  `id`                       int(11)      NOT NULL AUTO_INCREMENT         COMMENT 'Clave primaria autoincremental',
  `facility_id`              int(11)      NOT NULL                        COMMENT 'FK → facility.id (0 = configuración global)',
  `country_code`             char(2)      NOT NULL DEFAULT 'AR'           COMMENT 'Código de país ISO 3166-1 alpha-2 activo (AR, CL, CO, MX, etc.)',
  `default_code_type`        varchar(15)  DEFAULT NULL                    COMMENT 'ct_key del catálogo de códigos por defecto para billing LATAM (ej: NNAR para Nomenclador Nacional AR)',
  `settlement_mode`          enum('lote','claim','ambos') NOT NULL DEFAULT 'lote' COMMENT 'Modo de liquidación preferido: lote periódico, claim individual o ambos',
  `settlement_period`        enum('mensual','quincenal','semanal') NOT NULL DEFAULT 'mensual' COMMENT 'Periodicidad de armado de lotes de liquidación',
  `auto_auth_enabled`        tinyint(1)   NOT NULL DEFAULT 1              COMMENT 'Habilita el motor de autorizaciones automáticas (1=sí, 0=no)',
  `frequency_check_enabled`  tinyint(1)   NOT NULL DEFAULT 1              COMMENT 'Habilita la validación de frecuencia/periodicidad entre prácticas (1=sí, 0=no)',
  `provider_coverage_check`  tinyint(1)   NOT NULL DEFAULT 1              COMMENT 'Valida convenio prestador-financiador al asignar turno y en el check-in (1=sí, 0=no)',
  `created_at`               datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de creación del registro',
  `updated_at`               datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha y hora de última modificación',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_covl_config_facility` (`facility_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Configuración general del módulo de Coberturas LATAM por sede o instalación';

-- ---------------------------------------------------------------------------
-- 2. covl_authorizations — Autorizaciones previas de prácticas
-- Corrección FK types: pid, encounter_id, insurance_data_id y requested_by
--   usan int(11) para coincidir con patient_data.pid, form_encounter.encounter,
--   insurance_data.id y users.id en OpenEMR nativo.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `covl_authorizations` (
  `id`                   int(11)      NOT NULL AUTO_INCREMENT                COMMENT 'Clave primaria autoincremental',
  `pid`                  int(11)      NOT NULL                               COMMENT 'FK → patient_data.pid — paciente al que corresponde la autorización',
  `encounter_id`         int(11)      DEFAULT NULL                           COMMENT 'FK → form_encounter.encounter — encuentro clínico asociado (puede ser NULL al solicitar antes del encuentro)',
  `insurance_data_id`    int(11)      NOT NULL                               COMMENT 'FK → insurance_data.id — registro de cobertura vigente del paciente',
  `insurance_company_id` int(11)      NOT NULL                               COMMENT 'FK → insurance_companies.id — financiador (denormalizado para consultas rápidas)',
  `code_type`            varchar(15)  NOT NULL                               COMMENT 'Tipo de código de la práctica (ct_key de code_types, ej: CPT4, NNAR)',
  `code`                 varchar(25)  NOT NULL                               COMMENT 'Código de la práctica a autorizar',
  `code_text`            text         DEFAULT NULL                           COMMENT 'Descripción de la práctica al momento de la solicitud (snapshot para trazabilidad)',
  `quantity`             int(11)      NOT NULL DEFAULT 1                     COMMENT 'Cantidad de sesiones o unidades autorizadas',
  `quantity_used`        int(11)      NOT NULL DEFAULT 0                     COMMENT 'Cantidad de unidades ya consumidas de las autorizadas',
  `status`               enum('pendiente','en_auditoria','aprobada','rechazada','vencida','cancelada') NOT NULL DEFAULT 'pendiente' COMMENT 'Estado actual de la autorización',
  `auth_number`          varchar(50)  DEFAULT NULL                           COMMENT 'Número de autorización devuelto por el financiador',
  `request_date`         datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP     COMMENT 'Fecha y hora de la solicitud de autorización',
  `response_date`        datetime     DEFAULT NULL                           COMMENT 'Fecha y hora de la respuesta del financiador',
  `valid_from`           date         DEFAULT NULL                           COMMENT 'Fecha de inicio de validez de la autorización otorgada',
  `valid_until`          date         DEFAULT NULL                           COMMENT 'Fecha de vencimiento de la autorización otorgada',
  `reject_reason`        text         DEFAULT NULL                           COMMENT 'Motivo de rechazo o auditoría (si el estado es rechazada o en_auditoria)',
  `notes`                text         DEFAULT NULL                           COMMENT 'Notas internas del operador sobre esta autorización',
  `requested_by`         int(11)      DEFAULT NULL                           COMMENT 'FK → users.id — usuario del sistema que generó la solicitud',
  `adapter_id`           int(11)      DEFAULT NULL                           COMMENT 'FK → covl_adapters.id — adaptador de integración utilizado (NULL si fue carga manual)',
  `external_ref`         varchar(100) DEFAULT NULL                           COMMENT 'Referencia externa del sistema del financiador para trazabilidad cruzada',
  `created_at`           datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP     COMMENT 'Fecha y hora de creación del registro',
  `updated_at`           datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha y hora de última modificación',
  PRIMARY KEY (`id`),
  KEY `idx_covl_auth_pid`            (`pid`),
  KEY `idx_covl_auth_insco_status`   (`insurance_company_id`, `status`),
  KEY `idx_covl_auth_encounter`      (`encounter_id`),
  KEY `idx_covl_auth_number`         (`auth_number`),
  KEY `idx_covl_auth_valid_until`    (`status`, `valid_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Autorizaciones previas de prácticas médicas ante financiadores — entidad central del módulo LATAM';

-- ---------------------------------------------------------------------------
-- 3. covl_authorization_history — Historial de cambios de estado
-- Corrección FK types: id, authorization_id y changed_by usan int(11)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `covl_authorization_history` (
  `id`               int(11)      NOT NULL AUTO_INCREMENT                COMMENT 'Clave primaria autoincremental',
  `authorization_id` int(11)      NOT NULL                               COMMENT 'FK → covl_authorizations.id — autorización cuyo estado cambió',
  `status_from`      varchar(30)  DEFAULT NULL                           COMMENT 'Estado anterior al cambio',
  `status_to`        varchar(30)  NOT NULL                               COMMENT 'Nuevo estado registrado',
  `changed_by`       int(11)      DEFAULT NULL                           COMMENT 'FK → users.id — usuario que realizó el cambio (NULL si fue automático)',
  `change_reason`    text         DEFAULT NULL                           COMMENT 'Motivo o detalle del cambio de estado',
  `created_at`       datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP     COMMENT 'Fecha y hora en que se registró el cambio de estado',
  PRIMARY KEY (`id`),
  KEY `idx_covl_auth_hist_auth` (`authorization_id`),
  KEY `idx_covl_auth_hist_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Historial de auditoría de cambios de estado de las autorizaciones previas';

-- ---------------------------------------------------------------------------
-- 4. covl_auth_rules — Reglas de autorización por práctica × financiador × plan
-- Corrección UNIQUE KEY con NULL: plan_pattern y code usan NOT NULL DEFAULT '0'
--   (valor sentinel) en lugar de DEFAULT NULL. MySQL no detecta duplicados cuando
--   algún campo del índice único es NULL. En PHP: tratar '0' como wildcard
--   (todos los planes / todos los códigos del tipo).
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `covl_auth_rules` (
  `id`                   int(11)      NOT NULL AUTO_INCREMENT              COMMENT 'Clave primaria autoincremental',
  `insurance_company_id` int(11)      NOT NULL                             COMMENT 'FK → insurance_companies.id — financiador al que aplica la regla',
  `plan_pattern`         varchar(255) NOT NULL DEFAULT '0'                 COMMENT 'Patrón de nombre de plan al que aplica (0 = todos los planes del financiador; cualquier otro valor soporta % como comodín SQL LIKE)',
  `code_type`            varchar(15)  NOT NULL                             COMMENT 'Tipo de código al que aplica la regla (ct_key de code_types)',
  `code`                 varchar(25)  NOT NULL DEFAULT '0'                 COMMENT 'Código específico de la práctica (0 = todos los códigos del tipo indicado — wildcard)',
  `auth_mode`            enum('automatica','requerida','no_requerida') NOT NULL DEFAULT 'requerida' COMMENT 'Modo de autorización: automatica = se aprueba sin gestión, requerida = requiere auditoría del financiador, no_requerida = exenta',
  `max_quantity`         int(11)      DEFAULT NULL                         COMMENT 'Cantidad máxima aprobable automáticamente (aplica si auth_mode = automatica; si se supera, se escala a requerida)',
  `priority`             int(11)      NOT NULL DEFAULT 100                 COMMENT 'Orden de evaluación cuando múltiples reglas coinciden (menor valor = mayor prioridad)',
  `active`               tinyint(1)   NOT NULL DEFAULT 1                   COMMENT 'Estado de la regla (1=activa, 0=inactiva)',
  `country_code`         char(2)      DEFAULT NULL                         COMMENT 'País de aplicación ISO 3166-1 alpha-2 (NULL = genérico para cualquier país)',
  `notes`                varchar(500) DEFAULT NULL                         COMMENT 'Descripción o justificación de la regla',
  `created_at`           datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP   COMMENT 'Fecha y hora de creación del registro',
  `updated_at`           datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha y hora de última modificación',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_covl_auth_rules` (`insurance_company_id`, `plan_pattern`, `code_type`, `code`),
  KEY `idx_covl_auth_rules_code`     (`code_type`, `code`, `insurance_company_id`),
  KEY `idx_covl_auth_rules_priority` (`priority`, `active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Reglas que determinan si una práctica requiere autorización previa según financiador y plan del paciente';

-- ---------------------------------------------------------------------------
-- 5. covl_frequency_rules — Reglas de frecuencia/periodicidad de prácticas
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `covl_frequency_rules` (
  `id`                   int(11)      NOT NULL AUTO_INCREMENT              COMMENT 'Clave primaria autoincremental',
  `insurance_company_id` int(11)      NOT NULL                             COMMENT 'FK → insurance_companies.id — financiador al que aplica la restricción de frecuencia',
  `code_type`            varchar(15)  NOT NULL                             COMMENT 'Tipo de código de la práctica (ct_key de code_types)',
  `code`                 varchar(25)  NOT NULL                             COMMENT 'Código de la práctica con restricción de frecuencia',
  `min_interval_days`    int(11)      NOT NULL                             COMMENT 'Intervalo mínimo en días entre repeticiones de la misma práctica (ej: 180 = no antes de 6 meses)',
  `max_per_year`         int(11)      DEFAULT NULL                         COMMENT 'Cantidad máxima de veces que se puede realizar la práctica en un año calendario (NULL = sin límite anual)',
  `severity`             enum('alerta','bloqueo') NOT NULL DEFAULT 'alerta' COMMENT 'Comportamiento al detectar violación: alerta = avisa pero permite continuar, bloqueo = impide generar la orden',
  `active`               tinyint(1)   NOT NULL DEFAULT 1                   COMMENT 'Estado de la regla (1=activa, 0=inactiva)',
  `country_code`         char(2)      DEFAULT NULL                         COMMENT 'País de aplicación ISO 3166-1 alpha-2 (NULL = genérico para cualquier país)',
  `notes`                varchar(255) DEFAULT NULL                         COMMENT 'Justificación o referencia normativa de la restricción',
  `created_at`           datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP   COMMENT 'Fecha y hora de creación del registro',
  `updated_at`           datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha y hora de última modificación',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_covl_freq_rules` (`insurance_company_id`, `code_type`, `code`),
  KEY `idx_covl_freq_rules_code` (`code_type`, `code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Reglas de intervalo mínimo y frecuencia máxima entre repeticiones de una práctica por financiador';

-- ---------------------------------------------------------------------------
-- 6. covl_provider_coverage — Vigencia de convenio prestador × financiador
-- Corrección FK types: user_id usa int(11) (igual que users.id en OpenEMR nativo).
-- Corrección UNIQUE KEY con NULL: facility_id usa NOT NULL DEFAULT 0 (sentinel)
--   en lugar de DEFAULT NULL. En PHP: tratar facility_id = 0 como
--   "aplica a todas las sedes" en vez de consultar con IS NULL.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `covl_provider_coverage` (
  `id`                   int(11)      NOT NULL AUTO_INCREMENT              COMMENT 'Clave primaria autoincremental',
  `user_id`              int(11)      NOT NULL                             COMMENT 'FK → users.id — profesional o prestador con el convenio',
  `insurance_company_id` int(11)      NOT NULL                             COMMENT 'FK → insurance_companies.id — financiador con el que tiene convenio',
  `facility_id`          int(11)      NOT NULL DEFAULT 0                   COMMENT 'FK → facility.id — sede donde aplica el convenio (0 = aplica en todas las sedes — sentinel para UNIQUE KEY)',
  `provider_number`      varchar(50)  DEFAULT NULL                         COMMENT 'Número de prestador ante el financiador (matrícula de convenio o código de efector)',
  `date_from`            date         NOT NULL                             COMMENT 'Fecha de inicio de vigencia del convenio',
  `date_to`              date         DEFAULT NULL                         COMMENT 'Fecha de fin de vigencia del convenio (NULL = vigente sin fecha de vencimiento definida)',
  `specialties`          varchar(500) DEFAULT NULL                         COMMENT 'Especialidades cubiertas por el convenio (lista separada por comas; NULL = todas las especialidades del profesional)',
  `active`               tinyint(1)   NOT NULL DEFAULT 1                   COMMENT 'Activación manual del registro (1=activo, 0=suspendido manualmente)',
  `notes`                text         DEFAULT NULL                         COMMENT 'Observaciones sobre el convenio del prestador',
  `created_at`           datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP   COMMENT 'Fecha y hora de creación del registro',
  `updated_at`           datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha y hora de última modificación',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_covl_prov_cov` (`user_id`, `insurance_company_id`, `facility_id`, `date_from`),
  KEY `idx_covl_prov_cov_insco`   (`insurance_company_id`, `date_from`, `date_to`),
  KEY `idx_covl_prov_cov_user`    (`user_id`, `active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Vigencia temporal del convenio entre cada prestador y cada financiador; se valida al asignar turno y en el check-in';

-- ---------------------------------------------------------------------------
-- 7. covl_settlement_batches — Lotes de liquidación periódica
-- Corrección FK types: id y created_by usan int(11).
-- Nuevo campo: currency char(3) ISO 4217 para soporte multi-país.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `covl_settlement_batches` (
  `id`                   int(11)       NOT NULL AUTO_INCREMENT             COMMENT 'Clave primaria autoincremental',
  `batch_number`         varchar(30)   NOT NULL                            COMMENT 'Número de lote único (ej: AR-OSDE-2026-08-001)',
  `insurance_company_id` int(11)       NOT NULL                            COMMENT 'FK → insurance_companies.id — financiador al que se presenta el lote',
  `facility_id`          int(11)       NOT NULL                            COMMENT 'FK → facility.id — sede que presenta el lote',
  `period_from`          date          NOT NULL                            COMMENT 'Fecha de inicio del período que cubre el lote de liquidación',
  `period_to`            date          NOT NULL                            COMMENT 'Fecha de fin del período que cubre el lote de liquidación',
  `currency`             char(3)       NOT NULL DEFAULT 'ARS'              COMMENT 'Moneda de la liquidación en formato ISO 4217 (ARS=Peso Argentino, CLP=Peso Chileno, COP=Peso Colombiano, etc.)',
  `status`               enum('borrador','armado','presentado','pagado_parcial','pagado_total','en_disputa','anulado') NOT NULL DEFAULT 'borrador' COMMENT 'Estado del lote: borrador=en preparación, armado=cerrado listo para presentar, presentado=enviado al financiador, pagado_parcial=pago incompleto, pagado_total=liquidado completo, en_disputa=con débitos o rechazos, anulado=cancelado',
  `total_items`          int(11)       NOT NULL DEFAULT 0                  COMMENT 'Cantidad total de ítems incluidos en el lote (denormalizado para consultas rápidas)',
  `total_amount`         decimal(14,2) NOT NULL DEFAULT 0.00               COMMENT 'Monto total facturado presentado en el lote',
  `paid_amount`          decimal(14,2) DEFAULT NULL                        COMMENT 'Monto efectivamente pagado por el financiador (NULL hasta recibir el pago)',
  `presentation_date`    date          DEFAULT NULL                        COMMENT 'Fecha en que el lote fue presentado al financiador',
  `payment_date`         date          DEFAULT NULL                        COMMENT 'Fecha en que el financiador realizó el pago',
  `payment_reference`    varchar(100)  DEFAULT NULL                        COMMENT 'Número de transferencia, cheque o referencia del pago del financiador',
  `dispute_notes`        text          DEFAULT NULL                        COMMENT 'Detalle de débitos, rechazos parciales o notas de la disputa',
  `created_by`           int(11)       DEFAULT NULL                        COMMENT 'FK → users.id — usuario que creó el lote',
  `created_at`           datetime      NOT NULL DEFAULT CURRENT_TIMESTAMP  COMMENT 'Fecha y hora de creación del lote',
  `updated_at`           datetime      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha y hora de última modificación',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_covl_batch_number` (`batch_number`),
  KEY `idx_covl_batch_insco_status` (`insurance_company_id`, `status`),
  KEY `idx_covl_batch_period`       (`period_from`, `period_to`),
  KEY `idx_covl_batch_facility`     (`facility_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Lotes de liquidación periódica que agrupan prestaciones para presentar en conjunto a un financiador';

-- ---------------------------------------------------------------------------
-- 8. covl_settlement_items — Ítems individuales de un lote de liquidación
-- Correcciones:
--   - id, batch_id, pid, encounter_id, authorization_id usan int(11)
--   - Nuevo campo currency char(3) ISO 4217
--   - Nuevo campo attempt_number tinyint(3): permite re-presentar ítems rechazados
--     en un lote posterior sin romper la UNIQUE KEY
--   - UNIQUE KEY incluye attempt_number: (batch_id, billing_id, attempt_number)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `covl_settlement_items` (
  `id`               int(11)       NOT NULL AUTO_INCREMENT                  COMMENT 'Clave primaria autoincremental',
  `batch_id`         int(11)       NOT NULL                                 COMMENT 'FK → covl_settlement_batches.id — lote al que pertenece el ítem',
  `billing_id`       int(11)       NOT NULL                                 COMMENT 'FK → billing.id — prestación facturada del encuentro',
  `pid`              int(11)       NOT NULL                                 COMMENT 'FK → patient_data.pid — paciente de la prestación (denormalizado)',
  `encounter_id`     int(11)       NOT NULL                                 COMMENT 'FK → form_encounter.encounter — encuentro clínico al que pertenece la prestación',
  `authorization_id` int(11)       DEFAULT NULL                             COMMENT 'FK → covl_authorizations.id — autorización vinculada a la prestación (NULL si no requería autorización)',
  `code_type`        varchar(15)   NOT NULL                                 COMMENT 'Tipo de código presentado en el lote (ct_key)',
  `code`             varchar(25)   NOT NULL                                 COMMENT 'Código de la práctica presentada',
  `fee`              decimal(12,2) NOT NULL                                 COMMENT 'Monto facturado de esta línea presentada al financiador',
  `currency`         char(3)       NOT NULL DEFAULT 'ARS'                   COMMENT 'Moneda del ítem en formato ISO 4217 — debe coincidir con la moneda del lote',
  `attempt_number`   tinyint(3)    NOT NULL DEFAULT 1                       COMMENT 'Número de intento de presentación del ítem (1=primera presentación, 2=re-presentación tras rechazo, etc.)',
  `item_status`      enum('incluido','aprobado','rechazado','debitado') NOT NULL DEFAULT 'incluido' COMMENT 'Estado del ítem dentro del lote: incluido=en proceso, aprobado=aceptado por financiador, rechazado=no reconocido, debitado=aceptado con descuento',
  `debit_reason`     varchar(255)  DEFAULT NULL                             COMMENT 'Motivo del débito o rechazo informado por el financiador',
  `debit_amount`     decimal(12,2) DEFAULT NULL                             COMMENT 'Monto debitado por el financiador sobre este ítem (NULL si no hay débito)',
  `created_at`       datetime      NOT NULL DEFAULT CURRENT_TIMESTAMP       COMMENT 'Fecha y hora de inclusión del ítem en el lote',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_covl_items_batch_billing` (`batch_id`, `billing_id`, `attempt_number`),
  KEY `idx_covl_items_pid`           (`pid`),
  KEY `idx_covl_items_encounter`     (`encounter_id`),
  KEY `idx_covl_items_authorization` (`authorization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Prestaciones individuales incluidas en un lote de liquidación; un ítem rechazado puede re-presentarse en un lote posterior incrementando attempt_number';

-- ---------------------------------------------------------------------------
-- 9. covl_adapters — Catálogo de adaptadores de integración plugables
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `covl_adapters` (
  `id`                   int(11)      NOT NULL AUTO_INCREMENT              COMMENT 'Clave primaria autoincremental',
  `insurance_company_id` int(11)      DEFAULT NULL                         COMMENT 'FK → insurance_companies.id — financiador al que corresponde este adaptador (NULL = adaptador genérico o de uso múltiple)',
  `adapter_key`          varchar(50)  NOT NULL                             COMMENT 'Identificador único del adaptador (ej: manual_fallback, osde_api_v2, swiss_medical_ws)',
  `adapter_type`         enum('elegibilidad','autorizacion','ambos') NOT NULL DEFAULT 'ambos' COMMENT 'Capacidades del adaptador: elegibilidad=verifica vigencia del afiliado, autorizacion=envía y consulta autorizaciones, ambos=ambas funciones',
  `php_class`            varchar(255) NOT NULL                             COMMENT 'Nombre de clase PHP completamente calificado (FQCN) que implementa CoverageAdapterInterface',
  `config_json`          text         DEFAULT NULL                         COMMENT 'Configuración específica del adaptador en formato JSON (credenciales, endpoints, parámetros)',
  `active`               tinyint(1)   NOT NULL DEFAULT 1                   COMMENT 'Estado del adaptador (1=activo, 0=deshabilitado)',
  `created_at`           datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP   COMMENT 'Fecha y hora de registro del adaptador',
  `updated_at`           datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha y hora de última modificación',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_covl_adapter_key`   (`adapter_key`),
  KEY `idx_covl_adapter_insco`       (`insurance_company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Catálogo de adaptadores de integración plugables para verificación de elegibilidad y gestión de autorizaciones por financiador';

-- ---------------------------------------------------------------------------
-- 10. covl_integration_log — Registro de interacciones con sistemas externos
-- Corrección FK types: id, pid y authorization_id usan int(11)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `covl_integration_log` (
  `id`               int(11)      NOT NULL AUTO_INCREMENT                  COMMENT 'Clave primaria autoincremental',
  `adapter_id`       int(11)      NOT NULL                                 COMMENT 'FK → covl_adapters.id — adaptador que realizó la operación',
  `operation`        varchar(50)  NOT NULL                                 COMMENT 'Tipo de operación ejecutada (ej: verificar_elegibilidad, solicitar_autorizacion, consultar_estado)',
  `pid`              int(11)      DEFAULT NULL                             COMMENT 'FK → patient_data.pid — paciente involucrado en la operación (NULL si no aplica)',
  `authorization_id` int(11)      DEFAULT NULL                             COMMENT 'FK → covl_authorizations.id — autorización involucrada en la operación (NULL si no aplica)',
  `request_payload`  text         DEFAULT NULL                             COMMENT 'Datos enviados al sistema externo en formato JSON',
  `response_payload` text         DEFAULT NULL                             COMMENT 'Datos recibidos del sistema externo en formato JSON',
  `http_status`      int(3)       DEFAULT NULL                             COMMENT 'Código de estado HTTP de la respuesta (NULL si no fue una llamada HTTP)',
  `success`          tinyint(1)   NOT NULL DEFAULT 0                       COMMENT 'Resultado de la operación (1=exitosa, 0=con error)',
  `error_message`    text         DEFAULT NULL                             COMMENT 'Detalle del error producido si la operación falló',
  `duration_ms`      int(11)      DEFAULT NULL                             COMMENT 'Duración de la llamada al sistema externo en milisegundos',
  `created_at`       datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP       COMMENT 'Fecha y hora en que se ejecutó la operación',
  PRIMARY KEY (`id`),
  KEY `idx_covl_intlog_adapter_date` (`adapter_id`, `created_at`),
  KEY `idx_covl_intlog_pid`          (`pid`),
  KEY `idx_covl_intlog_auth`         (`authorization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Registro de auditoría de todas las interacciones con sistemas externos de financiadores vía adaptadores de integración';

-- ---------------------------------------------------------------------------
-- 11. covl_country_packs — Paquetes de configuración por país instalados
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `covl_country_packs` (
  `id`                    int(11)      NOT NULL AUTO_INCREMENT             COMMENT 'Clave primaria autoincremental',
  `country_code`          char(2)      NOT NULL                            COMMENT 'Código de país ISO 3166-1 alpha-2 (ej: AR, CL, CO, MX, PE)',
  `name`                  varchar(100) NOT NULL                            COMMENT 'Nombre descriptivo del paquete de país (ej: Argentina — Obras Sociales y Prepagas)',
  `version`               varchar(20)  NOT NULL DEFAULT '1.0.0'            COMMENT 'Versión del paquete de configuración de país instalado',
  `code_type_key`         varchar(15)  DEFAULT NULL                        COMMENT 'ct_key del nomenclador nacional que registra este paquete (ej: NNAR para Argentina)',
  `default_rules_loaded`  tinyint(1)   NOT NULL DEFAULT 0                  COMMENT 'Indica si ya se cargaron las reglas de autorización y frecuencia por defecto del país (1=sí, 0=no)',
  `installed_at`          datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP  COMMENT 'Fecha y hora de instalación del paquete de país',
  `updated_at`            datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha y hora de última actualización del paquete',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_covl_country_packs` (`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Paquetes de configuración por país instalados en el módulo: terminología, nomencladores y reglas por defecto';

-- ---------------------------------------------------------------------------
-- 12. covl_country_code_maps — Mapeo entre códigos locales y estándar
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `covl_country_code_maps` (
  `id`                 int(11)     NOT NULL AUTO_INCREMENT                  COMMENT 'Clave primaria autoincremental',
  `country_code`       char(2)     NOT NULL                                 COMMENT 'País del mapeo ISO 3166-1 alpha-2',
  `local_code_type`    varchar(15) NOT NULL                                 COMMENT 'ct_key del nomenclador local de origen (ej: NNAR)',
  `local_code`         varchar(25) NOT NULL                                 COMMENT 'Código en el nomenclador local',
  `standard_code_type` varchar(15) NOT NULL                                 COMMENT 'ct_key del código estándar de destino (ej: CPT4)',
  `standard_code`      varchar(25) NOT NULL                                 COMMENT 'Código estándar equivalente',
  `equivalence`        enum('exacta','aproximada','parcial') DEFAULT 'aproximada' COMMENT 'Grado de equivalencia: exacta=idéntica, aproximada=similar clínicamente, parcial=solo parte del procedimiento',
  `active`             tinyint(1)  NOT NULL DEFAULT 1                       COMMENT 'Estado del mapeo (1=activo, 0=obsoleto)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_covl_code_map` (`local_code_type`, `local_code`, `standard_code_type`, `standard_code`),
  KEY `idx_covl_code_map_standard` (`standard_code_type`, `standard_code`),
  KEY `idx_covl_code_map_country`  (`country_code`, `local_code_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Tabla de equivalencias entre códigos del nomenclador local de cada país y los códigos estándar internacionales (CPT4, HCPCS, etc.)';

-- ===========================================================================
-- DATOS INICIALES
-- ===========================================================================

-- ---------------------------------------------------------------------------
-- Registro del adaptador de carga manual (fallback por defecto)
-- Se instala siempre como respaldo cuando no hay integración real disponible
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO `covl_adapters`
  (`adapter_key`, `adapter_type`, `php_class`, `config_json`, `active`)
VALUES
  (
    'manual_fallback',
    'ambos',
    'OpenEMR\\Modules\\CoverageLatam\\Adapter\\ManualFallbackAdapter',
    '{"descripcion": "Carga manual por operador, sin integración en línea con el financiador"}',
    1
  );

-- ---------------------------------------------------------------------------
-- Paquete Argentina
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO `covl_country_packs`
  (`country_code`, `name`, `version`, `code_type_key`, `default_rules_loaded`)
VALUES
  ('AR', 'Argentina — Obras Sociales, Prepagas y ART', '1.0.0', 'NNAR', 0);

-- ---------------------------------------------------------------------------
-- NOTA: El tipo de código NNAR (Nomenclador Nacional Argentino) NO se registra
-- aquí para evitar colisión con ct_id de nomencladores custom preexistentes.
-- El registro se realiza dinámicamente desde el installer PHP del módulo,
-- calculando el próximo ct_id libre:
--   SELECT COALESCE(MAX(ct_id), 100) + 1 FROM code_types
-- Ver: src/Installer/CodeTypeInstaller.php
-- ---------------------------------------------------------------------------
