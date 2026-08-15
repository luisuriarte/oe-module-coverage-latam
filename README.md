# 🏥 oe-module-coverage-latam

> **Gestión de Coberturas LATAM para OpenEMR 8+**  
> Autorizaciones previas · Liquidaciones por lotes · Convenios de prestadores · Reglas de frecuencia

[![OpenEMR](https://img.shields.io/badge/OpenEMR-8.0%2B-0066CC?style=flat-square&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0id2hpdGUiIGQ9Ik0xMiAyQzYuNDggMiAyIDYuNDggMiAxMnM0LjQ4IDEwIDEwIDEwIDEwLTQuNDggMTAtMTBTMTcuNTIgMiAxMiAyem0tMiAxNWwtNS01IDEuNDEtMS40MUwxMCAxNC4xN2w3LjU5LTcuNTlMMTkgOGwtOSA5eiIvPjwvc3ZnPg==)](https://www.open-emr.org)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Licencia](https://img.shields.io/badge/Licencia-GPL--3.0-green?style=flat-square)](LICENSE)
[![Versión](https://img.shields.io/badge/Versión-1.0.0-blue?style=flat-square)](version.php)
[![País inicial](https://img.shields.io/badge/País_inicial-🇦🇷_Argentina-74ACDF?style=flat-square)](#-paquete-argentina)

---

## 📋 Tabla de contenidos

- [¿Qué es?](#-qué-es)
- [¿Para qué sirve?](#-para-qué-sirve)
- [Arquitectura](#-arquitectura)
- [Tablas de la base de datos](#-tablas-de-la-base-de-datos)
- [Instalación](#-instalación)
- [Navegación y Estructura del Menú](#-navegación-y-estructura-del-menú)
- [Paquete Argentina](#-paquete-argentina)
- [Adaptadores de integración](#-adaptadores-de-integración)
- [Servicios incluidos](#-servicios-incluidos)
- [Estructura de archivos](#-estructura-de-archivos)
- [Relación con OpenEMR nativo](#-relación-con-openemr-nativo)
- [Preguntas frecuentes](#-preguntas-frecuentes)
- [Hoja de ruta](#-hoja-de-ruta)
- [Autor y licencia](#-autor-y-licencia)

---

## 🤔 ¿Qué es?

**oe-module-coverage-latam** es un módulo para OpenEMR 8+ diseñado para cubrir las necesidades específicas de gestión de coberturas médicas en América Latina.

El sistema de seguros nativo de OpenEMR está pensado para el modelo norteamericano (EDI 837/835, claims individuales 1-a-1 vía clearinghouse). En LATAM, el modelo es radicalmente diferente:

| Característica | OpenEMR nativo | LATAM real |
|---|---|---|
| Terminología | Insurance / Payer | Obra social / Prepaga / EPS |
| Flujo de cobro | Claim individual | **Liquidación por lotes** (mensual) |
| Autorizaciones | Campo libre `prior_auth_number` | Proceso formal con estados y número devuelto |
| Convenios | Booleano simple | **Vigencia temporal** por prestador × financiador |
| Códigos | CPT4 / HCPCS | **Nomencladores nacionales** (NNAR, Arancel FONASA, etc.) |

Este módulo **no reemplaza** el sistema nativo: lo **extiende** agregando la capa LATAM encima, reutilizando `insurance_data`, `insurance_companies`, `billing` y `form_encounter` mediante claves foráneas.

---

## 🎯 ¿Para qué sirve?

### ✅ Autorizaciones previas
Gestión completa del ciclo de vida de una autorización:

```
Pendiente → En auditoría → Aprobada ──→ Vencida
                       ↘ Rechazada
```

- Estado por práctica: `pendiente`, `en_auditoria`, `aprobada`, `rechazada`, `vencida`, `cancelada`
- Número de autorización devuelto por el financiador
- Cantidad de sesiones autorizadas y consumidas
- Historial de auditoría de cada cambio de estado
- Reglas configurables por combinación **práctica × financiador × plan**

### ✅ Reglas de frecuencia y periodicidad
Valida al generar una orden si ya existe la misma práctica dentro de un intervalo mínimo:

```
TAC de cráneo (NNAR 380601) → Intervalo mínimo: 180 días
├── Último realizado: 2026-03-15
├── Próximo habilitado: 2026-09-11
└── ⚠️  Alerta (o 🚫 Bloqueo, configurable)
```

### ✅ Vigencia de convenio por prestador
Relación **prestador × financiador** con fecha desde/hasta:

- No es un booleano fijo: un profesional puede dejar de atender una cobertura temporalmente
- Se valida al asignar un turno (filtro de disponibilidad) y en el check-in
- Soporta convenios por sede o globales

### ✅ Lotes de liquidación periódica
Alternativa al flujo de claims nativo para el modelo LATAM:

```
Borrador → Armado → Presentado → Pagado parcial → Pagado total
                              ↘ En disputa → (resolución manual)
```

- Agrupa N prestaciones del período para un financiador
- Registra monto presentado, pagado, débitos individuales
- Compatible con el flujo de `billing` nativo

### ✅ Adaptadores de integración plugables
Interfaz común para conectar sistemas externos de financiadores:

- Verificación de vigencia de afiliación en línea
- Envío y consulta de autorizaciones
- **Adaptador manual** incluido como fallback (sin integración real, carga manual)
- Registro de auditoría de cada llamada

---

## 🏗️ Arquitectura

El módulo está organizado en **3 capas** que se apoyan sobre OpenEMR sin modificarlo:

```
┌─────────────────────────────────────────────────────────┐
│              Capa País / Financiador                    │
│  Paquete Argentina (NNAR, reglas por defecto, semillas) │
│  Futuros: Chile (FONASA), Colombia (EPS), México, etc.  │
├─────────────────────────────────────────────────────────┤
│              Capa Núcleo LATAM (covl_*)                 │
│  Autorizaciones · Frecuencia · Convenios · Lotes        │
│  Adaptadores · Log de integración · Config              │
├─────────────────────────────────────────────────────────┤
│              Capa OpenEMR Nativa (reutilizada)          │
│  insurance_companies · insurance_data · billing         │
│  form_encounter · codes · users · facility              │
└─────────────────────────────────────────────────────────┘
```

### Capas PHP

```
src/
├── Contracts/          ← Interfaces del contrato (nunca cambian)
│   ├── CoverageAdapterInterface.php
│   ├── EligibilityResultInterface.php
│   └── AuthorizationResultInterface.php
├── Dto/                ← Value objects inmutables
│   ├── EligibilityResult.php
│   └── AuthorizationResult.php
├── Adapter/            ← Implementaciones de integraciones
│   └── ManualFallbackAdapter.php
├── Service/            ← Lógica de negocio
│   ├── AuthorizationService.php
│   ├── FrequencyCheckService.php
│   ├── ProviderCoverageService.php
│   └── AdapterRegistry.php
└── Repository/         ← Acceso a datos con sqlStatement nativo
    └── AuthorizationRepository.php
```

---

## 🗄️ Tablas de la base de datos

El módulo crea **12 tablas** con prefijo `covl_`. Ninguna tabla nativa de OpenEMR se modifica.

| # | Tabla | Descripción |
|---|---|---|
| 1 | `covl_config` | Configuración por sede (país, modo de liquidación, flags) |
| 2 | `covl_authorizations` | **Entidad central** — autorizaciones previas de prácticas |
| 3 | `covl_authorization_history` | Historial de cambios de estado (auditoría completa) |
| 4 | `covl_auth_rules` | Reglas de autorización por práctica × financiador × plan |
| 5 | `covl_frequency_rules` | Intervalo mínimo entre repeticiones de una práctica |
| 6 | `covl_provider_coverage` | Vigencia de convenio prestador × financiador (con fechas) |
| 7 | `covl_settlement_batches` | Lotes de liquidación periódica |
| 8 | `covl_settlement_items` | Prestaciones individuales dentro de un lote |
| 9 | `covl_adapters` | Catálogo de adaptadores de integración registrados |
| 10 | `covl_integration_log` | Log de auditoría de llamadas a sistemas externos |
| 11 | `covl_country_packs` | Paquetes de configuración por país instalados |
| 12 | `covl_country_code_maps` | Equivalencias entre códigos locales y estándar (CPT4, etc.) |

> 💡 Todas las tablas y columnas tienen `COMMENT` en español latinoamericano para facilitar la comprensión directa desde cualquier cliente de base de datos.

### Claves foráneas a OpenEMR nativo

```sql
covl_authorizations.pid                → patient_data.pid          (bigint 20)
covl_authorizations.encounter_id       → form_encounter.encounter   (bigint 20)
covl_authorizations.insurance_data_id  → insurance_data.id          (bigint 20)
covl_authorizations.insurance_company_id → insurance_companies.id   (int 11)
covl_settlement_items.billing_id       → billing.id                 (int 11)
covl_provider_coverage.user_id         → users.id                   (bigint 20)
covl_config.facility_id                → facility.id                (int 11)
```

---

## 🚀 Instalación

### Requisitos

| Requisito | Versión mínima |
|---|---|
| OpenEMR | 8.0.0 |
| PHP | 8.1 |
| MySQL / MariaDB | 5.7 / 10.4 |

### Paso 1 — Copiar el módulo

```bash
# En la raíz de OpenEMR
cp -r oe-module-coverage-latam interface/modules/custom_modules/
```

### Paso 2 — Instalar desde el Module Manager

1. Ir a **Administración → Módulos → Módulos instalados**
2. Buscar **"Coverage LATAM"**
3. Hacer clic en **Instalar**

OpenEMR ejecutará automáticamente `sql/install.sql`.

### Paso 3 — Cargar datos del paquete Argentina (opcional)

Si vas a usar el módulo con Argentina como país base:

```sql
-- Desde la consola de MySQL o phpMyAdmin
SOURCE /path/to/oe-module-coverage-latam/sql/argentina_seed.sql;
```

Esto carga:
- ✅ Tipo de código **NNAR** (Nomenclador Nacional Argentino) en `code_types`
- ✅ Reglas de autorización base (TAC, RMN, consultas ambulatorias)
- ✅ Reglas de frecuencia base (intervalos mínimos comunes)

### Desinstalación

Desde **Administración → Módulos**, hacer clic en **Desinstalar**.  
Se ejecuta `sql/uninstall.sql` que elimina todas las tablas `covl_*` en orden FK-seguro.

> ⚠️ **Advertencia:** La desinstalación elimina permanentemente todos los datos de autorizaciones, lotes y convenios almacenados en el módulo. Hacé un respaldo antes.

---

## 📌 Navegación y Estructura del Menú

Al activar el módulo desde el **Module Manager**, se registra la entrada en el menú **Servicios / Honorarios** (o en la barra de menú principal en instalaciones personalizadas):

```
📂 Servicios / Honorarios (o Menú Principal)
└── 🏥 Coberturas LATAM
    ├── 📊 Dashboard                  (Métricas globales y resumen operativo)
    ├── 📋 Autorizaciones             (Gestión de solicitudes y estado de trámites)
    ├── 📦 Lotes de Liquidación       (Presentaciones masivas y cobro a obras sociales)
    ├── 👨‍⚕️ Convenios Prestadores     (Vigencia por profesional y financiador)
    ├── 🌎 Países                     (Catálogo e instalación de paquetes por país)
    └── ⚙️ Configuración              (Reglas de autorización previa y frecuencia)
```

### 🔒 Permisos y control de acceso (ACL):
- 📊 **Dashboard, Autorizaciones y Lotes:** Accesibles para personal administrativo y recepción (`patients`, `demo`).
- 👨‍⚕️ **Convenios Prestadores y Configuración:** Accesibles exclusivamente para administradores y auditores médicos (`admin`, `docs`).

---

## 🇦🇷 Paquete Argentina

El paquete Argentina es el primer paquete de país incluido en el módulo.

### Qué incluye

#### 📋 Tipo de código: NNAR
Se registra el **Nomenclador Nacional Argentino** como un tipo de código adicional en `code_types`:

```sql
ct_key  = 'NNAR'
ct_id   = 200      -- Rango alto para no colisionar con OpenEMR (hasta ~114)
ct_label = 'Nomenclador Nacional Argentino'
ct_proc = 1        -- Es un tipo de procedimiento
ct_fee  = 1        -- Soporta aranceles
```

Los códigos del NNAR se cargan en la tabla nativa `codes` con `code_type = 200`.

#### 📋 Reglas de autorización base

| Código NNAR | Práctica | Modo |
|---|---|---|
| 010101 | Consulta médica ambulatoria | 🟢 Automática |
| 010201 | Control de salud anual | 🟢 Automática |
| 380601–380801 | Tomografías computadas (TAC) | 🟡 Requerida |
| 390101–390301 | Resonancias magnéticas (RMN) | 🟡 Requerida |
| 420101–420201 | Cirugías programadas | 🟡 Requerida |

> 📌 Los códigos son ilustrativos. Deben reemplazarse por los códigos reales del nomenclador según la versión vigente del RES. 925/2000 y actualizaciones.

#### 📋 Reglas de frecuencia base

| Práctica | Intervalo mínimo | Máx/año | Severidad |
|---|---|---|---|
| TAC (cualquier región) | 180 días | 2 | ⚠️ Alerta |
| RMN (cualquier región) | 180 días | 2 | ⚠️ Alerta |
| Ecocardiograma doppler | 365 días | 1 | ⚠️ Alerta |
| Mamografía bilateral | 365 días | 1 | ⚠️ Alerta |

### Financiadores compatibles (Argentina)

El módulo reutiliza `insurance_companies` como catálogo de financiadores.  
Se pueden cargar obras sociales (OSDE, Swiss Medical, Galeno, IOMA, etc.) y prepagas usando el mismo formulario nativo de OpenEMR, reinterpretando los campos:

| Campo OpenEMR | Uso LATAM |
|---|---|
| `name` | Nombre de la obra social o prepaga |
| `cms_id` | RNOS (Registro Nacional de Obras Sociales) |
| `ins_type_code` | Tipo: OS Nacional / OS Provincial / Prepaga / ART |
| `inactive` | Convenio activo/suspendido globalmente |

---

## 🔌 Adaptadores de integración

El módulo define una interfaz plugable para conectar sistemas externos. Cada financiador puede tener su propia implementación.

### Interfaz del contrato

```php
interface CoverageAdapterInterface
{
    // Verifica si un afiliado tiene cobertura vigente
    public function checkEligibility(int $pid, int $insDataId, string $checkDate): EligibilityResultInterface;

    // Envía una solicitud de autorización previa
    public function requestAuthorization(int $pid, int $insCompanyId, string $codeType, string $code, int $quantity, array $extraParams): AuthorizationResultInterface;

    // Consulta el estado de una autorización enviada
    public function queryAuthorizationStatus(string $authNumber, int $insCompanyId): AuthorizationResultInterface;

    // Clave única del adaptador
    public function getAdapterKey(): string;

    // Verifica disponibilidad y configuración
    public function isAvailable(): bool;
}
```

### Adaptadores incluidos

| Adaptador | Clase | Descripción |
|---|---|---|
| `manual_fallback` | `ManualFallbackAdapter` | **Incluido.** Sin integración real. Registra operaciones como manuales para que el operador complete los datos vía interfaz. |

### Cómo agregar un adaptador nuevo

```php
// 1. Crear la clase que implementa la interfaz
class OsdeApiAdapter implements CoverageAdapterInterface
{
    public function __construct(private array $config) {}
    
    public function checkEligibility(int $pid, int $insDataId, string $checkDate): EligibilityResultInterface
    {
        // Llamada real a la API de OSDE
    }
    // ... resto de métodos
}

// 2. Registrar en covl_adapters
INSERT INTO covl_adapters (insurance_company_id, adapter_key, adapter_type, php_class, config_json)
VALUES (42, 'osde_api_v2', 'ambos', 'MiPaquete\\OsdeApiAdapter', '{"base_url":"https://api.osde.com.ar","token":"..."}');
```

El `AdapterRegistry` lo resuelve automáticamente al procesar solicitudes del financiador con `id = 42`.

---

## ⚙️ Servicios incluidos

### `AuthorizationService`
Orquesta el ciclo completo de una autorización:
1. Evalúa `covl_auth_rules` para determinar si la práctica es automática, requerida o exenta
2. Delega al adaptador del financiador
3. Persiste en `covl_authorizations` y registra el historial
4. Ofrece `linkToEncounter(authId, encounterId)` para vincular una autorización previa al encuentro clínico una vez que la práctica es facturada.

### `FrequencyCheckService`
Valida si una práctica puede realizarse según el intervalo mínimo y consumo anual:
- Consulta `covl_frequency_rules`
- Combina dos fuentes de antecedentes: `billing` (prácticas facturadas) y `covl_authorizations` (autorizaciones activas sin `encounter_id`, evitando doble conteo).
- **Filtro de estados:** Solo considera autorizaciones activas (`pendiente`, `en_auditoria`, `aprobada`). Las autorizaciones con estado `vencida`, `rechazada` o `cancelada` se excluyen correctamente ya que no representan una práctica realizada.
- **Limitación conocida (Bases de fecha heterogéneas):** Para `billing` se utiliza `fe.date` (fecha real del encuentro), mientras que para autorizaciones pendientes (`encounter_id IS NULL`) se utiliza `request_date` (fecha de solicitud). Dado que no hay un campo de fecha programada en autorizaciones pendientes, `request_date` es una aproximación razonable, aunque puede introducir desfasajes temporales si la autorización fue solicitada con mucha antelación a la fecha real del estudio.
- Retorna `FrequencyCheckResult` con `allowed`, `violation`, `severity`, `daysRemaining`

### `ProviderCoverageService`
Verifica la vigencia del convenio prestador × financiador:
- Método `check()` → valida para un profesional, financiador y fecha específica
- Método `getActiveInsurerIds()` → lista todos los financiadores vigentes de un profesional (para filtro de agenda)

### `AdapterRegistry`
Resuelve qué adaptador usar para cada financiador:
- Consulta `covl_adapters` por `insurance_company_id`
- Si la clase PHP existe y está disponible, la instancia dinámicamente
- Siempre tiene `ManualFallbackAdapter` como respaldo

---

## 📁 Estructura de archivos

```
oe-module-coverage-latam/
│
├── 📄 openemr.bootstrap.php       ← Registro de namespace + menú OpenEMR
├── 📄 moduleConfig.php            ← Panel del Module Manager
├── 📄 index.php                   ← Entrada estándar del módulo
├── 📄 version.php                 ← Versión y metadatos
├── 📄 composer.json               ← Autoload PSR-4
│
├── 📂 sql/
│   ├── 📄 install.sql             ← 12 tablas covl_* + datos iniciales
│   ├── 📄 uninstall.sql           ← DROP en orden FK-seguro
│   └── 📄 argentina_seed.sql      ← Reglas y semillas para Argentina 🇦🇷
│
└── 📂 src/
    ├── 📂 Contracts/              ← Interfaces del contrato (estables)
    │   ├── CoverageAdapterInterface.php
    │   ├── EligibilityResultInterface.php
    │   └── AuthorizationResultInterface.php
    │
    ├── 📂 Dto/                    ← Value objects inmutables
    │   ├── EligibilityResult.php
    │   └── AuthorizationResult.php
    │
    ├── 📂 Adapter/                ← Implementaciones de integraciones
    │   └── ManualFallbackAdapter.php
    │
    ├── 📂 Service/                ← Lógica de negocio
    │   ├── AuthorizationService.php
    │   ├── FrequencyCheckService.php
    │   ├── ProviderCoverageService.php
    │   └── AdapterRegistry.php
    │
    └── 📂 Repository/             ← Acceso a datos (sqlStatement nativo)
        └── AuthorizationRepository.php
```

---

## 🔗 Relación con OpenEMR nativo

### Tablas nativas reutilizadas (sin modificaciones)

| Tabla nativa | Rol en el módulo LATAM |
|---|---|
| `insurance_companies` | Catálogo de obras sociales, prepagas y ART |
| `insurance_data` | Cobertura vigente del paciente (plan, N° de afiliado, vigencia) |
| `form_encounter` | Encuentro clínico vinculado a autorizaciones y lotes |
| `billing` | Prestaciones facturables incluidas en los lotes de liquidación |
| `codes` + `code_types` | Catálogos de códigos; se registra el tipo NNAR para Argentina |
| `users` | Prestadores con convenio en `covl_provider_coverage` |
| `facility` | Sede vinculada a configuraciones y lotes |

### Motor de claims nativo

El módulo **no interfiere** con el motor EDI 837/835 de OpenEMR. Ambos pueden coexistir:

- Para financiadores norteamericanos: seguir usando el flujo nativo de claims
- Para financiadores LATAM: usar el flujo de `covl_settlement_batches`
- Un ítem de `billing` no puede estar en ambos flujos simultáneamente (controlado por unique key en `covl_settlement_items`)

---

## ❓ Preguntas frecuentes

**¿El módulo modifica tablas de OpenEMR?**  
No. Solo crea tablas propias con prefijo `covl_`. Las tablas nativas se usan mediante FK pero no se alteran.

**¿Puedo usar el módulo sin integración real con el financiador?**  
Sí. El `ManualFallbackAdapter` permite operar en modo manual completo: el operador gestiona autorizaciones y actualiza estados desde la interfaz del módulo.

**¿Cómo agrego soporte para otro país?**  
Desde el módulo: **Dashboard → Países → Agregar País** se instala un paquete del catálogo incluido (`packs/*.json` — Argentina, Chile, Colombia, México, Perú, Uruguay). Cada paquete registra el nomenclador nacional en `code_types`, da de alta el registro en `covl_country_packs` y carga las reglas base de autorización/frecuencia y equivalencias de códigos. Para un país nuevo: copiá `packs/xx.json`, completá los datos y ajustá `auth_rules`, `frequency_rules` y `code_maps`; luego instalalo desde la pestaña **Países** (o ejecutá `sql/miPais_seed.sql`).

**¿Qué pasa si un financiador tiene reglas distintas por plan?**  
Usá el campo `plan_pattern` en `covl_auth_rules` con comodines `%`. Ejemplo: `plan_name LIKE 'GOLD%'` → aplica a todos los planes que empiecen con "GOLD".

**¿El historial de estados tiene timestamp?**  
Sí. `covl_authorization_history` registra cada transición con `created_at`, el usuario que la realizó (`changed_by`) y el motivo del cambio.

---

## 🗺️ Hoja de ruta

### v1.0 — Base (actual)
- [x] Modelo de datos completo (12 tablas)
- [x] Contratos de adaptadores
- [x] Adaptador manual (fallback)
- [x] Servicio de autorizaciones
- [x] Validación de frecuencia
- [x] Convenios de prestadores
- [x] Paquete Argentina (semillas)

### v1.1 — En progreso
- [ ] Servicio y repositorio de lotes (`SettlementService`)
- [ ] Dashboard de gestión (PHP + UI)
- [ ] Clase `ArgentinaCountryPack` (carga programática)

### v1.2 — Planificado
- [ ] Integración AJAX: widget de autorización en el encuentro clínico
- [ ] Hook de check-in: validación automática de convenio prestador
- [ ] Exportación de lotes a Excel / PDF
- [ ] Importación masiva de convenios de prestadores

### v2.0 — Futuro
- [ ] Paquete Chile 🇨🇱 (FONASA, ISAPRES)
- [ ] Paquete Colombia 🇨🇴 (EPS, régimen subsidiado)
- [ ] API REST para consulta de autorizaciones desde sistemas externos
- [ ] Adaptador ARCA/OSDE para verificación en línea

---

## 👤 Autor y licencia

**Desarrollado por:** Luis A. Uriarte  
**Contacto:** luis.uriarte@gmail.com  
**Repositorio:** [github.com/openemr/oe-module-coverage-latam](https://github.com/openemr/oe-module-coverage-latam) *(próximamente)*

**Licencia:** [GNU General Public License v3.0](https://github.com/openemr/openemr/blob/master/LICENSE)

Este módulo es software libre: podés redistribuirlo y/o modificarlo bajo los términos de la Licencia Pública General GNU tal como la publica la Free Software Foundation, ya sea la versión 3 de la Licencia, o cualquier versión posterior.

---

<div align="center">

Desarrollado con ❤️ para la comunidad OpenEMR de América Latina

[📖 Documentación OpenEMR](https://www.open-emr.org/wiki) · [🐛 Reportar un issue](https://github.com/openemr/openemr/issues) · [💬 Foro de la comunidad](https://community.open-emr.org)

</div>
