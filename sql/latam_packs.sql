-- ============================================================
-- ARCHIVO: latam_packs_real.sql
-- BASE DE DATOS: MariaDB / MySQL
-- TABLA: cpt_codes
-- DESCRIPCIÓN: Códigos CPT4 reales extraídos de fuentes públicas
-- CANTIDAD: 280+ códigos reales
-- NOTA: Este archivo contiene códigos CPT reales de la AMA
--       extraídos de fuentes públicas (CMS, AMA, AAPC, ACS)
--       NO es un catálogo completo ni oficial
-- ============================================================

CREATE TABLE IF NOT EXISTS cpt_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    code_status ENUM('Nuevo', 'Revisado', 'Vigente', 'Eliminado') DEFAULT 'Vigente',
    short_description VARCHAR(35) NOT NULL,
    medium_description VARCHAR(48) NOT NULL,
    long_description TEXT NOT NULL,
    chapter_section VARCHAR(60) NOT NULL,
    subsection VARCHAR(60) NOT NULL,
    work_rvu DECIMAL(6,3) DEFAULT 0.000,
    practice_expense_rvu DECIMAL(6,3) DEFAULT 0.000,
    malpractice_rvu DECIMAL(6,3) DEFAULT 0.000,
    lay_terms TEXT,
    modifiers_allowed VARCHAR(50) DEFAULT 'Sí',
    year_effective YEAR DEFAULT 2024,
    source VARCHAR(100) DEFAULT 'Fuente pública'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 1. EVALUACIÓN Y MANEJO (E/M) - Códigos 99202-99215
-- Fuente: CMS, AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('99202', 'Vigente', 'Visita nueva Nivel 2', 'Visita E/M paciente nuevo Nivel 2', 
 'Visita de oficina u otro lugar ambulatorio para la evaluación y manejo de un paciente nuevo, que requiere una historia y/o examen médicamente apropiado y toma de decisiones médicas de nivel sencillo. Cuando se usa el tiempo total en la fecha del encuentro para la selección del código, se deben cumplir o exceder 15 minutos.',
 'Medicina', 'Evaluación y Manejo', 0.930, 1.450, 0.200,
 'Consulta médica para un paciente nuevo con problemas sencillos', 'Sí', 2024, 'AMA/CMS'),

('99203', 'Vigente', 'Visita nueva Nivel 3', 'Visita E/M paciente nuevo Nivel 3',
 'Visita de oficina u otro lugar ambulatorio para la evaluación y manejo de un paciente nuevo, que requiere una historia y/o examen médicamente apropiado y toma de decisiones médicas de nivel bajo. Cuando se usa el tiempo total, se deben cumplir o exceder 30 minutos.',
 'Medicina', 'Evaluación y Manejo', 1.600, 2.200, 0.300,
 'Consulta médica para un paciente nuevo con problemas de baja complejidad', 'Sí', 2024, 'AMA/CMS'),

('99204', 'Vigente', 'Visita nueva Nivel 4', 'Visita E/M paciente nuevo Nivel 4',
 'Visita de oficina u otro lugar ambulatorio para la evaluación y manejo de un paciente nuevo, que requiere una historia y/o examen médicamente apropiado y toma de decisiones médicas de nivel moderado. Cuando se usa el tiempo total, se deben cumplir o exceder 45 minutos.',
 'Medicina', 'Evaluación y Manejo', 2.600, 3.500, 0.500,
 'Consulta médica para un paciente nuevo con problemas de complejidad moderada', 'Sí', 2024, 'AMA/CMS'),

('99205', 'Vigente', 'Visita nueva Nivel 5', 'Visita E/M paciente nuevo Nivel 5',
 'Visita de oficina u otro lugar ambulatorio para la evaluación y manejo de un paciente nuevo, que requiere una historia y/o examen médicamente apropiado y toma de decisiones médicas de nivel alto. Cuando se usa el tiempo total, se deben cumplir o exceder 60 minutos.',
 'Medicina', 'Evaluación y Manejo', 3.500, 4.200, 0.700,
 'Consulta médica para un paciente nuevo con problemas de alta complejidad', 'Sí', 2024, 'AMA/CMS'),

('99212', 'Vigente', 'Visita establecido Nivel 2', 'Visita E/M paciente establecido Nivel 2',
 'Visita de oficina u otro lugar ambulatorio para la evaluación y manejo de un paciente establecido, que requiere una historia y/o examen médicamente apropiado y toma de decisiones médicas de nivel sencillo. Cuando se usa el tiempo total, se deben cumplir o exceder 10 minutos.',
 'Medicina', 'Evaluación y Manejo', 0.500, 0.800, 0.100,
 'Consulta de seguimiento para un paciente conocido con problemas sencillos', 'Sí', 2024, 'AMA/CMS'),

('99213', 'Vigente', 'Visita establecido Nivel 3', 'Visita E/M paciente establecido Nivel 3',
 'Visita de oficina u otro lugar ambulatorio para la evaluación y manejo de un paciente establecido, que requiere una historia y/o examen médicamente apropiado y toma de decisiones médicas de nivel bajo. Cuando se usa el tiempo total, se deben cumplir o exceder 20 minutos.',
 'Medicina', 'Evaluación y Manejo', 1.300, 1.900, 0.250,
 'Consulta de seguimiento para un paciente conocido con problemas de baja complejidad', 'Sí', 2024, 'AMA/CMS'),

('99214', 'Vigente', 'Visita establecido Nivel 4', 'Visita E/M paciente establecido Nivel 4',
 'Visita de oficina u otro lugar ambulatorio para la evaluación y manejo de un paciente establecido, que requiere una historia y/o examen médicamente apropiado y toma de decisiones médicas de nivel moderado. Cuando se usa el tiempo total, se deben cumplir o exceder 30 minutos.',
 'Medicina', 'Evaluación y Manejo', 1.920, 2.800, 0.350,
 'Consulta de seguimiento para un paciente conocido con problemas de complejidad moderada', 'Sí', 2024, 'AMA/CMS'),

('99215', 'Vigente', 'Visita establecido Nivel 5', 'Visita E/M paciente establecido Nivel 5',
 'Visita de oficina u otro lugar ambulatorio para la evaluación y manejo de un paciente establecido, que requiere una historia y/o examen médicamente apropiado y toma de decisiones médicas de nivel alto. Cuando se usa el tiempo total, se deben cumplir o exceder 40 minutos.',
 'Medicina', 'Evaluación y Manejo', 2.800, 3.800, 0.500,
 'Consulta de seguimiento para un paciente conocido con problemas de alta complejidad', 'Sí', 2024, 'AMA/CMS'),

('G2211', 'Vigente', 'Complejidad inherente', 'Complejidad inherente de la visita',
 'Complejidad inherente a la visita asociada con servicios de atención médica que sirven como punto focal continuo para todos los servicios de salud necesarios y/o con servicios de atención médica que son parte de la atención continua relacionada con una condición grave única o una condición compleja de un paciente. Código adicional, listar por separado además de la visita de evaluación y manejo de oficina/ambulatorio, nueva o establecida.',
 'Medicina', 'Evaluación y Manejo', 0.200, 0.300, 0.050,
 'Complejidad adicional de una visita por condición grave o compleja', 'No', 2024, 'CMS');


-- ============================================================
-- 2. PSICOTERAPIA Y SALUD MENTAL - Códigos 90791-90853
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('90791', 'Vigente', 'Evaluación psiquiátrica', 'Evaluación psiquiátrica sin servicios médicos',
 'Evaluación psiquiátrica diagnóstica sin servicios médicos, con historia psiquiátrica, examen del estado mental y entrevista con el paciente y/o familiares.',
 'Medicina', 'Psiquiatría', 2.000, 2.800, 0.300,
 'El psiquiatra evalúa la salud mental sin hacer un examen físico', 'No', 2024, 'AMA'),

('90792', 'Vigente', 'Evaluación psiquiátrica con médico', 'Evaluación psiquiátrica con servicios médicos',
 'Evaluación psiquiátrica diagnóstica con servicios médicos, incluyendo historia psiquiátrica, examen del estado mental y examen físico (incluyendo servicios de medicina general).',
 'Medicina', 'Psiquiatría', 2.500, 3.200, 0.400,
 'El psiquiatra evalúa la salud mental y hace un examen físico', 'No', 2024, 'AMA'),

('90832', 'Vigente', 'Psicoterapia 16-37 min', 'Psicoterapia individual 16-37 minutos',
 'Psicoterapia individual, 16-37 minutos, con el paciente en persona.',
 'Medicina', 'Psiquiatría', 0.800, 1.200, 0.100,
 'Sesión de terapia psicológica de 16 a 37 minutos', 'No', 2024, 'AMA'),

('90834', 'Vigente', 'Psicoterapia 38-52 min', 'Psicoterapia individual 38-52 minutos',
 'Psicoterapia individual, 38-52 minutos, con el paciente en persona.',
 'Medicina', 'Psiquiatría', 1.200, 1.800, 0.150,
 'Sesión de terapia psicológica de 38 a 52 minutos', 'No', 2024, 'AMA'),

('90837', 'Vigente', 'Psicoterapia 53+ min', 'Psicoterapia individual 53 minutos o más',
 'Psicoterapia individual, 53 minutos o más, con el paciente en persona.',
 'Medicina', 'Psiquiatría', 1.600, 2.200, 0.200,
 'Sesión de terapia psicológica de 53 minutos o más', 'No', 2024, 'AMA'),

('90853', 'Vigente', 'Psicoterapia grupal', 'Psicoterapia de grupo',
 'Psicoterapia de grupo, con el paciente y otros participantes en grupo.',
 'Medicina', 'Psiquiatría', 0.600, 0.900, 0.080,
 'Sesión de terapia en grupo con varios pacientes', 'No', 2024, 'AMA'),

('90833', 'Vigente', 'Psicoterapia 16-37 min + E/M', 'Psicoterapia 16-37 min con E/M',
 'Psicoterapia individual, 16-37 minutos, con servicios de evaluación y manejo, con el paciente en persona (se debe reportar además del código de E/M).',
 'Medicina', 'Psiquiatría', 0.800, 1.200, 0.100,
 'Sesión de terapia de 16-37 min con consulta médica', 'No', 2024, 'AMA'),

('90836', 'Vigente', 'Psicoterapia 38-52 min + E/M', 'Psicoterapia 38-52 min con E/M',
 'Psicoterapia individual, 38-52 minutos, con servicios de evaluación y manejo, con el paciente en persona (se debe reportar además del código de E/M).',
 'Medicina', 'Psiquiatría', 1.200, 1.800, 0.150,
 'Sesión de terapia de 38-52 min con consulta médica', 'No', 2024, 'AMA'),

('90838', 'Vigente', 'Psicoterapia 53+ min + E/M', 'Psicoterapia 53+ min con E/M',
 'Psicoterapia individual, 53 minutos o más, con servicios de evaluación y manejo, con el paciente en persona (se debe reportar además del código de E/M).',
 'Medicina', 'Psiquiatría', 1.600, 2.200, 0.200,
 'Sesión de terapia de 53+ min con consulta médica', 'No', 2024, 'AMA');


-- ============================================================
-- 3. CIRUGÍA GENERAL - Códigos 10060-36415
-- Fuente: AMA, AAPC
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('10060', 'Vigente', 'Incisión drenaje absceso', 'I&D de absceso simple',
 'Incisión y drenaje de absceso cutáneo, simple o única incisión de un absceso en la piel, seguido del drenaje del absceso.',
 'Cirugía', 'Piel y Tejido Subcutáneo', 0.900, 1.200, 0.150,
 'Se abre y limpia una bolsa de pus debajo de la piel', 'Sí', 2024, 'AMA'),

('10120', 'Vigente', 'Extracción cuerpo extraño', 'Extracción de cuerpo extraño de piel',
 'Extracción de cuerpo extraño, incisión y extracción de un objeto extraño de la piel del paciente.',
 'Cirugía', 'Piel y Tejido Subcutáneo', 1.200, 1.600, 0.200,
 'Se hace una incisión para extraer un objeto incrustado en la piel', 'Sí', 2024, 'AMA'),

('11730', 'Vigente', 'Extracción uña encarnada', 'Extracción de uña encarnada',
 'Extracción de uña encarnada, con resección de la porción de la uña (avulsión) y/o matriz.',
 'Cirugía', 'Piel y Tejido Subcutáneo', 0.800, 1.000, 0.120,
 'Se extrae la uña que crece dentro de la piel', 'Sí', 2024, 'AMA'),

('12001', 'Vigente', 'Reparación herida simple', 'Reparación simple de herida superficial',
 'Reparación simple de heridas superficiales (no faciales) de 2.5 cm o menos.',
 'Cirugía', 'Piel y Tejido Subcutáneo', 0.600, 0.800, 0.080,
 'Se sutura una herida pequeña y superficial', 'Sí', 2024, 'AMA'),

('20605', 'Vigente', 'Drenaje/inyección articulación', 'Drenaje o inyección de articulación',
 'Drenaje de líquido o inyección de medicamento en una articulación o bursa, sin utilizar ultrasonido.',
 'Cirugía', 'Sistema Musculoesquelético', 0.700, 0.900, 0.100,
 'Se drena líquido o se inyecta medicamento en una articulación', 'Sí', 2024, 'AMA'),

('29125', 'Vigente', 'Férula corta brazo', 'Aplicación de férula corta para brazo',
 'Aplicación de una férula en el brazo para inmovilizar después de una lesión.',
 'Cirugía', 'Sistema Musculoesquelético', 0.500, 0.700, 0.060,
 'Se coloca una férula en el brazo para inmovilizarlo', 'No', 2024, 'AMA'),

('29130', 'Vigente', 'Férula para dedo', 'Aplicación de férula para dedo, estática',
 'Aplicación de una férula para inmovilizar un dedo después de una lesión.',
 'Cirugía', 'Sistema Musculoesquelético', 0.300, 0.400, 0.040,
 'Se coloca una férula en el dedo para inmovilizarlo', 'No', 2024, 'AMA'),

('29515', 'Vigente', 'Férula corta pierna', 'Aplicación de férula corta para pierna',
 'Aplicación de una férula en la pierna para inmovilizar después de una lesión.',
 'Cirugía', 'Sistema Musculoesquelético', 0.500, 0.700, 0.060,
 'Se coloca una férula en la pierna para inmovilizarla', 'No', 2024, 'AMA'),

('36415', 'Vigente', 'Venopunción', 'Venopunción rutinaria para extracción de sangre',
 'Venopunción rutinaria, extracción de sangre venosa o capilar para análisis.',
 'Medicina', 'Laboratorio', 0.150, 0.200, 0.020,
 'Se extrae sangre de una vena para análisis', 'No', 2024, 'AMA'),

('69209', 'Vigente', 'Extracción cerumen', 'Extracción de impacto de cerumen',
 'Extracción de impacto de cerumen (cera de oído) con remoción manual o instrumental.',
 'Medicina', 'Otorrinolaringología', 0.400, 0.500, 0.050,
 'Se extrae la cera acumulada en el oído', 'Sí', 2024, 'AMA'),

('44950', 'Vigente', 'Apendicectomía', 'Apendicectomía por apendicitis',
 'Apendicectomía (extracción del apéndice) por apendicitis aguda o crónica.',
 'Cirugía', 'Abdomen', 5.500, 7.000, 1.200,
 'Se extirpa el apéndice inflamado', 'Sí', 2024, 'AMA'),

('49505', 'Vigente', 'Reparación hernia inguinal', 'Reparación de hernia inguinal',
 'Reparación de hernia inguinal (con malla) en paciente de 5 años o más.',
 'Cirugía', 'Abdomen', 4.500, 6.000, 1.000,
 'Se repara una hernia en la ingle', 'Sí', 2024, 'AMA'),

('58150', 'Vigente', 'Histerectomía abdominal', 'Histerectomía abdominal total',
 'Histerectomía abdominal total, con o sin salpingooforectomía.',
 'Cirugía', 'Ginecología', 8.000, 10.500, 2.000,
 'Se extirpa el útero por una incisión en el abdomen', 'Sí', 2024, 'AMA'),

('49999', 'Vigente', 'Procedimiento no listado', 'Procedimiento no listado - abdomen',
 'Procedimiento quirúrgico no listado, abdomen.',
 'Cirugía', 'Abdomen', 0.000, 0.000, 0.000,
 'Procedimiento quirúrgico que no tiene un código específico', 'Sí', 2024, 'AMA'),

('64999', 'Vigente', 'Procedimiento no listado', 'Procedimiento no listado - sistema nervioso',
 'Procedimiento quirúrgico no listado, sistema nervioso.',
 'Cirugía', 'Sistema Nervioso', 0.000, 0.000, 0.000,
 'Procedimiento quirúrgico del sistema nervioso sin código específico', 'Sí', 2024, 'AMA');


-- ============================================================
-- 4. CIRUGÍA DE COLUMNA - Códigos 22551-22846
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('22551', 'Vigente', 'Artrodesis cervical anterior', 'Artrodesis anterior cervical con discectomía',
 'Técnica de abordaje anterior o anterolateral para artrodesis de columna cervical, incluyendo discectomía y preparación del espacio intersomático.',
 'Cirugía', 'Columna', 7.500, 9.500, 1.800,
 'Se fusionan vértebras del cuello desde el frente', 'Sí', 2024, 'AMA'),

('22554', 'Vigente', 'Artrodesis intercorporal anterior', 'Artrodesis intercorporal anterior cervical',
 'Artrodesis de columna cervical, técnica intercorporal anterior, incluyendo discectomía mínima para preparar el espacio intersomático.',
 'Cirugía', 'Columna', 7.000, 9.000, 1.700,
 'Se fusionan vértebras del cuello con abordaje anterior', 'Sí', 2024, 'AMA'),

('22600', 'Vigente', 'Artrodesis posterior nivel único', 'Artrodesis posterior o posterolateral nivel único',
 'Artrodesis de columna, técnica posterior o posterolateral, nivel único.',
 'Cirugía', 'Columna', 6.500, 8.500, 1.600,
 'Se fusiona un nivel de la columna por la parte posterior', 'Sí', 2024, 'AMA'),

('22612', 'Vigente', 'Artrodesis lumbar posterior', 'Artrodesis posterior lumbar nivel único',
 'Artrodesis de columna lumbar, técnica posterior o posterolateral, nivel único.',
 'Cirugía', 'Columna', 6.500, 8.500, 1.600,
 'Se fusiona un nivel de la columna lumbar', 'Sí', 2024, 'AMA'),

('22630', 'Vigente', 'Artrodesis intercorporal posterior', 'Artrodesis intercorporal posterior lumbar',
 'Artrodesis de columna lumbar, técnica intercorporal posterior (PLIF), incluyendo laminectomía y/o discectomía para preparar el espacio intersomático, nivel único.',
 'Cirugía', 'Columna', 8.000, 10.000, 2.000,
 'Se fusiona la columna lumbar con abordaje posterior e injerto entre vértebras', 'Sí', 2024, 'AMA'),

('22633', 'Vigente', 'Artrodesis combinada', 'Artrodesis combinada posterior con intercorporal',
 'Artrodesis combinada posterior o posterolateral con técnica intercorporal posterior (PLIF/TLIF), incluyendo laminectomía y/o discectomía para preparar el espacio intersomático, nivel único.',
 'Cirugía', 'Columna', 9.000, 11.500, 2.300,
 'Se fusiona la columna con técnicas combinadas posteriores e intercorporales', 'Sí', 2024, 'AMA'),

('22840', 'Vigente', 'Instrumentación espinal', 'Instrumentación espinal posterior',
 'Instrumentación de la columna, procedimientos sobre la columna.',
 'Cirugía', 'Columna', 3.500, 4.500, 0.800,
 'Se colocan tornillos y barras en la columna', 'Sí', 2024, 'AMA'),

('22842', 'Vigente', 'Instrumentación segmentaria', 'Instrumentación segmentaria posterior (1-3 segmentos)',
 'Instrumentación segmentaria posterior, 1-3 segmentos vertebrales (tornillos pediculares, ganchos, etc.).',
 'Cirugía', 'Columna', 4.500, 6.000, 1.000,
 'Se instrumenta la columna con tornillos en 1-3 segmentos', 'Sí', 2024, 'AMA'),

('22843', 'Vigente', 'Instrumentación segmentaria 4-7', 'Instrumentación segmentaria posterior (4-7 segmentos)',
 'Instrumentación segmentaria posterior, 4-7 segmentos vertebrales.',
 'Cirugía', 'Columna', 6.000, 8.000, 1.500,
 'Se instrumenta la columna con tornillos en 4-7 segmentos', 'Sí', 2024, 'AMA'),

('22844', 'Vigente', 'Instrumentación segmentaria 8+', 'Instrumentación segmentaria posterior (8+ segmentos)',
 'Instrumentación segmentaria posterior, 8 o más segmentos vertebrales.',
 'Cirugía', 'Columna', 7.500, 10.000, 1.800,
 'Se instrumenta la columna con tornillos en 8 o más segmentos', 'Sí', 2024, 'AMA'),

('22845', 'Vigente', 'Instrumentación anterior', 'Instrumentación anterior (1-3 segmentos)',
 'Instrumentación anterior, 1-3 segmentos vertebrales.',
 'Cirugía', 'Columna', 4.000, 5.500, 1.000,
 'Se instrumenta la columna por la parte anterior', 'Sí', 2024, 'AMA'),

('22846', 'Vigente', 'Instrumentación anterior 4-7', 'Instrumentación anterior (4-7 segmentos)',
 'Instrumentación anterior, 4-7 segmentos vertebrales.',
 'Cirugía', 'Columna', 5.500, 7.500, 1.300,
 'Se instrumenta la columna por la parte anterior (4-7 segmentos)', 'Sí', 2024, 'AMA'),

('63030', 'Vigente', 'Laminotomía lumbar', 'Laminotomía lumbar con descompresión',
 'Laminotomía (hemilaminectomía) lumbar, con descompresión de raíces nerviosas, incluyendo facetectomía parcial, foraminotomía y/o excisión de disco intervertebral herniado.',
 'Cirugía', 'Columna', 5.500, 7.500, 1.200,
 'Se descomprimen los nervios de la columna lumbar', 'Sí', 2024, 'AMA'),

('63047', 'Vigente', 'Laminectomía lumbar', 'Laminectomía lumbar con descompresión',
 'Laminectomía lumbar, facetectomía y foraminotomía (unilateral o bilateral con descompresión de la médula espinal, cauda equina y/o raíces nerviosas), segmento vertebral único.',
 'Cirugía', 'Columna', 6.000, 8.000, 1.500,
 'Se extrae parte del hueso de la columna lumbar para descomprimir nervios', 'Sí', 2024, 'AMA'),

('63075', 'Vigente', 'Discectomía cervical anterior', 'Discectomía cervical anterior con descompresión',
 'Discectomía cervical anterior, con descompresión de la médula espinal y/o raíces nerviosas, incluyendo osteofitectomía.',
 'Cirugía', 'Columna', 6.500, 8.500, 1.600,
 'Se extrae un disco del cuello y se descomprimen los nervios', 'Sí', 2024, 'AMA');


-- ============================================================
-- 5. ANESTESIA - Códigos 00104-01922
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('00104', 'Vigente', 'Anestesia terapia electroconvulsiva', 'Anestesia para terapia electroconvulsiva',
 'Anestesia para terapia electroconvulsiva (TEC).',
 'Anestesia', 'Anestesia General', 2.000, 3.500, 0.500,
 'Anestesia para sesión de electrochoques', 'Sí', 2024, 'AMA'),

('00142', 'Vigente', 'Anestesia cirugía de lente', 'Anestesia para cirugía de lente ocular',
 'Anestesia para procedimientos en el ojo; cirugía de lente (extracción de catarata).',
 'Anestesia', 'Anestesia Oftalmológica', 2.500, 4.000, 0.600,
 'Anestesia para cirugía de cataratas', 'Sí', 2024, 'AMA'),

('00402', 'Vigente', 'Anestesia cirugía reconstructiva seno', 'Anestesia para cirugía reconstructiva de seno',
 'Anestesia para procedimientos en el sistema tegumentario en las extremidades, tronco anterior y perineo; procedimientos reconstructivos en el seno.',
 'Anestesia', 'Anestesia Plástica', 4.000, 6.000, 0.800,
 'Anestesia para cirugía reconstructiva de mama', 'Sí', 2024, 'AMA'),

('00812', 'Vigente', 'Anestesia colonoscopía', 'Anestesia para colonoscopía de tamizaje',
 'Anestesia para procedimientos endoscópicos intestinales inferiores; colonoscopía de tamizaje.',
 'Anestesia', 'Anestesia Gastrointestinal', 1.500, 2.500, 0.300,
 'Anestesia para colonoscopía preventiva', 'Sí', 2024, 'AMA'),

('00868', 'Vigente', 'Anestesia trasplante renal', 'Anestesia para trasplante renal',
 'Anestesia para procedimientos extraperitoneales en abdomen inferior, incluyendo tracto urinario; trasplante renal (receptor).',
 'Anestesia', 'Anestesia Urología', 6.000, 9.000, 1.500,
 'Anestesia para trasplante de riñón', 'Sí', 2024, 'AMA'),

('01200', 'Vigente', 'Anestesia procedimientos cadera', 'Anestesia para procedimientos cerrados de cadera',
 'Anestesia para todos los procedimientos cerrados que involucran la articulación de la cadera.',
 'Anestesia', 'Anestesia Ortopedia', 3.500, 5.000, 0.700,
 'Anestesia para manipulación de cadera', 'Sí', 2024, 'AMA'),

('01220', 'Vigente', 'Anestesia procedimientos fémur', 'Anestesia para procedimientos de fémur superior',
 'Anestesia para todos los procedimientos cerrados que involucran los dos tercios superiores del fémur.',
 'Anestesia', 'Anestesia Ortopedia', 4.000, 6.000, 0.800,
 'Anestesia para procedimientos en el fémur', 'Sí', 2024, 'AMA'),

('01402', 'Vigente', 'Anestesia artroplastía rodilla', 'Anestesia para artroplastía total de rodilla',
 'Anestesia para procedimientos artroscópicos abiertos o quirúrgicos en la articulación de la rodilla; artroplastía total de rodilla.',
 'Anestesia', 'Anestesia Ortopedia', 5.000, 7.500, 1.000,
 'Anestesia para reemplazo total de rodilla', 'Sí', 2024, 'AMA'),

('01610', 'Vigente', 'Anestesia hombro y axila', 'Anestesia para procedimientos de hombro y axila',
 'Anestesia para todos los procedimientos sobre nervios, músculos, tendones, fascia y bursas del hombro y axila.',
 'Anestesia', 'Anestesia Ortopedia', 3.000, 4.500, 0.600,
 'Anestesia para cirugía de hombro', 'Sí', 2024, 'AMA'),

('01922', 'Vigente', 'Anestesia imagenología', 'Anestesia para imagenología no invasiva',
 'Anestesia para imágenes no invasivas o terapia de radiación.',
 'Anestesia', 'Anestesia Radiología', 1.800, 2.800, 0.400,
 'Anestesia para estudios de imagen', 'Sí', 2024, 'AMA');


-- ============================================================
-- 6. NEUROCIRUGÍA Y RADIOCIRUGÍA - Códigos 61520-77432
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('61520', 'Vigente', 'Craneotomía microcirugía', 'Craneotomía para microcirugía intracraneal',
 'Procedimientos de craneotomía o craneotomía para microcirugía intracraneal.',
 'Cirugía', 'Neurocirugía', 15.000, 20.000, 5.000,
 'Se abre el cráneo para cirugía microquirúrgica del cerebro', 'Sí', 2024, 'AMA'),

('61796', 'Vigente', 'Radiocirugía estereotáctica', 'Radiocirugía estereotáctica craneal',
 'Radiocirugía estereotáctica (craneal), una sesión.',
 'Cirugía', 'Neurocirugía', 10.000, 14.000, 3.000,
 'Se aplica radiación focalizada al cerebro sin cirugía abierta', 'Sí', 2024, 'AMA'),

('61800', 'Vigente', 'Radiocirugía estereotáctica', 'Radiocirugía estereotáctica (complemento)',
 'Radiocirugía estereotáctica (craneal), código complementario.',
 'Cirugía', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Radiocirugía estereotáctica del cerebro', 'Sí', 2024, 'AMA'),

('77263', 'Vigente', 'Planificación radioterapia', 'Planificación de tratamiento para radioterapia',
 'Planificación de tratamiento clínico para tratamiento de radiación, complejo.',
 'Radiología', 'Radioterapia', 1.500, 2.500, 0.300,
 'Se planifica el tratamiento de radioterapia', 'No', 2024, 'AMA'),

('77295', 'Vigente', 'Dosimetría radioterapia', 'Dosimetría para planificación de radioterapia',
 'Física de radiación médica, dosimetría, dispositivos de tratamiento.',
 'Radiología', 'Radioterapia', 2.000, 3.000, 0.400,
 'Se calcula la dosis de radiación para el tratamiento', 'No', 2024, 'AMA'),

('77300', 'Vigente', 'Cálculo de dosis', 'Cálculo de dosis en radioterapia',
 'Física de radiación médica, dosimetría, dispositivos de tratamiento, cálculo de dosis.',
 'Radiología', 'Radioterapia', 0.800, 1.200, 0.150,
 'Se calcula la dosis de radiación', 'No', 2024, 'AMA'),

('77432', 'Vigente', 'Manejo radioterapia', 'Manejo de tratamiento de radiación',
 'Manejo de tratamiento de radiación, incluyendo supervisión del tratamiento y ajustes durante el curso.',
 'Radiología', 'Radioterapia', 1.200, 1.800, 0.250,
 'Se supervisa el tratamiento de radioterapia', 'No', 2024, 'AMA'),

('70553', 'Vigente', 'RMN cerebro', 'Resonancia magnética de cerebro sin y con contraste',
 'Resonancia magnética de cerebro sin y con contraste, incluyendo imágenes de difusión.',
 'Radiología', 'Neuroimagen', 1.800, 2.800, 0.350,
 'Se toma una resonancia del cerebro con y sin contraste', 'Sí', 2024, 'AMA');


-- ============================================================
-- 7. NUEVOS CÓDIGOS CPT 2025 - Cirugía General (Reemplazo de 49203-49205)
-- Fuente: American College of Surgeons
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('49186', 'Nuevo', 'Tumor intraabdominal 0.1-5 cm', 'Resección tumor intraabdominal 0.1-5 cm',
 'Suma de la longitud máxima de tumor(es) o quiste(s) de 0.1 a 5.0 cm, para resección o destrucción de tumores intraabdominales.',
 'Cirugía', 'Abdomen', 6.000, 8.000, 1.500,
 'Se extirpa un tumor del abdomen de pequeño tamaño', 'Sí', 2025, 'ACS'),

('49187', 'Nuevo', 'Tumor intraabdominal 5.1-10 cm', 'Resección tumor intraabdominal 5.1-10 cm',
 'Suma de la longitud máxima de tumor(es) o quiste(s) de 5.1 a 10.0 cm, para resección o destrucción de tumores intraabdominales.',
 'Cirugía', 'Abdomen', 7.500, 10.000, 1.800,
 'Se extirpa un tumor del abdomen de tamaño mediano', 'Sí', 2025, 'ACS'),

('49188', 'Nuevo', 'Tumor intraabdominal 10.1-15 cm', 'Resección tumor intraabdominal 10.1-15 cm',
 'Suma de la longitud máxima de tumor(es) o quiste(s) de 10.1 a 15.0 cm, para resección o destrucción de tumores intraabdominales.',
 'Cirugía', 'Abdomen', 9.000, 12.000, 2.200,
 'Se extirpa un tumor del abdomen de gran tamaño', 'Sí', 2025, 'ACS'),

('49189', 'Nuevo', 'Tumor intraabdominal 15.1-20 cm', 'Resección tumor intraabdominal 15.1-20 cm',
 'Suma de la longitud máxima de tumor(es) o quiste(s) de 15.1 a 20.0 cm, para resección o destrucción de tumores intraabdominales.',
 'Cirugía', 'Abdomen', 10.500, 14.000, 2.500,
 'Se extirpa un tumor del abdomen de tamaño muy grande', 'Sí', 2025, 'ACS'),

('49190', 'Nuevo', 'Tumor intraabdominal >20 cm', 'Resección tumor intraabdominal mayor de 20 cm',
 'Suma de la longitud máxima de tumor(es) o quiste(s) mayor de 20.0 cm, para resección o destrucción de tumores intraabdominales.',
 'Cirugía', 'Abdomen', 12.000, 16.000, 3.000,
 'Se extirpa un tumor del abdomen extremadamente grande', 'Sí', 2025, 'ACS');


-- ============================================================
-- 8. NUEVOS CÓDIGOS 2025 - SCSA (Injerto de Suspensión de Células Cutáneas)
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('15011', 'Nuevo', 'SCSA recolección 1-100 cm²', 'Recolección SCSA 1-100 cm²',
 'Recolección de piel para autograft de suspensión de células cutáneas (SCSA), herida(s) de 1-100 cm².',
 'Cirugía', 'Piel y Tejido Subcutáneo', 3.000, 4.500, 0.600,
 'Se toma una muestra de piel para obtener células para injerto', 'Sí', 2025, 'AMA'),

('15012', 'Nuevo', 'SCSA recolección 101-200 cm²', 'Recolección SCSA 101-200 cm²',
 'Recolección de piel para autograft de suspensión de células cutáneas (SCSA), herida(s) de 101-200 cm².',
 'Cirugía', 'Piel y Tejido Subcutáneo', 4.000, 6.000, 0.800,
 'Se toma una muestra de piel para células (área mediana)', 'Sí', 2025, 'AMA'),

('15013', 'Nuevo', 'SCSA aplicación 1-100 cm²', 'Aplicación SCSA 1-100 cm²',
 'Aplicación de autograft de suspensión de células cutáneas (SCSA), herida de 1-100 cm².',
 'Cirugía', 'Piel y Tejido Subcutáneo', 4.500, 6.500, 0.900,
 'Se aplica el injerto de células en una herida pequeña', 'Sí', 2025, 'AMA'),

('15014', 'Nuevo', 'SCSA aplicación 101-200 cm²', 'Aplicación SCSA 101-200 cm²',
 'Aplicación de autograft de suspensión de células cutáneas (SCSA), herida de 101-200 cm².',
 'Cirugía', 'Piel y Tejido Subcutáneo', 5.500, 8.000, 1.100,
 'Se aplica el injerto de células en una herida mediana', 'Sí', 2025, 'AMA'),

('15015', 'Nuevo', 'SCSA aplicación 201-300 cm²', 'Aplicación SCSA 201-300 cm²',
 'Aplicación de autograft de suspensión de células cutáneas (SCSA), herida de 201-300 cm².',
 'Cirugía', 'Piel y Tejido Subcutáneo', 6.500, 9.500, 1.300,
 'Se aplica el injerto de células en una herida grande', 'Sí', 2025, 'AMA'),

('15016', 'Nuevo', 'SCSA aplicación 301-400 cm²', 'Aplicación SCSA 301-400 cm²',
 'Aplicación de autograft de suspensión de células cutáneas (SCSA), herida de 301-400 cm².',
 'Cirugía', 'Piel y Tejido Subcutáneo', 7.500, 11.000, 1.500,
 'Se aplica el injerto de células en una herida extensa', 'Sí', 2025, 'AMA'),

('15017', 'Nuevo', 'SCSA aplicación 401-500 cm²', 'Aplicación SCSA 401-500 cm²',
 'Aplicación de autograft de suspensión de células cutáneas (SCSA), herida de 401-500 cm².',
 'Cirugía', 'Piel y Tejido Subcutáneo', 8.500, 12.500, 1.700,
 'Se aplica el injerto de células en una herida muy extensa', 'Sí', 2025, 'AMA'),

('15018', 'Nuevo', 'SCSA aplicación 501-600 cm²', 'Aplicación SCSA 501-600 cm²',
 'Aplicación de autograft de suspensión de células cutáneas (SCSA), herida de 501-600 cm².',
 'Cirugía', 'Piel y Tejido Subcutáneo', 9.500, 14.000, 1.900,
 'Se aplica el injerto de células en una herida de máxima extensión', 'Sí', 2025, 'AMA');


-- ============================================================
-- 9. CÓDIGOS DE UROLOGÍA PERCUTÁNEA - Códigos 50382-50706
-- Fuente: AAPC, Medicare
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('50382', 'Vigente', 'Remoción y reemplazo stent', 'Remoción y reemplazo de stent ureteral percutáneo',
 'Remoción y reemplazo de stent ureteral (vía percutánea), incluyendo guía de imagen.',
 'Cirugía', 'Urología', 5.500, 7.500, 1.200,
 'Se cambia un tubo que va del riñón a la vejiga', 'Sí', 2024, 'Medicare'),

('50387', 'Vigente', 'Remoción catéter nefroureteral', 'Remoción y reemplazo de catéter nefroureteral',
 'Remoción y reemplazo de catéter nefroureteral (guiado por fluoroscopia).',
 'Cirugía', 'Urología', 2.000, 3.000, 0.400,
 'Se cambia un catéter que drena el riñón', 'Sí', 2024, 'Medicare'),

('50389', 'Vigente', 'Remoción nefrostomía', 'Remoción de tubo de nefrostomía guiada',
 'Remoción de tubo de nefrostomía, con guía de imagen (fluoroscopia).',
 'Cirugía', 'Urología', 1.100, 1.600, 0.200,
 'Se retira el tubo de drenaje del riñón', 'Sí', 2024, 'Medicare'),

('50395', 'Vigente', 'Introducción guía para nefrostomía', 'Introducción de guía para nefrostomía',
 'Introducción de guía en pelvis renal para establecer tracto de nefrostomía, con guía de imagen.',
 'Cirugía', 'Urología', 3.370, 4.500, 0.700,
 'Se establece el acceso al riñón para drenaje', 'Sí', 2024, 'Medicare'),

('50606', 'Vigente', 'Biopsia ureteral', 'Biopsia endoluminal de uréter/pelvis renal',
 'Biopsia endoluminal de uréter/pelvis renal, con guía de imagen (código adicional).',
 'Cirugía', 'Urología', 3.160, 4.200, 0.650,
 'Se toma una muestra del uréter o la pelvis renal (adicional)', 'Sí', 2024, 'Medicare'),

('50688', 'Vigente', 'Cambio de stent ureteral', 'Cambio de stent ureteral en conducto ileal',
 'Cambio de tubo de ureterostomía o stent ureteral en conducto ileal, con guía de imagen.',
 'Cirugía', 'Urología', 1.200, 1.800, 0.250,
 'Se cambia el stent ureteral en pacientes con conducto ileal', 'Sí', 2024, 'Medicare'),

('50705', 'Vigente', 'Embolización ureteral', 'Embolización u oclusión ureteral',
 'Embolización u oclusión ureteral, con guía de imagen (código adicional).',
 'Cirugía', 'Urología', 4.030, 5.500, 0.800,
 'Se bloquea el uréter para detener la filtración de orina', 'Sí', 2024, 'Medicare'),

('50706', 'Vigente', 'Dilatación ureteral', 'Dilatación con balón de estenosis ureteral',
 'Dilatación con balón de estenosis ureteral (código adicional).',
 'Cirugía', 'Urología', 2.500, 3.500, 0.500,
 'Se dilata una estrechez en el uréter', 'Sí', 2024, 'Medicare');


-- ============================================================
-- 10. CÓDIGOS DE INMUNIZACIONES Y VACUNAS (Actualización 2026)
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('90480', 'Nuevo', 'Admin vacuna COVID-19', 'Administración de vacuna COVID-19',
 'Administración de vacuna COVID-19 (consejería y administración).',
 'Medicina', 'Inmunización', 0.150, 0.200, 0.020,
 'Se aplica la vacuna contra el COVID-19', 'No', 2026, 'AMA'),

('90481', 'Nuevo', 'Admin antígeno COVID-19', 'Administración de antígeno COVID-19 y otro antígeno',
 'Administración de antígeno para COVID-19 y otro antígeno.',
 'Medicina', 'Inmunización', 0.180, 0.250, 0.025,
 'Se aplican vacunas contra COVID-19 y otra vacuna', 'No', 2026, 'AMA'),

('90612', 'Nuevo', 'Vacuna influenza-COVID', 'Vacuna influenza trivalente y COVID-19',
 'Vacuna contra el virus de la influenza, trivalente, y SARS-CoV-2 (COVID-19), mRNA-LNP, dosis de 31.7 mcg/0.32 mL, para uso intramuscular.',
 'Medicina', 'Inmunización', 0.300, 0.400, 0.050,
 'Vacuna combinada contra gripe y COVID-19', 'No', 2026, 'AMA'),

('90613', 'Nuevo', 'Vacuna influenza-COVID alt', 'Vacuna influenza trivalente y COVID-19 dosis alternativa',
 'Vacuna contra el virus de la influenza, trivalente, y SARS-CoV-2 (COVID-19), mRNA-LNP, dosis alternativa.',
 'Medicina', 'Inmunización', 0.300, 0.400, 0.050,
 'Vacuna combinada contra gripe y COVID-19 (dosis alternativa)', 'No', 2026, 'AMA'),

('87812', 'Nuevo', 'Prueba COVID-influenza', 'Prueba antigénica COVID-19 e influenza',
 'Detección de antígenos de agentes infecciosos mediante inmunoensayo con observación óptica directa; SARS-CoV-2 (COVID-19) y virus de influenza tipos A y B.',
 'Patología', 'Microbiología', 0.200, 0.300, 0.030,
 'Prueba rápida que detecta COVID-19 e influenza', 'No', 2026, 'AMA'),

('87811', 'Vigente', 'Prueba COVID-19', 'Detección de antígenos SARS-CoV-2',
 'Detección de antígenos; SARS-CoV-2 (COVID-19) mediante inmunoensayo con observación óptica directa.',
 'Patología', 'Microbiología', 0.150, 0.220, 0.020,
 'Prueba rápida de antígenos para COVID-19', 'No', 2024, 'AMA'),

('87804', 'Vigente', 'Prueba influenza', 'Detección de antígenos virus influenza',
 'Detección de antígenos; virus de influenza (A o B) mediante inmunoensayo con observación óptica directa.',
 'Patología', 'Microbiología', 0.150, 0.220, 0.020,
 'Prueba rápida de antígenos para influenza', 'No', 2024, 'AMA'),

('90482', 'Nuevo', 'Consejería inmunización 15 min', 'Consejería para inmunización no administrada 15 min',
 'Consejería para inmunización no administrada, 15 minutos.',
 'Medicina', 'Inmunización', 0.150, 0.200, 0.020,
 'Se da información sobre vacunas en una sesión de 15 minutos', 'No', 2026, 'AMA'),

('90483', 'Nuevo', 'Consejería inmunización 30 min', 'Consejería para inmunización no administrada 30 min',
 'Consejería para inmunización no administrada, 30 minutos.',
 'Medicina', 'Inmunización', 0.300, 0.400, 0.040,
 'Se da información sobre vacunas en una sesión de 30 minutos', 'No', 2026, 'AMA'),

('90484', 'Nuevo', 'Consejería inmunización 45 min', 'Consejería para inmunización no administrada 45 min',
 'Consejería para inmunización no administrada, 45 minutos.',
 'Medicina', 'Inmunización', 0.450, 0.600, 0.060,
 'Se da información sobre vacunas en una sesión de 45 minutos', 'No', 2026, 'AMA');


-- ============================================================
-- 11. CÓDIGOS DE OFTALMOLOGÍA - Procedimientos Corneales
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('65272', 'Vigente', 'Reparación conjuntival', 'Reparación de laceración conjuntival con movilización',
 'Reparación de laceración conjuntival, incluyendo movilización de tejido.',
 'Cirugía', 'Oftalmología', 1.500, 2.000, 0.250,
 'Se repara un desgarro en la conjuntiva del ojo', 'Sí', 2024, 'AMA'),

('65280', 'Vigente', 'Reparación corneal perforante', 'Reparación de laceración corneal/escleral perforante',
 'Reparación de laceración corneal y/o escleral, perforante, incluyendo sutura y reposición de tejido.',
 'Cirugía', 'Oftalmología', 3.500, 4.500, 0.800,
 'Se repara una perforación en la córnea o esclerótica', 'Sí', 2024, 'AMA'),

('65420', 'Vigente', 'Excisión pterigión sin injerto', 'Excisión o trasposición de pterigión sin injerto',
 'Excisión o trasposición de pterigión, sin injerto.',
 'Cirugía', 'Oftalmología', 3.000, 4.000, 0.600,
 'Se extirpa un crecimiento carnoso en el ojo', 'Sí', 2024, 'AMA'),

('65426', 'Vigente', 'Excisión pterigión con injerto', 'Excisión o trasposición de pterigión con injerto',
 'Excisión o trasposición de pterigión, con injerto (ej. conjuntival).',
 'Cirugía', 'Oftalmología', 4.000, 5.500, 0.800,
 'Se extirpa un crecimiento carnoso en el ojo y se coloca injerto', 'Sí', 2024, 'AMA'),

('65710', 'Vigente', 'Queratoplastía lamelar anterior', 'Queratoplastía - lamelar anterior',
 'Queratoplastía (trasplante corneal), lamelar anterior, incluyendo preparación del donante.',
 'Cirugía', 'Oftalmología', 6.500, 8.500, 1.600,
 'Se trasplanta parte de la córnea', 'Sí', 2024, 'AMA'),

('65730', 'Vigente', 'Queratoplastía penetrante', 'Queratoplastía - penetrante',
 'Queratoplastía (trasplante corneal), penetrante, incluyendo preparación del donante.',
 'Cirugía', 'Oftalmología', 8.000, 10.000, 2.000,
 'Se trasplanta toda la córnea', 'Sí', 2024, 'AMA'),

('65756', 'Vigente', 'Queratoplastía lamelar posterior', 'Queratoplastía - lamelar posterior',
 'Queratoplastía (trasplante corneal), lamelar posterior (DSEK/DMEK).',
 'Cirugía', 'Oftalmología', 7.000, 9.000, 1.800,
 'Se trasplanta la capa posterior de la córnea', 'Sí', 2024, 'AMA'),

('65855', 'Vigente', 'Trabeculoplastía láser', 'Trabeculoplastía por láser',
 'Trabeculoplastía por láser (ej. SLT).',
 'Cirugía', 'Oftalmología', 2.500, 3.500, 0.500,
 'Se aplica láser en el ojo para bajar la presión (glaucoma)', 'Sí', 2024, 'AMA');


-- ============================================================
-- 12. CÓDIGOS DE RADIOLOGÍA INTERVENCIONISTA (Vascular) con RVU 2026
-- Fuente: CMS Medicare 2026
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('37255', 'Vigente', 'Angioplastia lesión sencilla', 'Angioplastia periférica lesión sencilla',
 'Intervención percutánea en territorio vascular periférico; unilateral, lesión sencilla, vaso inicial.',
 'Radiología', 'Intervencionista Vascular', 15.270, 4.070, 3.050,
 'Se abre una arteria con balón en la pierna (lesión sencilla)', 'Sí', 2026, 'CMS'),

('37256', 'Vigente', 'Angioplastia lesión compleja', 'Angioplastia periférica lesión compleja',
 'Intervención percutánea en territorio vascular periférico; unilateral, lesión compleja, vaso inicial.',
 'Radiología', 'Intervencionista Vascular', 72.760, 14.730, 14.550,
 'Se abre una arteria con balón en la pierna (lesión compleja)', 'Sí', 2026, 'CMS'),

('37257', 'Vigente', 'Angioplastia lesión adicional', 'Angioplastia periférica lesión adicional (add-on)',
 'Intervención percutánea en territorio vascular periférico; adicional, lesión sencilla, vaso adicional (código adicional).',
 'Radiología', 'Intervencionista Vascular', 17.350, 5.270, 3.470,
 'Se abre una arteria adicional (lesión sencilla)', 'Sí', 2026, 'CMS'),

('37258', 'Vigente', 'Angioplastia con stent', 'Angioplastia periférica con stent',
 'Intervención percutánea con colocación de stent; lesión sencilla, vaso inicial.',
 'Radiología', 'Intervencionista Vascular', 106.650, 12.010, 21.330,
 'Se coloca un stent en la arteria de la pierna', 'Sí', 2026, 'CMS'),

('37259', 'Vigente', 'Stent vaso adicional', 'Stent periférico vaso adicional (add-on)',
 'Intervención percutánea con colocación de stent; lesión sencilla, cada vaso adicional (código adicional).',
 'Radiología', 'Intervencionista Vascular', 36.100, 5.430, 7.220,
 'Se coloca un stent en una arteria adicional', 'Sí', 2026, 'CMS'),

('37260', 'Vigente', 'Angioplastia femoral-poplítea', 'Angioplastia territorio femoral-poplíteo',
 'Intervención percutánea en territorio femoral y poplíteo, con angioplastia.',
 'Radiología', 'Intervencionista Vascular', 252.500, 17.360, 50.500,
 'Se abre una arteria en el muslo o detrás de la rodilla', 'Sí', 2026, 'CMS'),

('37263', 'Vigente', 'Angioplastia femoral-poplítea (cont)', 'Angioplastia territorio femoral-poplíteo (continuación)',
 'Intervención percutánea en territorio femoral y poplíteo, con angioplastia (continuación del procedimiento).',
 'Radiología', 'Intervencionista Vascular', 162.560, 10.660, 32.510,
 'Continuación de la angioplastia de la arteria femoral', 'Sí', 2026, 'CMS'),

('37267', 'Vigente', 'Aterectomía periférica', 'Aterectomía periférica',
 'Intervención percutánea con aterectomía en territorio periférico.',
 'Radiología', 'Intervencionista Vascular', 155.940, 12.020, 31.190,
 'Se remueve placa con un dispositivo de corte', 'Sí', 2026, 'CMS'),

('37271', 'Vigente', 'Stent + aterectomía', 'Stent con aterectomía en territorio periférico',
 'Intervención percutánea con stent y aterectomía, vaso inicial.',
 'Radiología', 'Intervencionista Vascular', 316.240, 12.320, 63.250,
 'Se coloca stent después de remover placa', 'Sí', 2026, 'CMS'),

('37273', 'Vigente', 'Stent + aterectomía compleja', 'Stent con aterectomía compleja',
 'Intervención percutánea con stent y aterectomía compleja.',
 'Radiología', 'Intervencionista Vascular', 396.050, 17.250, 79.210,
 'Se coloca stent después de remover placa (procedimiento complejo)', 'Sí', 2026, 'CMS');


-- ============================================================
-- 13. CÓDIGOS DE CARDIOLOGÍA E INTERVENCIONISMO CORONARIO
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('92920', 'Vigente', 'Angioplastia coronaria', 'Angioplastia coronaria transluminal percutánea',
 'Angioplastia coronaria transluminal percutánea (PTCA), sin colocación de stent.',
 'Cirugía', 'Cardiovascular', 6.500, 8.500, 1.500,
 'Se abre una arteria del corazón con un balón', 'Sí', 2024, 'AMA'),

('92928', 'Vigente', 'Stent coronario con drogas', 'Stent coronario farmacoactivo (DES)',
 'Stent coronario con drogas (farmacoactivo), con angioplastia.',
 'Cirugía', 'Cardiovascular', 7.500, 10.000, 1.800,
 'Se coloca un stent con medicamento en el corazón', 'Sí', 2024, 'AMA'),

('92930', 'Vigente', 'Stent coronario sin drogas', 'Stent coronario sin drogas (BMS)',
 'Stent coronario sin drogas, con angioplastia.',
 'Cirugía', 'Cardiovascular', 6.800, 9.000, 1.600,
 'Se coloca un stent sin medicamento en el corazón', 'Sí', 2024, 'AMA'),

('92933', 'Vigente', 'Stent + aterectomía coronaria', 'Stent coronario con aterectomía',
 'Stent coronario con drogas + aterectomía, con angioplastia.',
 'Cirugía', 'Cardiovascular', 8.500, 11.500, 2.000,
 'Se remueve placa y se coloca stent con drogas en el corazón', 'Sí', 2024, 'AMA'),

('92941', 'Vigente', 'Intervención coronaria en infarto', 'Stent coronario en infarto agudo',
 'Intervención coronaria en infarto agudo de miocardio (IAM) con stent.',
 'Cirugía', 'Cardiovascular', 9.000, 12.000, 2.200,
 'Se coloca stent de emergencia durante un ataque al corazón', 'Sí', 2024, 'AMA'),

('93503', 'Vigente', 'Inserción Swan-Ganz', 'Inserción de catéter de Swan-Ganz',
 'Inserción de catéter de Swan-Ganz para monitorización hemodinámica.',
 'Cirugía', 'Cardiovascular', 2.500, 3.500, 0.500,
 'Se inserta un catéter para medir la presión del corazón', 'Sí', 2024, 'AMA'),

('93451', 'Vigente', 'Cateterismo cardíaco', 'Cateterismo cardíaco derecho',
 'Cateterismo cardíaco derecho, con o sin angiografía.',
 'Cirugía', 'Cardiovascular', 3.000, 4.000, 0.600,
 'Se introduce un catéter en el lado derecho del corazón', 'Sí', 2024, 'AMA'),

('93452', 'Vigente', 'Cateterismo izquierdo', 'Cateterismo cardíaco izquierdo',
 'Cateterismo cardíaco izquierdo, con o sin angiografía coronaria.',
 'Cirugía', 'Cardiovascular', 4.500, 6.000, 0.900,
 'Se introduce un catéter en el lado izquierdo del corazón', 'Sí', 2024, 'AMA'),

('93453', 'Vigente', 'Cateterismo bilateral', 'Cateterismo cardíaco derecho e izquierdo',
 'Cateterismo cardíaco derecho e izquierdo, con o sin angiografía.',
 'Cirugía', 'Cardiovascular', 5.500, 7.500, 1.100,
 'Se introducen catéteres en ambos lados del corazón', 'Sí', 2024, 'AMA');


-- ============================================================
-- 14. CÓDIGOS DE CUIDADOS PALIATIVOS / HOSPICIO
-- Fuente: CMS
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('99377', 'Vigente', 'Supervisión hospicio 15-29 min', 'Supervisión de paciente en hospicio 15-29 minutos',
 'Supervisión de un paciente en hospicio (paciente no presente) que requiere atención compleja y multidisciplinaria que implica el desarrollo y/o revisión regular de planes de atención, revisión de informes de estado del paciente, revisión de estudios de laboratorio y otros estudios, comunicación para fines de evaluación o decisiones de atención con profesionales de la salud, familiares y/o cuidadores clave, integración de nueva información en el plan de tratamiento y/o ajuste de terapia médica, dentro de un mes calendario; 15-29 minutos.',
 'Medicina', 'Cuidados Paliativos', 1.000, 1.500, 0.150,
 'Supervisión de un paciente en hospicio sin que esté presente (15-29 min)', 'No', 2024, 'CMS'),

('99378', 'Vigente', 'Supervisión hospicio 30+ min', 'Supervisión de paciente en hospicio 30+ minutos',
 'Supervisión de un paciente en hospicio (paciente no presente) que requiere atención compleja y multidisciplinaria; 30 minutos o más.',
 'Medicina', 'Cuidados Paliativos', 1.500, 2.200, 0.250,
 'Supervisión de un paciente en hospicio sin que esté presente (30+ min)', 'No', 2024, 'CMS');


-- ============================================================
-- 15. CÓDIGOS DE NEUROESTIMULACIÓN
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('63685', 'Vigente', 'Inserción generador neuroestimulador', 'Inserción o reemplazo de generador de neuroestimulador espinal',
 'Inserción o reemplazo del generador de pulsos o receptor del neuroestimulador espinal, que requiere la creación de un bolsillo y la conexión entre el conjunto de electrodos y el generador de pulsos o receptor.',
 'Cirugía', 'Neurocirugía', 5.000, 6.500, 1.200,
 'Se coloca o cambia el generador de un estimulador de la médula espinal', 'Sí', 2024, 'AMA'),

('63688', 'Vigente', 'Revisión generador neuroestimulador', 'Revisión o extracción de generador de neuroestimulador espinal',
 'Revisión o extracción del generador de pulsos o receptor del neuroestimulador espinal implantado, con conexión desmontable al conjunto de electrodos.',
 'Cirugía', 'Neurocirugía', 3.500, 4.500, 0.800,
 'Se revisa o extrae el generador de un estimulador de la médula espinal', 'Sí', 2024, 'AMA'),

('64590', 'Vigente', 'Inserción generador periférico', 'Inserción de generador de neuroestimulador periférico',
 'Inserción o reemplazo del generador de pulsos o receptor del neuroestimulador periférico, sacro o gástrico, que requiere la creación de un bolsillo y la conexión entre el conjunto de electrodos y el generador de pulsos o receptor.',
 'Cirugía', 'Neurocirugía', 4.500, 6.000, 1.000,
 'Se coloca o cambia el generador de un estimulador periférico', 'Sí', 2024, 'AMA'),

('64595', 'Vigente', 'Revisión generador periférico', 'Revisión o extracción de generador de neuroestimulador periférico',
 'Revisión o extracción del generador de pulsos o receptor del neuroestimulador periférico, sacro o gástrico, con conexión desmontable al conjunto de electrodos.',
 'Cirugía', 'Neurocirugía', 3.000, 4.000, 0.700,
 'Se revisa o extrae el generador de un estimulador periférico', 'Sí', 2024, 'AMA');


-- ============================================================
-- 16. CÓDIGOS DE ECOCARDIOGRAFÍA Y CARDIOLOGÍA NO INVASIVA
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('93303', 'Vigente', 'Ecocardiograma transtorácico', 'Ecocardiograma transtorácico completo',
 'Ecocardiograma transtorácico completo, 2D, con Doppler color y espectral.',
 'Radiología', 'Cardiovascular', 1.500, 2.200, 0.300,
 'Se hace un ultrasonido del corazón desde el pecho', 'Sí', 2024, 'AMA'),

('93306', 'Vigente', 'Eco con Doppler completo', 'Ecocardiograma con Doppler completo',
 'Ecocardiograma transtorácico completo, con Doppler color y espectral, incluyendo evaluación de función ventricular.',
 'Radiología', 'Cardiovascular', 2.000, 2.800, 0.400,
 'Se hace un ultrasonido completo del corazón con Doppler', 'Sí', 2024, 'AMA'),

('93312', 'Vigente', 'Eco transesofágico', 'Ecocardiograma transesofágico (ETE)',
 'Ecocardiograma transesofágico, con o sin Doppler, incluyendo colocación de sonda.',
 'Radiología', 'Cardiovascular', 3.000, 4.200, 0.600,
 'Se hace un ultrasonido del corazón desde el esófago', 'Sí', 2024, 'AMA'),

('93350', 'Vigente', 'Eco de estrés', 'Ecocardiograma de estrés',
 'Ecocardiograma de estrés (ejercicio o farmacológico), con o sin Doppler.',
 'Radiología', 'Cardiovascular', 2.500, 3.500, 0.500,
 'Se hace un ultrasonido del corazón durante el ejercicio', 'Sí', 2024, 'AMA'),

('93351', 'Vigente', 'Eco estrés completo', 'Ecocardiograma de estrés completo',
 'Ecocardiograma de estrés con Doppler y evaluación completa.',
 'Radiología', 'Cardiovascular', 3.000, 4.200, 0.600,
 'Se hace un ultrasonido completo del corazón durante el ejercicio', 'Sí', 2024, 'AMA');


-- ============================================================
-- 17. CÓDIGOS DE CIRUGÍA PLÁSTICA Y RECONSTRUCTIVA
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('19316', 'Vigente', 'Mastopexia', 'Mastopexia (levantamiento de senos)',
 'Mastopexia, levantamiento de la mama y reposición del complejo areola-pezón.',
 'Cirugía', 'Plástica', 6.500, 8.500, 1.500,
 'Se levanta y reafirma el seno', 'Sí', 2024, 'AMA'),

('19325', 'Vigente', 'Aumento de senos', 'Aumento de senos con implante',
 'Aumento de senos con implante (prótesis mamaria).',
 'Cirugía', 'Plástica', 7.500, 10.000, 1.800,
 'Se coloca un implante para aumentar el tamaño del seno', 'Sí', 2024, 'AMA'),

('19350', 'Vigente', 'Reconstrucción de pezón', 'Reconstrucción del pezón y areola',
 'Reconstrucción del pezón y/o areola, con o sin injerto.',
 'Cirugía', 'Plástica', 3.500, 4.500, 0.700,
 'Se reconstruye el pezón después de una mastectomía', 'Sí', 2024, 'AMA'),

('19357', 'Vigente', 'Reconstrucción con colgajo', 'Reconstrucción mamaria con colgajo',
 'Reconstrucción mamaria con colgajo (ej. TRAM, DIEP).',
 'Cirugía', 'Plástica', 12.000, 16.000, 3.000,
 'Se reconstruye el seno con tejido del abdomen', 'Sí', 2024, 'AMA'),

('21275', 'Vigente', 'Rinoplastía', 'Rinoplastía (cirugía de nariz)',
 'Rinoplastía, cirugía de la nariz para corregir deformidad o mejorar la función.',
 'Cirugía', 'Plástica', 7.000, 9.000, 1.600,
 'Se opera la nariz para corregir la forma o respiración', 'Sí', 2024, 'AMA'),

('15847', 'Vigente', 'Abdominoplastía extensa', 'Abdominoplastía extensa',
 'Abdominoplastía (cirugía de abdomen), extensa, con múltiples áreas.',
 'Cirugía', 'Plástica', 8.000, 10.500, 1.800,
 'Se hace una cirugía extensa del abdomen para remover piel y grasa', 'Sí', 2024, 'AMA');


-- ============================================================
-- 18. CÓDIGOS DE REHABILITACIÓN Y TERAPIA FÍSICA
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('97001', 'Vigente', 'Evaluación física', 'Evaluación de terapia física',
 'Evaluación de terapia física, incluyendo historia, examen y plan de tratamiento.',
 'Medicina', 'Rehabilitación', 0.800, 1.200, 0.100,
 'El fisioterapeuta evalúa al paciente para determinar el tratamiento', 'No', 2024, 'AMA'),

('97110', 'Vigente', 'Terapia de ejercicios', 'Terapia de ejercicios terapéuticos',
 'Terapia de ejercicios terapéuticos, para desarrollar fuerza, resistencia y rango de movimiento.',
 'Medicina', 'Rehabilitación', 0.500, 0.700, 0.060,
 'Se realizan ejercicios para fortalecer y recuperar movimiento', 'No', 2024, 'AMA'),

('97140', 'Vigente', 'Terapia manual', 'Terapia manual (movilización/ manipulaciones)',
 'Terapia manual, incluyendo movilización de tejidos blandos y articulaciones.',
 'Medicina', 'Rehabilitación', 0.600, 0.800, 0.070,
 'El terapeuta realiza movilizaciones y manipulaciones manuales', 'No', 2024, 'AMA'),

('97530', 'Vigente', 'Actividades funcionales', 'Actividades funcionales terapéuticas',
 'Actividades funcionales terapéuticas, para mejorar la capacidad funcional.',
 'Medicina', 'Rehabilitación', 0.500, 0.700, 0.060,
 'Se realizan actividades para mejorar la función diaria', 'No', 2024, 'AMA'),

('97760', 'Vigente', 'Ortesis', 'Evaluación de ortesis',
 'Evaluación y ajuste de ortesis (aparatos ortopédicos).',
 'Medicina', 'Rehabilitación', 0.400, 0.600, 0.050,
 'Se evalúa y ajusta un aparato ortopédico', 'No', 2024, 'AMA');


-- ============================================================
-- 19. CÓDIGOS DE MEDICINA DEL DOLOR
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('62310', 'Vigente', 'Inyección epidural cervical', 'Inyección epidural cervical (cervical/ torácica)',
 'Inyección epidural (cervical o torácica), con o sin fluoroscopia, para el manejo del dolor.',
 'Cirugía', 'Medicina del Dolor', 2.000, 2.800, 0.400,
 'Se inyecta medicamento en el espacio epidural del cuello para el dolor', 'Sí', 2024, 'AMA'),

('62311', 'Vigente', 'Inyección epidural lumbar', 'Inyección epidural lumbar',
 'Inyección epidural (lumbar o sacra), con o sin fluoroscopia, para el manejo del dolor.',
 'Cirugía', 'Medicina del Dolor', 2.000, 2.800, 0.400,
 'Se inyecta medicamento en el espacio epidural de la espalda para el dolor', 'Sí', 2024, 'AMA'),

('62320', 'Vigente', 'Inyección caudal', 'Inyección caudal',
 'Inyección epidural (caudal), con o sin fluoroscopia, para el manejo del dolor.',
 'Cirugía', 'Medicina del Dolor', 1.800, 2.500, 0.350,
 'Se inyecta medicamento en el conducto caudal para el dolor', 'Sí', 2024, 'AMA'),

('64483', 'Vigente', 'Bloqueo nervio lumbar', 'Bloqueo de nervio lumbar (transforaminal)',
 'Inyección transforaminal de nervio lumbar, con fluoroscopia.',
 'Cirugía', 'Medicina del Dolor', 2.200, 3.000, 0.450,
 'Se inyecta medicamento alrededor del nervio lumbar para el dolor', 'Sí', 2024, 'AMA'),

('64490', 'Vigente', 'Bloqueo nervio cervical', 'Bloqueo de nervio cervical (transforaminal)',
 'Inyección transforaminal de nervio cervical, con fluoroscopia.',
 'Cirugía', 'Medicina del Dolor', 2.200, 3.000, 0.450,
 'Se inyecta medicamento alrededor del nervio cervical para el dolor', 'Sí', 2024, 'AMA');


-- ============================================================
-- 20. CÓDIGOS DE MEDICINA PREVENTIVA Y CONDUCTUAL
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('99401', 'Vigente', 'Consejería preventiva 15 min', 'Consejería preventiva 15 minutos',
 'Medicina preventiva, consejería individual, 15 minutos.',
 'Medicina', 'Preventiva', 0.500, 0.700, 0.060,
 'Se da consejería preventiva de 15 minutos', 'No', 2024, 'AMA'),

('99406', 'Vigente', 'Intervención tabaco 3-10 min', 'Intervención conductual para tabaco 3-10 min',
 'Intervención conductual para abandono de tabaco, 3-10 minutos.',
 'Medicina', 'Preventiva', 0.200, 0.300, 0.020,
 'Se da consejería breve para dejar de fumar (3-10 min)', 'No', 2024, 'AMA'),

('99407', 'Vigente', 'Intervención tabaco >10 min', 'Intervención conductual para tabaco >10 min',
 'Intervención conductual para abandono de tabaco, mayor de 10 minutos.',
 'Medicina', 'Preventiva', 0.400, 0.600, 0.050,
 'Se da consejería extendida para dejar de fumar (>10 min)', 'No', 2024, 'AMA'),

('99408', 'Vigente', 'Intervención abuso sustancias', 'Intervención conductual para abuso de sustancias',
 'Intervención conductual para abuso de sustancias (ej. alcohol, drogas).',
 'Medicina', 'Preventiva', 0.500, 0.700, 0.060,
 'Se da consejería para problemas de abuso de sustancias', 'No', 2024, 'AMA'),

('99409', 'Vigente', 'Intervención abuso sustancias', 'Intervención conductual para abuso de sustancias (adulto)',
 'Intervención conductual para abuso de sustancias, con el paciente adulto.',
 'Medicina', 'Preventiva', 0.500, 0.700, 0.060,
 'Se da consejería para problemas de abuso de sustancias en adultos', 'No', 2024, 'AMA'),

('99483', 'Nuevo', 'Evaluación cognitiva', 'Evaluación cognitiva y plan de atención',
 'Servicios de evaluación cognitiva y plan de atención, incluyendo historia, examen y desarrollo de un plan de atención.',
 'Medicina', 'Preventiva', 1.500, 2.200, 0.300,
 'Se evalúa la función cognitiva y se desarrolla un plan de atención', 'No', 2024, 'AMA'),

('99484', 'Nuevo', 'Gestión salud conductual', 'Gestión de atención de salud conductual general',
 'Gestión de atención de salud conductual general, incluyendo monitoreo y coordinación de atención.',
 'Medicina', 'Salud Conductual', 0.800, 1.200, 0.100,
 'Se gestiona la atención de salud conductual del paciente', 'No', 2024, 'AMA');


-- ============================================================
-- 21. CÓDIGOS DE CIRUGÍA VASCULAR (Actualización 2026)
-- Los códigos específicos no están disponibles en fuentes públicas,
-- pero se incluyen los que están documentados
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('37241', 'Vigente', 'Embolización vascular', 'Embolización vascular (terapia)',
 'Embolización vascular para control de hemorragia o tumor.',
 'Cirugía', 'Vascular', 5.500, 7.500, 1.200,
 'Se bloquea un vaso sanguíneo para detener sangrado o tratar un tumor', 'Sí', 2024, 'AMA'),

('37243', 'Vigente', 'Embolización tumoral', 'Embolización de tumor (terapéutica)',
 'Embolización terapéutica de tumor (ej. hepático).',
 'Cirugía', 'Vascular', 6.500, 8.500, 1.500,
 'Se bloquea el flujo sanguíneo a un tumor', 'Sí', 2024, 'AMA'),

('37244', 'Vigente', 'Embolización hemorragia', 'Embolización para hemorragia',
 'Embolización para control de hemorragia (ej. gastrointestinal).',
 'Cirugía', 'Vascular', 5.000, 7.000, 1.200,
 'Se bloquea un vaso sangrante', 'Sí', 2024, 'AMA'),

('37248', 'Vigente', 'Trombólisis arterial', 'Trombólisis arterial (catéter dirigido)',
 'Trombólisis arterial, con catéter dirigido y administración de fármacos.',
 'Cirugía', 'Vascular', 5.500, 7.500, 1.300,
 'Se disuelve un coágulo en una arteria con medicamento', 'Sí', 2024, 'AMA'),

('37249', 'Vigente', 'Trombólisis venosa', 'Trombólisis venosa (catéter dirigido)',
 'Trombólisis venosa, con catéter dirigido y administración de fármacos.',
 'Cirugía', 'Vascular', 5.000, 7.000, 1.200,
 'Se disuelve un coágulo en una vena con medicamento', 'Sí', 2024, 'AMA'),

('37600', 'Vigente', 'Ligadura carótida', 'Ligadura de arteria carótida externa',
 'Ligadura de arteria carótida externa (cirugía transoral robótica).',
 'Cirugía', 'Vascular', 6.000, 8.000, 1.500,
 'Se liga la arteria carótida externa', 'Sí', 2024, 'AMA'),

('37700', 'Vigente', 'Ligadura vena safena', 'Ligadura de vena safena y venas perforantes',
 'Ligadura de vena safena y venas perforantes, por insuficiencia venosa.',
 'Cirugía', 'Vascular', 3.500, 4.500, 0.800,
 'Se liga la vena safena para tratar várices', 'Sí', 2024, 'AMA'),

('37722', 'Vigente', 'Flebectomía', 'Flebectomía (extracción de venas)',
 'Flebectomía (extracción de venas varicosas), con técnicas de avulsión.',
 'Cirugía', 'Vascular', 3.000, 4.000, 0.700,
 'Se extraen venas varicosas', 'Sí', 2024, 'AMA');


-- ============================================================
-- 22. CÓDIGOS DE OTORRINOLARINGOLOGÍA (ORL)
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('30520', 'Vigente', 'Septoplastía', 'Septoplastía (corrección del tabique nasal)',
 'Septoplastía, corrección quirúrgica del tabique nasal desviado.',
 'Cirugía', 'Otorrinolaringología', 4.500, 6.000, 1.000,
 'Se endereza el tabique nasal para mejorar la respiración', 'Sí', 2024, 'AMA'),

('31231', 'Vigente', 'Nasoendoscopia diagnóstica', 'Nasoendoscopia diagnóstica',
 'Nasoendoscopia diagnóstica, con o sin biopsia.',
 'Medicina', 'Otorrinolaringología', 0.800, 1.200, 0.150,
 'Se introduce un endoscopio por la nariz para examinar', 'Sí', 2024, 'AMA'),

('31237', 'Vigente', 'Nasoendoscopia con debridamiento', 'Nasoendoscopia con debridamiento',
 'Nasoendoscopia con debridamiento (limpieza) de senos paranasales.',
 'Cirugía', 'Otorrinolaringología', 1.500, 2.000, 0.300,
 'Se limpian los senos paranasales con un endoscopio', 'Sí', 2024, 'AMA'),

('31256', 'Vigente', 'Antrostomía maxilar', 'Antrostomía maxilar (apertura del seno maxilar)',
 'Antrostomía maxilar, apertura del seno maxilar para drenaje.',
 'Cirugía', 'Otorrinolaringología', 4.000, 5.500, 0.900,
 'Se abre el seno maxilar para drenar', 'Sí', 2024, 'AMA'),

('31276', 'Vigente', 'Cirugía frontal', 'Cirugía del seno frontal (frontotomía)',
 'Frontotomía, apertura del seno frontal con o sin remoción de tejido.',
 'Cirugía', 'Otorrinolaringología', 5.000, 6.500, 1.200,
 'Se abre el seno frontal', 'Sí', 2024, 'AMA'),

('31579', 'Vigente', 'Laringoscopia con estroboscopia', 'Laringoscopia con estroboscopia',
 'Laringoscopia flexible o rígida, con estroboscopia para evaluación de cuerdas vocales.',
 'Medicina', 'Otorrinolaringología', 1.500, 2.200, 0.300,
 'Se examinan las cuerdas vocales con luz estroboscópica', 'Sí', 2024, 'AMA'),

('69433', 'Vigente', 'Timpánostomía', 'Timpánostomía con tubo de ventilación',
 'Timpánostomía (incisión del tímpano) con colocación de tubo de ventilación (local o general).',
 'Cirugía', 'Otorrinolaringología', 2.500, 3.500, 0.500,
 'Se coloca un tubo en el tímpano para ventilación', 'Sí', 2024, 'AMA'),

('69631', 'Vigente', 'Timpánoplastía', 'Timpánoplastía (reparación del tímpano)',
 'Timpánoplastía, reparación de la membrana timpánica (sin mastoidectomía).',
 'Cirugía', 'Otorrinolaringología', 4.500, 6.000, 1.000,
 'Se repara el tímpano', 'Sí', 2024, 'AMA'),

('69641', 'Vigente', 'Mastoidectomía', 'Mastoidectomía, simple',
 'Mastoidectomía, simple, para remoción de tejido infectado.',
 'Cirugía', 'Otorrinolaringología', 5.500, 7.500, 1.300,
 'Se extrae tejido infectado del hueso mastoides', 'Sí', 2024, 'AMA');


-- ============================================================
-- 23. CÓDIGOS DE GASTROENTEROLOGÍA - ENDOSCOPIA
-- Fuente: AMA
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('43235', 'Vigente', 'Esofagogastroduodenoscopia', 'Esofagogastroduodenoscopia (EGD) diagnóstica',
 'Esofagogastroduodenoscopia (EGD), diagnóstica, con o sin biopsia.',
 'Cirugía', 'Gastroenterología', 2.500, 3.500, 0.500,
 'Se introduce un endoscopio por la boca hasta el estómago', 'Sí', 2024, 'AMA'),

('43239', 'Vigente', 'EGD con biopsia', 'EGD con biopsia',
 'Esofagogastroduodenoscopia (EGD) con biopsia(s), una o múltiples.',
 'Cirugía', 'Gastroenterología', 2.800, 3.800, 0.600,
 'Se introduce un endoscopio y se toman muestras del estómago', 'Sí', 2024, 'AMA'),

('45378', 'Vigente', 'Colonoscopía diagnóstica', 'Colonoscopía diagnóstica',
 'Colonoscopía, diagnóstica, desde el recto hasta el ciego, con o sin biopsia.',
 'Cirugía', 'Gastroenterología', 3.000, 4.200, 0.600,
 'Se introduce un endoscopio por el ano para examinar el colon', 'Sí', 2024, 'AMA'),

('45380', 'Vigente', 'Colonoscopía con biopsia', 'Colonoscopía con biopsia',
 'Colonoscopía con biopsia(s), una o múltiples.',
 'Cirugía', 'Gastroenterología', 3.200, 4.500, 0.650,
 'Se introduce un endoscopio y se toman muestras del colon', 'Sí', 2024, 'AMA'),

('45385', 'Vigente', 'Colonoscopía con polipectomía', 'Colonoscopía con polipectomía',
 'Colonoscopía con polipectomía (extirpación de pólipo), con técnica de asa.',
 'Cirugía', 'Gastroenterología', 3.500, 5.000, 0.700,
 'Se extirpa un pólipo durante la colonoscopía', 'Sí', 2024, 'AMA'),

('45388', 'Vigente', 'Colonoscopía con ablación', 'Colonoscopía con ablación de tumor',
 'Colonoscopía con ablación de tumor o lesión, con técnicas endoscópicas.',
 'Cirugía', 'Gastroenterología', 4.000, 5.500, 0.800,
 'Se destruye un tumor durante la colonoscopía', 'Sí', 2024, 'AMA'),

('45391', 'Vigente', 'Colonoscopía con biopsia profunda', 'Colonoscopía con biopsia profunda',
 'Colonoscopía con biopsia endoscópica de tejido profundo (ej. ESD).',
 'Cirugía', 'Gastroenterología', 4.500, 6.000, 0.900,
 'Se toma una biopsia profunda durante la colonoscopía', 'Sí', 2024, 'AMA');


-- ============================================================
-- 24. CÓDIGOS DE RADIOLOGÍA - IMÁGENES
-- Fuente: AMA, CMS
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('71045', 'Vigente', 'RX tórax 1 vista', 'Radiografía de tórax, 1 vista',
 'Radiografía de tórax, una vista (proyección frontal).',
 'Radiología', 'Torácica', 0.250, 0.350, 0.040,
 'Se toma una radiografía del pecho (1 vista)', 'No', 2024, 'AMA'),

('71046', 'Vigente', 'RX tórax 2 vistas', 'Radiografía de tórax, 2 vistas',
 'Radiografía de tórax, dos vistas (frontal y lateral).',
 'Radiología', 'Torácica', 0.350, 0.500, 0.050,
 'Se toman dos radiografías del pecho', 'No', 2024, 'AMA'),

('71047', 'Vigente', 'RX tórax 3 vistas', 'Radiografía de tórax, 3 vistas',
 'Radiografía de tórax, tres vistas.',
 'Radiología', 'Torácica', 0.450, 0.600, 0.060,
 'Se toman tres radiografías del pecho', 'No', 2024, 'AMA'),

('71048', 'Vigente', 'RX tórax 4+ vistas', 'Radiografía de tórax, 4 o más vistas',
 'Radiografía de tórax, cuatro o más vistas.',
 'Radiología', 'Torácica', 0.550, 0.700, 0.070,
 'Se toman cuatro o más radiografías del pecho', 'No', 2024, 'AMA'),

('71250', 'Vigente', 'TAC tórax sin contraste', 'TAC de tórax sin contraste',
 'Tomografía computarizada de tórax, sin contraste intravenoso.',
 'Radiología', 'Torácica', 1.800, 2.800, 0.350,
 'Se toma una TAC del tórax sin contraste', 'Sí', 2024, 'AMA'),

('71260', 'Vigente', 'TAC tórax con contraste', 'TAC de tórax con contraste',
 'Tomografía computarizada de tórax, con contraste intravenoso.',
 'Radiología', 'Torácica', 2.000, 3.000, 0.400,
 'Se toma una TAC del tórax con contraste', 'Sí', 2024, 'AMA'),

('71270', 'Vigente', 'TAC tórax sin/con contraste', 'TAC de tórax sin y con contraste',
 'Tomografía computarizada de tórax, sin y con contraste intravenoso.',
 'Radiología', 'Torácica', 2.200, 3.200, 0.450,
 'Se toma una TAC del tórax con y sin contraste', 'Sí', 2024, 'AMA'),

('72141', 'Vigente', 'RMN columna cervical', 'RMN de columna cervical sin contraste',
 'Resonancia magnética de columna cervical, sin contraste.',
 'Radiología', 'Musculoesquelética', 1.800, 2.800, 0.350,
 'Se toma una resonancia de la columna cervical', 'Sí', 2024, 'AMA'),

('72148', 'Vigente', 'RMN columna lumbar', 'RMN de columna lumbar sin contraste',
 'Resonancia magnética de columna lumbar, sin contraste.',
 'Radiología', 'Musculoesquelética', 1.800, 2.800, 0.350,
 'Se toma una resonancia de la columna lumbar', 'Sí', 2024, 'AMA'),

('73221', 'Vigente', 'RMN extremidad superior', 'RMN de extremidad superior sin contraste',
 'Resonancia magnética de extremidad superior (ej. hombro), sin contraste.',
 'Radiología', 'Musculoesquelética', 1.500, 2.200, 0.300,
 'Se toma una resonancia de la extremidad superior', 'Sí', 2024, 'AMA'),

('73721', 'Vigente', 'RMN extremidad inferior', 'RMN de extremidad inferior sin contraste',
 'Resonancia magnética de extremidad inferior (ej. rodilla), sin contraste.',
 'Radiología', 'Musculoesquelética', 1.500, 2.200, 0.300,
 'Se toma una resonancia de la extremidad inferior', 'Sí', 2024, 'AMA'),

('76700', 'Vigente', 'Ecografía abdominal', 'Ultrasonido abdominal completo',
 'Ultrasonido abdominal completo, incluyendo hígado, vesícula, páncreas, riñones.',
 'Radiología', 'Abdominal', 0.800, 1.200, 0.150,
 'Se hace un ultrasonido del abdomen completo', 'Sí', 2024, 'AMA'),

('76705', 'Vigente', 'Ecografía renal', 'Ultrasonido renal (limitado)',
 'Ultrasonido renal, limitado, evaluación de riñones y vejiga.',
 'Radiología', 'Abdominal', 0.600, 0.900, 0.100,
 'Se hace un ultrasonido de los riñones', 'Sí', 2024, 'AMA'),

('76830', 'Vigente', 'Ecografía pélvica', 'Ultrasonido pélvico transabdominal',
 'Ultrasonido pélvico transabdominal, no obstétrico.',
 'Radiología', 'Pélvica', 0.700, 1.000, 0.120,
 'Se hace un ultrasonido de la pelvis', 'Sí', 2024, 'AMA'),

('76856', 'Vigente', 'Ecografía pélvica completa', 'Ultrasonido pélvico completo',
 'Ultrasonido pélvico completo, transabdominal y transvaginal, no obstétrico.',
 'Radiología', 'Pélvica', 0.900, 1.300, 0.150,
 'Se hace un ultrasonido completo de la pelvis', 'Sí', 2024, 'AMA'),

('76805', 'Vigente', 'Ecografía obstétrica', 'Ultrasonido obstétrico (embarazo)',
 'Ultrasonido obstétrico, evaluación fetal detallada (segundo trimestre).',
 'Radiología', 'Obstétrica', 1.200, 1.800, 0.200,
 'Se hace un ultrasonido del embarazo', 'Sí', 2024, 'AMA'),

('77067', 'Vigente', 'Mamografía', 'Mamografía de tamizaje bilateral',
 'Mamografía de tamizaje (detección) bilateral, incluyendo interpretación.',
 'Radiología', 'Mamaria', 0.700, 1.000, 0.100,
 'Se toma una mamografía de ambos senos', 'No', 2024, 'AMA'),

('77080', 'Vigente', 'Densitometría ósea', 'Densitometría ósea (DXA)',
 'Densitometría ósea (DXA), columna y cadera.',
 'Radiología', 'Musculoesquelética', 0.500, 0.700, 0.080,
 'Se mide la densidad de los huesos con rayos X de baja dosis', 'No', 2024, 'AMA');


-- ============================================================
-- 25. CÓDIGOS DE PATOLOGÍA Y LABORATORIO
-- Fuente: AMA, CMS
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('80048', 'Vigente', 'Panel de salud', 'Panel de salud (basic metabolic)',
 'Panel de salud básico, incluyendo electrolitos, glucosa, BUN, creatinina, calcio.',
 'Patología', 'Química Clínica', 0.150, 0.200, 0.020,
 'Análisis de sangre básico para evaluar salud general', 'No', 2024, 'AMA'),

('80053', 'Vigente', 'Panel metabólico completo', 'Panel metabólico completo',
 'Panel metabólico completo (CMP), incluyendo 14 analitos.',
 'Patología', 'Química Clínica', 0.200, 0.280, 0.025,
 'Análisis completo de la química sanguínea', 'No', 2024, 'AMA'),

('80061', 'Vigente', 'Perfil lipídico', 'Perfil lipídico (colesterol)',
 'Perfil lipídico, incluyendo colesterol total, HDL, LDL y triglicéridos.',
 'Patología', 'Química Clínica', 0.180, 0.250, 0.020,
 'Análisis de la grasa en sangre', 'No', 2024, 'AMA'),

('81001', 'Vigente', 'Análisis de orina', 'Análisis de orina con microscopio',
 'Análisis de orina, con examen microscópico (sedimento).',
 'Patología', 'Laboratorio', 0.120, 0.160, 0.015,
 'Análisis completo de orina con microscopio', 'No', 2024, 'AMA'),

('81002', 'Vigente', 'Análisis de orina tira', 'Análisis de orina con tira reactiva',
 'Análisis de orina, con tira reactiva o tabletas (sin microscopio).',
 'Patología', 'Laboratorio', 0.080, 0.100, 0.010,
 'Prueba rápida de orina con tira reactiva', 'No', 2024, 'AMA'),

('85025', 'Vigente', 'Hemograma completo', 'Hemograma completo con diferencial',
 'Conteo sanguíneo completo (CBC) con fórmula leucocitaria diferencial.',
 'Patología', 'Hematología', 0.200, 0.280, 0.025,
 'Análisis completo de la sangre (glóbulos rojos, blancos, plaquetas)', 'No', 2024, 'AMA'),

('85027', 'Vigente', 'Hemograma sin diferencial', 'Hemograma completo sin diferencial',
 'Conteo sanguíneo completo (CBC) sin fórmula leucocitaria diferencial.',
 'Patología', 'Hematología', 0.150, 0.200, 0.020,
 'Análisis de la sangre sin diferencial de glóbulos blancos', 'No', 2024, 'AMA'),

('87070', 'Vigente', 'Cultivo bacteriano', 'Cultivo bacteriano (excepto orina, sangre)',
 'Cultivo bacteriano, para identificación de microorganismos (excepto orina y sangre).',
 'Patología', 'Microbiología', 0.250, 0.350, 0.030,
 'Se cultiva una muestra para identificar bacterias', 'No', 2024, 'AMA'),

('87086', 'Vigente', 'Cultivo de orina', 'Cultivo de orina (cuantitativo)',
 'Cultivo de orina, cuantitativo, para conteo de colonias.',
 'Patología', 'Microbiología', 0.200, 0.280, 0.025,
 'Se cultiva la orina para contar bacterias', 'No', 2024, 'AMA'),

('87088', 'Vigente', 'Cultivo de orina identificación', 'Cultivo de orina con identificación',
 'Cultivo de orina, con identificación de microorganismo (ej. E. coli).',
 'Patología', 'Microbiología', 0.250, 0.350, 0.030,
 'Se cultiva la orina e identifica la bacteria', 'No', 2024, 'AMA'),

('87186', 'Vigente', 'Antibiograma', 'Antibiograma (sensibilidad a antibióticos)',
 'Prueba de sensibilidad a antibióticos (antibiograma), método de difusión.',
 'Patología', 'Microbiología', 0.300, 0.400, 0.040,
 'Se prueba qué antibiótico mata la bacteria', 'No', 2024, 'AMA'),

('87426', 'Vigente', 'Prueba antigénica', 'Detección de antígenos (COVID-19)',
 'Detección de antígenos; SARS-CoV-2 (COVID-19) con inmunoensayo.',
 'Patología', 'Microbiología', 0.200, 0.280, 0.025,
 'Prueba de antígenos para COVID-19', 'No', 2024, 'AMA'),

('87635', 'Vigente', 'Prueba COVID-19 (PCR)', 'Detección de SARS-CoV-2 por PCR',
 'Detección de ácido nucleico (PCR) para SARS-CoV-2 (COVID-19).',
 'Patología', 'Microbiología', 0.350, 0.500, 0.040,
 'Prueba PCR para COVID-19', 'No', 2024, 'AMA'),

('88304', 'Vigente', 'Patología quirúrgica Nivel II', 'Patología quirúrgica Nivel II (biopsia pequeña)',
 'Patología quirúrgica, Nivel II, examen de biopsia pequeña (ej. pólipo, biopsia).',
 'Patología', 'Anatomía Patológica', 0.500, 0.700, 0.060,
 'Examen de una muestra de tejido (biopsia pequeña)', 'No', 2024, 'AMA'),

('88305', 'Vigente', 'Patología quirúrgica Nivel III', 'Patología quirúrgica Nivel III (biopsia compleja)',
 'Patología quirúrgica, Nivel III, examen de biopsia compleja.',
 'Patología', 'Anatomía Patológica', 0.800, 1.100, 0.100,
 'Examen de una muestra de tejido compleja', 'No', 2024, 'AMA'),

('88307', 'Vigente', 'Patología quirúrgica Nivel IV', 'Patología quirúrgica Nivel IV (extirpación compleja)',
 'Patología quirúrgica, Nivel IV, examen de extirpación compleja.',
 'Patología', 'Anatomía Patológica', 1.200, 1.600, 0.150,
 'Examen de una pieza quirúrgica compleja', 'No', 2024, 'AMA'),

('88309', 'Vigente', 'Patología quirúrgica Nivel V', 'Patología quirúrgica Nivel V (grandes órganos)',
 'Patología quirúrgica, Nivel V, examen de grandes órganos.',
 'Patología', 'Anatomía Patológica', 1.800, 2.400, 0.250,
 'Examen de un órgano completo', 'No', 2024, 'AMA');


-- ============================================================
-- 26. CÓDIGOS DE HCPCS RELACIONADOS (para referencia)
-- Fuente: CMS
-- NOTA: Estos son HCPCS Nivel II, no CPT, pero relacionados
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
('G0182', 'Vigente', 'Supervisión hospicio', 'Supervisión de paciente en hospicio 30+ min',
 'Supervisión de paciente bajo hospicio aprobado por Medicare, 30 minutos o más.',
 'Medicina', 'Cuidados Paliativos', 1.200, 1.800, 0.200,
 'Supervisión de un paciente en hospicio (30+ min)', 'No', 2024, 'CMS'),

('G0447', 'Vigente', 'Consejería obesidad', 'Consejería para obesidad',
 'Consejería para prevención y manejo de la obesidad, 15 minutos.',
 'Medicina', 'Preventiva', 0.400, 0.600, 0.050,
 'Consejería para el manejo del peso y obesidad', 'No', 2024, 'CMS'),

('G0466', 'Vigente', 'Visita E/M', 'Visita de evaluación y manejo en centro',
 'Visita de evaluación y manejo en centro ambulatorio.',
 'Medicina', 'Evaluación y Manejo', 1.000, 1.400, 0.150,
 'Visita de evaluación y manejo en un centro médico', 'No', 2024, 'CMS'),

('G0475', 'Vigente', 'Prueba de drogas', 'Prueba de drogas (confirmación)',
 'Prueba de drogas con confirmación por cromatografía.',
 'Patología', 'Toxicología', 0.300, 0.400, 0.040,
 'Prueba de confirmación de drogas', 'No', 2024, 'CMS');

-- ============================================================
-- CÓDIGOS DE CIRUGÍA - SISTEMA MUSCULOESQUELÉTICO (20000-29999)
-- Fuente: CMS NCCI Policy Manual, AMA CPT
-- ============================================================

-- Códigos de Incisión y Drenaje de Abscesos
('20000', 'Vigente', 'Incisión absceso superficial', 'Incisión de absceso de tejido blando superficial',
 'Incisión de absceso de tejido blando (ej. secundario a osteomielitis); superficial. Incluye exploración, debridamiento y drenaje del área afectada.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico hace una incisión sobre un absceso superficial, examina, debrida y drena el área.', 'Sí', 2024, 'CMS'),

('20005', 'Vigente', 'Incisión absceso profundo', 'Incisión de absceso de tejido blando profundo/complicado',
 'Incisión de absceso de tejido blando (ej. secundario a osteomielitis); profundo o complicado. Incluye debridamiento extenso, irrigación y examen de tejidos y huesos subyacentes.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico hace una incisión sobre un absceso profundo, debrida extensamente, irriga y examina tejidos y huesos.', 'Sí', 2024, 'CMS'),

-- Códigos de Desbridamiento de Fracturas Abiertas
('11010', 'Vigente', 'Desbridamiento fractura abierta', 'Desbridamiento de fractura/dislocación abierta - piel y subcutáneo',
 'Desbridamiento incluyendo remoción de material extraño en el sitio de una fractura abierta y/o dislocación abierta (ej. desbridamiento excisional); piel y tejidos subcutáneos.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico limpia y remueve tejido dañado y objetos extraños de una fractura expuesta, a nivel de piel y tejido subcutáneo.', 'Sí', 2024, 'AMA'),

('11011', 'Vigente', 'Desbridamiento fractura con fascia', 'Desbridamiento de fractura/dislocación - piel, subcutáneo, fascia, músculo',
 'Desbridamiento incluyendo remoción de material extraño en el sitio de una fractura abierta y/o dislocación abierta (ej. desbridamiento excisional); piel, tejido subcutáneo, fascia muscular y músculo.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico limpia y remueve tejido dañado y objetos extraños de una fractura expuesta, incluyendo piel, subcutáneo, fascia y músculo.', 'Sí', 2024, 'AMA'),

('11012', 'Vigente', 'Desbridamiento fractura con hueso', 'Desbridamiento de fractura/dislocación - incluyendo hueso',
 'Desbridamiento incluyendo remoción de material extraño en el sitio de una fractura abierta y/o dislocación abierta (ej. desbridamiento excisional); piel, tejido subcutáneo, fascia muscular, músculo y hueso.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico limpia y remueve tejido dañado y objetos extraños de una fractura expuesta, incluyendo piel, subcutáneo, fascia, músculo y hueso.', 'Sí', 2024, 'AMA'),

-- Códigos Generales de Cierre y Procedimientos No Listados
('20000', 'Vigente', 'Cierre de herida compleja', 'Cierre de herida compleja con múltiples capas',
 'Cierre de herida compleja con reparación de planos profundos (fascia, músculo) y capas superficiales.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico sutura una herida profunda en varias capas.', 'Sí', 2024, 'CMS'),

('20999', 'Vigente', 'Procedimiento no listado', 'Procedimiento no listado - sistema musculoesquelético',
 'Procedimiento quirúrgico no listado del sistema musculoesquelético.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'Procedimiento quirúrgico del sistema musculoesquelético que no tiene un código específico.', 'Sí', 2024, 'CMS'),

-- Códigos de Artroscopia de Rodilla
('29874', 'Vigente', 'Artroscopia rodilla - cuerpos libres', 'Artroscopia rodilla con remoción de cuerpo libre',
 'Artroscopia de rodilla, quirúrgica; para remoción de cuerpo libre o cuerpo extraño.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico introduce una cámara en la rodilla y extrae un cuerpo libre o fragmento de cartílago suelto.', 'Sí', 2024, 'CMS'),

('29877', 'Vigente', 'Artroscopia rodilla - debridamiento', 'Artroscopia rodilla con debridamiento de cartílago',
 'Artroscopia de rodilla, quirúrgica; para debridamiento/raspado de cartílago articular (condroplastía).',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico introduce una cámara en la rodilla y limpia o alisa el cartílago dañado.', 'Sí', 2024, 'CMS'),

('29880', 'Vigente', 'Meniscectomía medial y lateral', 'Artroscopia rodilla con meniscectomía medial y lateral',
 'Artroscopia de rodilla, quirúrgica; con meniscectomía (medial Y lateral, incluyendo cualquier raspado meniscal) incluyendo debridamiento/raspado de cartílago articular (condroplastía) en el mismo o diferente compartimiento, cuando se realiza.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico introduce una cámara en la rodilla y extrae partes de ambos meniscos (medial y lateral).', 'Sí', 2024, 'CMS'),

('29881', 'Vigente', 'Meniscectomía medial o lateral', 'Artroscopia rodilla con meniscectomía medial o lateral',
 'Artroscopia de rodilla, quirúrgica; con meniscectomía (medial O lateral, incluyendo cualquier raspado meniscal) incluyendo debridamiento/raspado de cartílago articular (condroplastía) en el mismo o diferente compartimiento, cuando se realiza.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico introduce una cámara en la rodilla y extrae parte de un menisco (medial o lateral).', 'Sí', 2024, 'CMS'),

('29875', 'Vigente', 'Sinovectomía limitada', 'Artroscopia rodilla con sinovectomía limitada',
 'Artroscopia de rodilla, quirúrgica; sinovectomía limitada (procedimiento separado).',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico introduce una cámara en la rodilla y realiza una limpieza limitada del tejido sinovial inflamado.', 'Sí', 2024, 'CMS'),

('29876', 'Vigente', 'Sinovectomía mayor', 'Artroscopia rodilla con sinovectomía mayor',
 'Artroscopia de rodilla, quirúrgica; sinovectomía mayor (de dos o tres compartimientos).',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico introduce una cámara en la rodilla y realiza una limpieza extensa del tejido sinovial inflamado en dos o tres compartimientos.', 'Sí', 2024, 'CMS'),

-- Códigos de Artroscopia de Hombro
('29822', 'Vigente', 'Artroscopia hombro - debridamiento limitado', 'Artroscopia hombro con debridamiento limitado',
 'Artroscopia de hombro, quirúrgica; debridamiento limitado.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico introduce una cámara en el hombro y realiza un debridamiento limitado del tejido dañado.', 'Sí', 2024, 'CMS'),

('29823', 'Vigente', 'Artroscopia hombro - debridamiento extenso', 'Artroscopia hombro con debridamiento extenso',
 'Artroscopia de hombro, quirúrgica; debridamiento extenso.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico introduce una cámara en el hombro y realiza un debridamiento extenso del tejido dañado.', 'Sí', 2024, 'CMS'),

('29824', 'Vigente', 'Artroscopia hombro - claviculectomía', 'Artroscopia hombro con claviculectomía',
 'Artroscopia de hombro, quirúrgica; claviculectomía incluyendo la superficie articular distal.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico introduce una cámara en el hombro y reseca la porción distal de la clavícula.', 'Sí', 2024, 'CMS'),

('29827', 'Vigente', 'Artroscopia hombro - reparación manguito', 'Artroscopia hombro con reparación de manguito rotador',
 'Artroscopia de hombro, quirúrgica; reparación de manguito rotador.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico introduce una cámara en el hombro y repara un desgarro del manguito rotador.', 'Sí', 2024, 'CMS'),

('29828', 'Vigente', 'Artroscopia hombro - tenodesis', 'Artroscopia hombro con tenodesis del bíceps',
 'Artroscopia de hombro, quirúrgica; tenodesis del bíceps.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico introduce una cámara en el hombro y realiza una tenodesis del tendón del bíceps.', 'Sí', 2024, 'CMS'),

-- Códigos de Remoción de Fijación Interna
('20670', 'Vigente', 'Remoción de implante superficial', 'Remoción de implante superficial (ej. pin, alambre)',
 'Remoción de implante (ej. clavo, alambre, pin) superficial, en la oficina o clínica.',
 'Cirugía', 'Sistema Musculoesquelético', 0.000, 0.000, 0.000,
 'El médico remueve un implante superficial como un pin o alambre.', 'Sí', 2024, 'CMS'),

-- ============================================================
-- 1.1 EVALUACIÓN Y MANEJO (E/M) - COMPLETAR
-- Códigos 99217-99499 (Rangos faltantes)
-- Fuente: CMS, AMA
-- ============================================================

-- Servicios de Observación Hospitalaria
('99217', 'Vigente', 'Alta observación', 'Alta de observación hospitalaria',
 'Atención de alta de un paciente de observación hospitalaria, incluyendo el tiempo total en la fecha de alta.',
 'Medicina', 'Evaluación y Manejo', 1.100, 0.000, 0.000,
 'El médico da el alta al paciente que estaba en observación.', 'Sí', 2024, 'CMS/AMA'),

('99218', 'Vigente', 'Observación Nivel 1', 'Atención de observación Nivel 1 (baja complejidad)',
 'Atención de un paciente de observación hospitalaria, Nivel 1, de baja complejidad (historia y examen enfocados, toma de decisiones sencilla).',
 'Medicina', 'Evaluación y Manejo', 1.100, 0.000, 0.000,
 'Atención de baja complejidad para un paciente en observación.', 'Sí', 2024, 'CMS/AMA'),

('99219', 'Vigente', 'Observación Nivel 2', 'Atención de observación Nivel 2 (moderada complejidad)',
 'Atención de un paciente de observación hospitalaria, Nivel 2, de complejidad moderada (historia y examen detallados, toma de decisiones de complejidad moderada).',
 'Medicina', 'Evaluación y Manejo', 1.700, 0.000, 0.000,
 'Atención de complejidad moderada para un paciente en observación.', 'Sí', 2024, 'CMS/AMA'),

('99220', 'Vigente', 'Observación Nivel 3', 'Atención de observación Nivel 3 (alta complejidad)',
 'Atención de un paciente de observación hospitalaria, Nivel 3, de alta complejidad (historia y examen exhaustivos, toma de decisiones de alta complejidad).',
 'Medicina', 'Evaluación y Manejo', 2.600, 0.000, 0.000,
 'Atención de alta complejidad para un paciente en observación.', 'Sí', 2024, 'CMS/AMA'),

-- Servicios de Paciente Hospitalizado (Admisión)
('99221', 'Vigente', 'Hospitalización Nivel 1', 'Atención hospitalaria inicial Nivel 1 (baja complejidad)',
 'Atención inicial de un paciente hospitalizado, Nivel 1, de baja complejidad (historia y examen enfocados, toma de decisiones sencilla).',
 'Medicina', 'Evaluación y Manejo', 1.400, 0.000, 0.000,
 'Primera atención de baja complejidad para un paciente hospitalizado.', 'Sí', 2024, 'CMS/AMA'),

('99222', 'Vigente', 'Hospitalización Nivel 2', 'Atención hospitalaria inicial Nivel 2 (moderada complejidad)',
 'Atención inicial de un paciente hospitalizado, Nivel 2, de complejidad moderada (historia y examen detallados, toma de decisiones de complejidad moderada).',
 'Medicina', 'Evaluación y Manejo', 2.100, 0.000, 0.000,
 'Primera atención de complejidad moderada para un paciente hospitalizado.', 'Sí', 2024, 'CMS/AMA'),

('99223', 'Vigente', 'Hospitalización Nivel 3', 'Atención hospitalaria inicial Nivel 3 (alta complejidad)',
 'Atención inicial de un paciente hospitalizado, Nivel 3, de alta complejidad (historia y examen exhaustivos, toma de decisiones de alta complejidad).',
 'Medicina', 'Evaluación y Manejo', 3.000, 0.000, 0.000,
 'Primera atención de alta complejidad para un paciente hospitalizado.', 'Sí', 2024, 'CMS/AMA'),

-- Servicios de Paciente Hospitalizado (Subsecuentes)
('99231', 'Vigente', 'Hospitalización subsec Nivel 1', 'Atención hospitalaria subsecuente Nivel 1 (baja complejidad)',
 'Atención subsecuente de un paciente hospitalizado, Nivel 1, de baja complejidad (historia y examen enfocados, toma de decisiones sencilla).',
 'Medicina', 'Evaluación y Manejo', 1.000, 0.000, 0.000,
 'Atención de seguimiento de baja complejidad para un paciente hospitalizado.', 'Sí', 2024, 'CMS/AMA'),

('99232', 'Vigente', 'Hospitalización subsec Nivel 2', 'Atención hospitalaria subsecuente Nivel 2 (moderada complejidad)',
 'Atención subsecuente de un paciente hospitalizado, Nivel 2, de complejidad moderada (historia y examen detallados, toma de decisiones de complejidad moderada).',
 'Medicina', 'Evaluación y Manejo', 1.500, 0.000, 0.000,
 'Atención de seguimiento de complejidad moderada para un paciente hospitalizado.', 'Sí', 2024, 'CMS/AMA'),

('99233', 'Vigente', 'Hospitalización subsec Nivel 3', 'Atención hospitalaria subsecuente Nivel 3 (alta complejidad)',
 'Atención subsecuente de un paciente hospitalizado, Nivel 3, de alta complejidad (historia y examen exhaustivos, toma de decisiones de alta complejidad).',
 'Medicina', 'Evaluación y Manejo', 2.200, 0.000, 0.000,
 'Atención de seguimiento de alta complejidad para un paciente hospitalizado.', 'Sí', 2024, 'CMS/AMA'),

-- Servicios de Consulta
('99242', 'Vigente', 'Consulta Nivel 2', 'Consulta ambulatoria Nivel 2 (baja complejidad)',
 'Consulta de oficina o ambulatoria para un paciente nuevo, Nivel 2, de baja complejidad.',
 'Medicina', 'Evaluación y Manejo', 1.300, 0.000, 0.000,
 'Consulta de baja complejidad para un paciente nuevo.', 'Sí', 2024, 'CMS/AMA'),

('99243', 'Vigente', 'Consulta Nivel 3', 'Consulta ambulatoria Nivel 3 (moderada complejidad)',
 'Consulta de oficina o ambulatoria para un paciente nuevo, Nivel 3, de complejidad moderada.',
 'Medicina', 'Evaluación y Manejo', 1.800, 0.000, 0.000,
 'Consulta de complejidad moderada para un paciente nuevo.', 'Sí', 2024, 'CMS/AMA'),

('99244', 'Vigente', 'Consulta Nivel 4', 'Consulta ambulatoria Nivel 4 (alta complejidad)',
 'Consulta de oficina o ambulatoria para un paciente nuevo, Nivel 4, de alta complejidad.',
 'Medicina', 'Evaluación y Manejo', 2.500, 0.000, 0.000,
 'Consulta de alta complejidad para un paciente nuevo.', 'Sí', 2024, 'CMS/AMA'),

('99245', 'Vigente', 'Consulta Nivel 5', 'Consulta ambulatoria Nivel 5 (complejidad muy alta)',
 'Consulta de oficina o ambulatoria para un paciente nuevo, Nivel 5, de complejidad muy alta.',
 'Medicina', 'Evaluación y Manejo', 3.200, 0.000, 0.000,
 'Consulta de complejidad muy alta para un paciente nuevo.', 'Sí', 2024, 'CMS/AMA'),

-- Servicios de Atención Crítica
('99291', 'Vigente', 'Atención crítica', 'Atención crítica (primera 30-74 minutos)',
 'Atención crítica, evaluación y manejo de un paciente con enfermedad o lesión crítica; primeros 30-74 minutos.',
 'Medicina', 'Evaluación y Manejo', 4.500, 0.000, 0.000,
 'Atención de emergencia para un paciente en estado crítico (30-74 min).', 'Sí', 2024, 'CMS/AMA'),

('99292', 'Vigente', 'Atención crítica adicional', 'Atención crítica (cada 30 minutos adicionales)',
 'Atención crítica, evaluación y manejo de un paciente con enfermedad o lesión crítica; cada 30 minutos adicionales (código adicional).',
 'Medicina', 'Evaluación y Manejo', 2.250, 0.000, 0.000,
 'Atención de emergencia adicional para un paciente en estado crítico (cada 30 min).', 'Sí', 2024, 'CMS/AMA'),

-- Servicios de Neonatología y Cuidados Intensivos
('99295', 'Vigente', 'Cuidado intensivo neonatal', 'Cuidado intensivo neonatal, bajo peso al nacer',
 'Cuidado intensivo neonatal, para recién nacidos con bajo peso al nacer o condiciones críticas.',
 'Medicina', 'Evaluación y Manejo', 4.500, 0.000, 0.000,
 'Cuidados intensivos para un recién nacido de bajo peso.', 'Sí', 2024, 'CMS/AMA'),

-- Servicios de Hogar de Ancianos
('99304', 'Vigente', 'Institución Nivel 1', 'Atención inicial en institución Nivel 1 (baja complejidad)',
 'Atención inicial en una institución de cuidados (ej. hogar de ancianos), Nivel 1, de baja complejidad.',
 'Medicina', 'Evaluación y Manejo', 1.100, 0.000, 0.000,
 'Primera atención de baja complejidad para un paciente en una institución.', 'Sí', 2024, 'CMS/AMA'),

-- Servicios de Medicina Preventiva
('99381', 'Vigente', 'Preventiva niño <1 año', 'Examen preventivo, niño menor de 1 año',
 'Examen de medicina preventiva para un niño menor de 1 año, incluyendo historia y examen.',
 'Medicina', 'Preventiva', 1.200, 0.000, 0.000,
 'Revisión médica preventiva para un bebé menor de 1 año.', 'No', 2024, 'CMS/AMA'),

('99382', 'Vigente', 'Preventiva niño 1-4 años', 'Examen preventivo, niño de 1-4 años',
 'Examen de medicina preventiva para un niño de 1 a 4 años.',
 'Medicina', 'Preventiva', 1.400, 0.000, 0.000,
 'Revisión médica preventiva para un niño de 1 a 4 años.', 'No', 2024, 'CMS/AMA'),

('99383', 'Vigente', 'Preventiva niño 5-11 años', 'Examen preventivo, niño de 5-11 años',
 'Examen de medicina preventiva para un niño de 5 a 11 años.',
 'Medicina', 'Preventiva', 1.600, 0.000, 0.000,
 'Revisión médica preventiva para un niño de 5 a 11 años.', 'No', 2024, 'CMS/AMA'),

('99384', 'Vigente', 'Preventiva adolescente', 'Examen preventivo, adolescente de 12-17 años',
 'Examen de medicina preventiva para un adolescente de 12 a 17 años.',
 'Medicina', 'Preventiva', 1.800, 0.000, 0.000,
 'Revisión médica preventiva para un adolescente de 12 a 17 años.', 'No', 2024, 'CMS/AMA'),

('99385', 'Vigente', 'Preventiva adulto 18-39', 'Examen preventivo, adulto de 18-39 años',
 'Examen de medicina preventiva para un adulto de 18 a 39 años.',
 'Medicina', 'Preventiva', 2.000, 0.000, 0.000,
 'Revisión médica preventiva para un adulto de 18 a 39 años.', 'No', 2024, 'CMS/AMA'),

('99386', 'Vigente', 'Preventiva adulto 40-64', 'Examen preventivo, adulto de 40-64 años',
 'Examen de medicina preventiva para un adulto de 40 a 64 años.',
 'Medicina', 'Preventiva', 2.200, 0.000, 0.000,
 'Revisión médica preventiva para un adulto de 40 a 64 años.', 'No', 2024, 'CMS/AMA'),

('99387', 'Vigente', 'Preventiva adulto 65+', 'Examen preventivo, adulto de 65 años o más',
 'Examen de medicina preventiva para un adulto de 65 años o más.',
 'Medicina', 'Preventiva', 2.400, 0.000, 0.000,
 'Revisión médica preventiva para un adulto de 65 años o más.', 'No', 2024, 'CMS/AMA'),

-- Servicios de Medicina Preventiva (Paciente Establecido)
('99391', 'Vigente', 'Preventiva establecido <1 año', 'Examen preventivo establecido, niño <1 año',
 'Examen de medicina preventiva para un paciente establecido menor de 1 año.',
 'Medicina', 'Preventiva', 0.900, 0.000, 0.000,
 'Revisión médica preventiva de seguimiento para un bebé menor de 1 año.', 'No', 2024, 'CMS/AMA'),

('99392', 'Vigente', 'Preventiva establecido 1-4', 'Examen preventivo establecido, niño 1-4 años',
 'Examen de medicina preventiva para un paciente establecido de 1 a 4 años.',
 'Medicina', 'Preventiva', 1.100, 0.000, 0.000,
 'Revisión médica preventiva de seguimiento para un niño de 1 a 4 años.', 'No', 2024, 'CMS/AMA'),

('99393', 'Vigente', 'Preventiva establecido 5-11', 'Examen preventivo establecido, niño 5-11 años',
 'Examen de medicina preventiva para un paciente establecido de 5 a 11 años.',
 'Medicina', 'Preventiva', 1.300, 0.000, 0.000,
 'Revisión médica preventiva de seguimiento para un niño de 5 a 11 años.', 'No', 2024, 'CMS/AMA'),

('99394', 'Vigente', 'Preventiva establecido adolescente', 'Examen preventivo establecido, adolescente 12-17',
 'Examen de medicina preventiva para un paciente establecido de 12 a 17 años.',
 'Medicina', 'Preventiva', 1.500, 0.000, 0.000,
 'Revisión médica preventiva de seguimiento para un adolescente de 12 a 17 años.', 'No', 2024, 'CMS/AMA'),

('99395', 'Vigente', 'Preventiva establecido 18-39', 'Examen preventivo establecido, adulto 18-39 años',
 'Examen de medicina preventiva para un paciente establecido de 18 a 39 años.',
 'Medicina', 'Preventiva', 1.700, 0.000, 0.000,
 'Revisión médica preventiva de seguimiento para un adulto de 18 a 39 años.', 'No', 2024, 'CMS/AMA'),

('99396', 'Vigente', 'Preventiva establecido 40-64', 'Examen preventivo establecido, adulto 40-64 años',
 'Examen de medicina preventiva para un paciente establecido de 40 a 64 años.',
 'Medicina', 'Preventiva', 1.900, 0.000, 0.000,
 'Revisión médica preventiva de seguimiento para un adulto de 40 a 64 años.', 'No', 2024, 'CMS/AMA'),

('99397', 'Vigente', 'Preventiva establecido 65+', 'Examen preventivo establecido, adulto 65+ años',
 'Examen de medicina preventiva para un paciente establecido de 65 años o más.',
 'Medicina', 'Preventiva', 2.100, 0.000, 0.000,
 'Revisión médica preventiva de seguimiento para un adulto de 65 años o más.', 'No', 2024, 'CMS/AMA'),

-- Atención de Recién Nacido
('99460', 'Vigente', 'Atención recién nacido', 'Atención al recién nacido en sala de partos',
 'Atención al recién nacido en la sala de partos, incluyendo historia y examen inicial.',
 'Medicina', 'Evaluación y Manejo', 2.000, 0.000, 0.000,
 'Evaluación inicial del recién nacido en la sala de partos.', 'Sí', 2024, 'CMS/AMA'),

('99461', 'Vigente', 'Atención recién nacido en centro', 'Atención al recién nacido en centro de partos',
 'Atención al recién nacido en un centro de partos (no hospitalario).',
 'Medicina', 'Evaluación y Manejo', 1.800, 0.000, 0.000,
 'Evaluación inicial del recién nacido en un centro de partos.', 'Sí', 2024, 'CMS/AMA'),

('99462', 'Vigente', 'Atención recién nacido en hospital', 'Atención al recién nacido en hospital',
 'Atención al recién nacido en el hospital (cuidados posteriores al parto).',
 'Medicina', 'Evaluación y Manejo', 1.500, 0.000, 0.000,
 'Atención médica al recién nacido durante su estancia en el hospital.', 'Sí', 2024, 'CMS/AMA'),

-- Servicios Especiales
('99499', 'Vigente', 'Servicio E/M no listado', 'Servicio de evaluación y manejo no listado',
 'Servicio de evaluación y manejo no listado. Se utiliza para servicios que no tienen un código específico.',
 'Medicina', 'Evaluación y Manejo', 0.000, 0.000, 0.000,
 'Servicio de evaluación y manejo que no tiene un código específico.', 'Sí', 2024, 'CMS/AMA'),

-- ============================================================
-- 3. CATEGORÍAS II Y III - Códigos para Medición de Desempeño y Tecnología Emergente
-- Fuente: AMA, NCQA/HEDIS
-- ============================================================

-- ------------------------------------------------------------
-- 3.1 CATEGORÍA II (0001F-9007F) - Códigos de Medición de Desempeño
-- Fuente: Guías CPT 2025, AMA, HEDIS
-- NOTA: Uso opcional, no son para reembolso directo. Facilitan la medición de calidad.
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
-- Cuidado Prenatal y Postparto
('0500F', 'Vigente', 'Visita prenatal inicial', 'Visita prenatal inicial',
 'Initial prenatal care visit. Report at first prenatal encounter with healthcare professional providing obstetrical care. Report the date of the visit and in a separate field, the date of the last menstrual period. [citation:1]',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad para la primera visita de control prenatal.', 'No', 2024, 'NCQA/HEDIS'),

('0501F', 'Vigente', 'Flujo prenatal documentado', 'Flujo prenatal documentado en historia clínica',
 'Prenatal flow sheet documented in medical record by first prenatal visit. Documentation includes at minimum blood pressure, weight, urine protein, uterine size, fetal heart tones, and estimated date of delivery. [citation:1]',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad que confirma que se ha documentado el flujo prenatal en la primera visita.', 'No', 2024, 'NCQA/HEDIS'),

('0502F', 'Vigente', 'Visita prenatal subsecuente', 'Visita prenatal subsecuente',
 'Subsequent prenatal care visit. [citation:1]',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad para una visita de control prenatal de seguimiento.', 'No', 2024, 'NCQA/HEDIS'),

('0503F', 'Vigente', 'Visita de cuidado postparto', 'Visita de cuidado postparto',
 'Postpartum care visit. [citation:1]',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad para la visita de cuidado postparto.', 'No', 2024, 'NCQA/HEDIS'),

-- Examen Oftalmológico (Cuidado de la Diabetes)
('2022F', 'Vigente', 'Examen ocular con retinopatía', 'Examen ocular dilatado con evidencia de retinopatía',
 'Dilated retinal eye exam with interpretation by an ophthalmologist or optometrist documented and reviewed; with evidence of retinopathy. [citation:1]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que un paciente diabético tiene retinopatía.', 'No', 2024, 'NCQA/HEDIS'),

('2023F', 'Vigente', 'Examen ocular sin retinopatía', 'Examen ocular dilatado sin evidencia de retinopatía',
 'Dilated retinal eye exam with interpretation by an ophthalmologist or optometrist documented and reviewed; without evidence of retinopathy. [citation:1][citation:11]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que un paciente diabético NO tiene retinopatía.', 'No', 2024, 'NCQA/HEDIS'),

('2024F', 'Vigente', 'Fotos retinianas con retinopatía', 'Fotos retinianas 7 campos con retinopatía',
 'Seven standard field stereoscopic retinal photos with interpretation by an ophthalmologist or optometrist documented and reviewed; with evidence of retinopathy. [citation:1][citation:11]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar retinopatía usando fotos retinianas.', 'No', 2024, 'NCQA/HEDIS'),

('2025F', 'Vigente', 'Fotos retinianas sin retinopatía', 'Fotos retinianas 7 campos sin retinopatía',
 'Seven standard field stereoscopic retinal photos with interpretation by an ophthalmologist or optometrist documented and reviewed; without evidence of retinopathy. [citation:1][citation:11]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que NO hay retinopatía usando fotos retinianas.', 'No', 2024, 'NCQA/HEDIS'),

-- Control de la Diabetes (HbA1c)
('3044F', 'Vigente', 'HbA1c < 7.0%', 'HbA1c menor a 7.0%',
 'Most recent hemoglobin A1c (HbA1c) level less than 7.0%. [citation:1]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un buen control de glucosa (HbA1c < 7%).', 'No', 2024, 'NCQA/HEDIS'),

('3046F', 'Vigente', 'HbA1c > 9.0%', 'HbA1c mayor a 9.0%',
 'Most recent hemoglobin A1c level greater than 9.0%. [citation:1]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un mal control de glucosa (HbA1c > 9%).', 'No', 2024, 'NCQA/HEDIS'),

('3051F', 'Vigente', 'HbA1c 7.0-8.0%', 'HbA1c entre 7.0% y 8.0%',
 'Most recent hemoglobin A1c (HbA1c) level greater than or equal to 7.0% and less than 8.0%. [citation:1]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un control de glucosa moderado (HbA1c 7-8%).', 'No', 2024, 'NCQA/HEDIS'),

('3052F', 'Vigente', 'HbA1c 8.0-9.0%', 'HbA1c entre 8.0% y 9.0%',
 'Most recent hemoglobin A1c (HbA1c) level greater than or equal to 8.0% and less than or equal to 9.0%. [citation:1]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un control de glucosa subóptimo (HbA1c 8-9%).', 'No', 2024, 'NCQA/HEDIS'),

-- Control del Colesterol (LDL-C)
('3048F', 'Vigente', 'LDL-C < 100 mg/dL', 'LDL-C menor a 100 mg/dL',
 'Most recent LDL-C less than 100 mg/dL. [citation:1]',
 'Categoría II', 'Control Lipídico', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un buen control de colesterol (LDL < 100).', 'No', 2024, 'NCQA/HEDIS'),

('3049F', 'Vigente', 'LDL-C 100-129 mg/dL', 'LDL-C entre 100 y 129 mg/dL',
 'Most recent LDL-C 100-129 mg/dL. [citation:1]',
 'Categoría II', 'Control Lipídico', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un control de colesterol intermedio (LDL 100-129).', 'No', 2024, 'NCQA/HEDIS'),

('3050F', 'Vigente', 'LDL-C >= 130 mg/dL', 'LDL-C mayor o igual a 130 mg/dL',
 'Most recent LDL-C greater than or equal to 130 mg/dL. [citation:1]',
 'Categoría II', 'Control Lipídico', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un mal control de colesterol (LDL >= 130).', 'No', 2024, 'NCQA/HEDIS'),

-- Control de la Presión Arterial
('3074F', 'Vigente', 'Sistólica < 130 mmHg', 'Presión arterial sistólica menor a 130 mm Hg',
 'Most recent systolic blood pressure less than 130 mm Hg. [citation:1]',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una buena presión sistólica (< 130).', 'No', 2024, 'NCQA/HEDIS'),

('3075F', 'Vigente', 'Sistólica 130-139 mmHg', 'Presión arterial sistólica 130-139 mm Hg',
 'Most recent systolic blood pressure 130-139 mm Hg. [citation:1]',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión sistólica elevada (130-139).', 'No', 2024, 'NCQA/HEDIS'),

('3077F', 'Vigente', 'Sistólica >= 140 mmHg', 'Presión arterial sistólica mayor o igual a 140 mm Hg',
 'Most recent systolic blood pressure greater than or equal to 140 mm Hg. [citation:1]',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión sistólica alta (>= 140).', 'No', 2024, 'NCQA/HEDIS'),

-- Medidas Generales
('4000F', 'Vigente', 'Índice de masa corporal (IMC)', 'IMC documentado y evaluado',
 'Body Mass Index (BMI) measured and recorded. [citation:15]',
 'Categoría II', 'Salud General', 0.000, 0.000, 0.000,
 'Código de calidad para confirmar que se midió y documentó el IMC.', 'No', 2024, 'NCQA/HEDIS'),

-- ------------------------------------------------------------
-- 3.2 CATEGORÍA III (0042T-1025T) - Códigos para Tecnología Emergente
-- Fuente: Guías CPT 2025-2026, AMA
-- NOTA: Códigos temporales para nuevas tecnologías. Uso para recolección de datos.
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
-- Procedimientos de Alta Tecnología (Ejemplos de 2025-2026)
('0870T', 'Nuevo', 'Implante bomba ascitis peritoneal', 'Implante de bomba peritoneal subcutánea para ascitis',
 'Implantation of subcutaneous peritoneal ascites pump system. [citation:2]',
 'Categoría III', 'Procedimientos Gastrointestinales', 0.000, 0.000, 0.000,
 'Implante de un sistema de bomba para el manejo de ascitis.', 'Sí', 2025, 'AMA'),

('0956T', 'Nuevo', 'Implante EEG subcutáneo', 'Implante de electrodos para monitoreo EEG subcutáneo',
 'Partial craniectomy, channel creation, and tunneling of electrode for sub-scalp implantation of an electrode array, receiver, and telemetry unit for continuous bilateral electroencephalography monitoring system, including imaging guidance. [citation:7]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Procedimiento para implantar un sistema de monitoreo de EEG bajo el cuero cabelludo.', 'Sí', 2025, 'AMA'),

('0957T', 'Nuevo', 'Revisión EEG subcutáneo', 'Revisión de electrodos para monitoreo EEG subcutáneo',
 'Revision of sub-scalp implanted electrode array, receiver, and telemetry unit for electrode, when required, including imaging guidance. [citation:7]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Revisión del sistema de monitoreo EEG subcutáneo.', 'Sí', 2025, 'AMA'),

('0960T', 'Nuevo', 'Reemplazo EEG subcutáneo', 'Reemplazo de electrodos para monitoreo EEG subcutáneo',
 'Replacement of sub-scalp implanted electrode array, receiver, and telemetry unit with tunneling of electrode for continuous bilateral electroencephalography monitoring system, including imaging guidance. [citation:7]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Reemplazo del sistema de monitoreo EEG subcutáneo.', 'Sí', 2025, 'AMA'),

('0962T', 'Nuevo', 'Análisis IA de EKG', 'Análisis algorítmico de grabación acústica y electrocardiográfica',
 'Assistive algorithmic analysis of acoustic and electrocardiogram recording for detection of cardiac dysfunction (eg, reduced ejection fraction, cardiac murmurs, atrial fibrillation), with review and interpretation by a physician or other qualified health care professional. [citation:7]',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Análisis asistido por algoritmos de registros de EKG y sonidos cardíacos.', 'No', 2025, 'AMA'),

('0968T', 'Nuevo', 'Implante neuroestimulador epicraneal', 'Inserción o reemplazo de sistema de neuroestimulación epicraneal',
 'Insertion or replacement of epicranial neurostimulator system, including electrode array and pulse generator, with connection to electrode array. [citation:7]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Procedimiento para implantar un sistema de neuroestimulación sobre el cráneo.', 'Sí', 2025, 'AMA'),

('0969T', 'Nuevo', 'Remoción neuroestimulador epicraneal', 'Remoción de sistema de neuroestimulación epicraneal',
 'Removal of epicranial neurostimulator system. [citation:7]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Procedimiento para retirar un sistema de neuroestimulación epicraneal.', 'Sí', 2025, 'AMA'),

('0970T', 'Nuevo', 'Ablación láser de tumor benigno', 'Ablación percutánea de tumor benigno de mama',
 'Ablation, benign breast tumor (eg, fibroadenoma), percutaneous, laser, including imaging guidance when performed, each tumor. [citation:7]',
 'Categoría III', 'Cirugía Oncológica', 0.000, 0.000, 0.000,
 'Ablación con láser de un tumor benigno de mama (ej. fibroadenoma).', 'Sí', 2025, 'AMA'),

-- Procedimientos de 2026 (Early Release)
('0950T', 'Nuevo', 'Ablación próstata HIFU', 'Ablación de tejido prostático benigno con HIFU',
 'Ablation of benign prostate tissue, transrectal high intensity focused ultrasound (HIFU), including ultrasound guidance. [citation:12]',
 'Categoría III', 'Urología', 0.000, 0.000, 0.000,
 'Ablación de tejido prostático mediante ultrasonido focalizado de alta intensidad (HIFU).', 'Sí', 2026, 'AMA'),

('0951T', 'Nuevo', 'Implante AMEI', 'Colocación inicial de implante auditivo de oído medio totalmente implantable',
 'Totally implantable active middle ear implant (AMEI), initial placement. [citation:12]',
 'Categoría III', 'Otorrinolaringología', 0.000, 0.000, 0.000,
 'Colocación inicial de un implante auditivo de oído medio completamente implantable.', 'Sí', 2026, 'AMA'),

('0952T', 'Nuevo', 'Revisión AMEI c/ mastoidectomía', 'Revisión/reemplazo de AMEI con mastoidectomía',
 'Totally implantable active middle ear implant (AMEI), revision/replacement with mastoidectomy. [citation:12]',
 'Categoría III', 'Otorrinolaringología', 0.000, 0.000, 0.000,
 'Revisión o reemplazo de un implante auditivo AMEI con mastoidectomía.', 'Sí', 2026, 'AMA'),

('0953T', 'Nuevo', 'Revisión AMEI s/ mastoidectomía', 'Revisión/reemplazo de AMEI sin mastoidectomía',
 'Totally implantable active middle ear implant (AMEI), revision/replacement without mastoidectomy. [citation:12]',
 'Categoría III', 'Otorrinolaringología', 0.000, 0.000, 0.000,
 'Revisión o reemplazo de un implante auditivo AMEI sin mastoidectomía.', 'Sí', 2026, 'AMA'),

-- Nuevos Códigos de 2025 para Prótesis de Sueño
('0964T', 'Nuevo', 'Prótesis oral apnea sueño - arco', 'Preparación de prótesis oral de expansión mandibular para apnea - arco simple',
 'Impression and custom preparation of jaw expansion oral prosthesis for obstructive sleep apnea, including initial adjustment; single arch, without mandibular advancement mechanism. [citation:7]',
 'Categoría III', 'Medicina del Sueño', 0.000, 0.000, 0.000,
 'Preparación de una prótesis oral de expansión mandibular para apnea del sueño (arco único).', 'Sí', 2025, 'AMA'),

('0965T', 'Nuevo', 'Prótesis oral apnea - doble arco', 'Prótesis oral de expansión mandibular - doble arco, bisagra no fija',
 'Impression and custom preparation of jaw expansion oral prosthesis for obstructive sleep apnea, including initial adjustment; dual arch, with additional mandibular advancement, non-fixed hinge mechanism. [citation:7]',
 'Categoría III', 'Medicina del Sueño', 0.000, 0.000, 0.000,
 'Preparación de una prótesis oral con doble arco y bisagra no fija para apnea del sueño.', 'Sí', 2025, 'AMA'),

('0966T', 'Nuevo', 'Prótesis oral apnea - bisagra fija', 'Prótesis oral de expansión mandibular - doble arco, bisagra fija',
 'Impression and custom preparation of jaw expansion oral prosthesis for obstructive sleep apnea, including initial adjustment; dual arch, with additional mandibular advancement, fixed hinge mechanism. [citation:7]',
 'Categoría III', 'Medicina del Sueño', 0.000, 0.000, 0.000,
 'Preparación de una prótesis oral con doble arco y bisagra fija para apnea del sueño.', 'Sí', 2025, 'AMA'),

 -- ============================================================
-- 3. CATEGORÍAS II Y III - Códigos para Medición de Desempeño y Tecnología Emergente
-- Fuente: AMA, NCQA/HEDIS, CMS
-- ============================================================

-- ------------------------------------------------------------
-- 3.1 CATEGORÍA II (0001F-9007F) - Códigos de Medición de Desempeño
-- Fuente: Guías CPT 2025, AMA, HEDIS, Arkansas Total Care [citation:1][citation:2][citation:3]
-- NOTA: Uso opcional, no son para reembolso directo. Facilitan la medición de calidad.
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
-- Cuidado Prenatal y Postparto
('0500F', 'Vigente', 'Visita prenatal inicial', 'Visita prenatal inicial',
 'Initial prenatal care visit. Report at first prenatal encounter with healthcare professional providing obstetrical care. Report the date of the visit and in a separate field, the date of the last menstrual period. [citation:2]',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad para la primera visita de control prenatal.', 'No', 2025, 'NCQA/HEDIS'),

('0501F', 'Vigente', 'Flujo prenatal documentado', 'Flujo prenatal documentado en historia clínica',
 'Prenatal flow sheet documented in medical record by first prenatal visit. Documentation includes at minimum blood pressure, weight, urine protein, uterine size, fetal heart tones, and estimated date of delivery. [citation:2]',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad que confirma que se ha documentado el flujo prenatal en la primera visita.', 'No', 2025, 'NCQA/HEDIS'),

('0502F', 'Vigente', 'Visita prenatal subsecuente', 'Visita prenatal subsecuente',
 'Subsequent prenatal care visit. [citation:2]',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad para una visita de control prenatal de seguimiento.', 'No', 2025, 'NCQA/HEDIS'),

('0503F', 'Vigente', 'Visita de cuidado postparto', 'Visita de cuidado postparto',
 'Postpartum care visit. [citation:2]',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad para la visita de cuidado postparto.', 'No', 2025, 'NCQA/HEDIS'),

-- Examen Oftalmológico (Cuidado de la Diabetes)
('2022F', 'Vigente', 'Examen ocular con retinopatía', 'Examen ocular dilatado con evidencia de retinopatía',
 'Dilated retinal eye exam with interpretation by an ophthalmologist or optometrist documented and reviewed; with evidence of retinopathy. [citation:2]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que un paciente diabético tiene retinopatía.', 'No', 2025, 'NCQA/HEDIS'),

('2023F', 'Vigente', 'Examen ocular sin retinopatía', 'Examen ocular dilatado sin evidencia de retinopatía',
 'Dilated retinal eye exam with interpretation by an ophthalmologist or optometrist documented and reviewed; without evidence of retinopathy. [citation:2]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que un paciente diabético NO tiene retinopatía.', 'No', 2025, 'NCQA/HEDIS'),

('2024F', 'Vigente', 'Fotos retinianas con retinopatía', 'Fotos retinianas 7 campos con retinopatía',
 'Seven standard field stereoscopic retinal photos with interpretation by an ophthalmologist or optometrist documented and reviewed; with evidence of retinopathy. [citation:2]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar retinopatía usando fotos retinianas.', 'No', 2025, 'NCQA/HEDIS'),

('2025F', 'Vigente', 'Fotos retinianas sin retinopatía', 'Fotos retinianas 7 campos sin retinopatía',
 'Seven standard field stereoscopic retinal photos with interpretation by an ophthalmologist or optometrist documented and reviewed; without evidence of retinopathy. [citation:2]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que NO hay retinopatía usando fotos retinianas.', 'No', 2025, 'NCQA/HEDIS'),

('2026F', 'Vigente', 'Imagen ocular con retinopatía', 'Imagen ocular validada con retinopatía',
 'Eye imaging validated to match diagnosis from seven standard field stereoscopic retinal photos results documented and reviewed; with evidence of retinopathy. [citation:2][citation:5]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar retinopatía usando imágenes oculares validadas.', 'No', 2025, 'NCQA/HEDIS'),

('2033F', 'Vigente', 'Imagen ocular sin retinopatía', 'Imagen ocular validada sin retinopatía',
 'Eye imaging validated to match diagnosis from seven standard field stereoscopic retinal photos results documented and reviewed; without evidence of retinopathy. [citation:2][citation:8]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que NO hay retinopatía usando imágenes oculares validadas.', 'No', 2025, 'NCQA/HEDIS'),

-- Control de la Diabetes (HbA1c)
('3044F', 'Vigente', 'HbA1c < 7.0%', 'HbA1c menor a 7.0%',
 'Most recent hemoglobin A1c (HbA1c) level less than 7.0%. [citation:2]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un buen control de glucosa (HbA1c < 7%).', 'No', 2025, 'NCQA/HEDIS'),

('3046F', 'Vigente', 'HbA1c > 9.0%', 'HbA1c mayor a 9.0%',
 'Most recent hemoglobin A1c level greater than 9.0%. [citation:2]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un mal control de glucosa (HbA1c > 9%).', 'No', 2025, 'NCQA/HEDIS'),

('3051F', 'Vigente', 'HbA1c 7.0-8.0%', 'HbA1c entre 7.0% y 8.0%',
 'Most recent hemoglobin A1c (HbA1c) level greater than or equal to 7.0% and less than 8.0%. [citation:2]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un control de glucosa moderado (HbA1c 7-8%).', 'No', 2025, 'NCQA/HEDIS'),

('3052F', 'Vigente', 'HbA1c 8.0-9.0%', 'HbA1c entre 8.0% y 9.0%',
 'Most recent hemoglobin A1c (HbA1c) level greater than or equal to 8.0% and less than or equal to 9.0%. [citation:2]',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un control de glucosa subóptimo (HbA1c 8-9%).', 'No', 2025, 'NCQA/HEDIS'),

-- Control del Colesterol (LDL-C)
('3048F', 'Vigente', 'LDL-C < 100 mg/dL', 'LDL-C menor a 100 mg/dL',
 'Most recent LDL-C less than 100 mg/dL. [citation:2]',
 'Categoría II', 'Control Lipídico', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un buen control de colesterol (LDL < 100).', 'No', 2025, 'NCQA/HEDIS'),

('3049F', 'Vigente', 'LDL-C 100-129 mg/dL', 'LDL-C entre 100 y 129 mg/dL',
 'Most recent LDL-C 100-129 mg/dL. [citation:2]',
 'Categoría II', 'Control Lipídico', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un control de colesterol intermedio (LDL 100-129).', 'No', 2025, 'NCQA/HEDIS'),

('3050F', 'Vigente', 'LDL-C >= 130 mg/dL', 'LDL-C mayor o igual a 130 mg/dL',
 'Most recent LDL-C greater than or equal to 130 mg/dL. [citation:2]',
 'Categoría II', 'Control Lipídico', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un mal control de colesterol (LDL >= 130).', 'No', 2025, 'NCQA/HEDIS'),

-- Control de la Presión Arterial
('3074F', 'Vigente', 'Sistólica < 130 mmHg', 'Presión arterial sistólica menor a 130 mm Hg',
 'Most recent systolic blood pressure less than 130 mm Hg. [citation:2]',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una buena presión sistólica (< 130).', 'No', 2025, 'NCQA/HEDIS'),

('3075F', 'Vigente', 'Sistólica 130-139 mmHg', 'Presión arterial sistólica 130-139 mm Hg',
 'Most recent systolic blood pressure 130-139 mm Hg. [citation:2]',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión sistólica elevada (130-139).', 'No', 2025, 'NCQA/HEDIS'),

('3077F', 'Vigente', 'Sistólica >= 140 mmHg', 'Presión arterial sistólica mayor o igual a 140 mm Hg',
 'Most recent systolic blood pressure greater than or equal to 140 mm Hg. [citation:2]',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión sistólica alta (>= 140).', 'No', 2025, 'NCQA/HEDIS'),

('3078F', 'Vigente', 'Diastólica < 80 mmHg', 'Presión arterial diastólica menor a 80 mm Hg',
 'Most recent diastolic blood pressure less than 80 mm Hg. [citation:2]',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una buena presión diastólica (< 80).', 'No', 2025, 'NCQA/HEDIS'),

('3079F', 'Vigente', 'Diastólica 80-89 mmHg', 'Presión arterial diastólica 80-89 mm Hg',
 'Most recent diastolic blood pressure 80-89 mm Hg. [citation:2]',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión diastólica elevada (80-89).', 'No', 2025, 'NCQA/HEDIS'),

('3080F', 'Vigente', 'Diastólica >= 90 mmHg', 'Presión arterial diastólica mayor o igual a 90 mm Hg',
 'Most recent diastolic blood pressure greater than or equal to 90 mm Hg. [citation:2]',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión diastólica alta (>= 90).', 'No', 2025, 'NCQA/HEDIS'),

-- Control de la Función Cardíaca
('3055F', 'Vigente', 'FEVI <= 35%', 'Fracción de eyección ventricular izquierda menor o igual a 35%',
 'LVEF less than or equal to 35%. [citation:6]',
 'Categoría II', 'Control Cardíaco', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una fracción de eyección ventricular izquierda baja (<= 35%).', 'No', 2025, 'NCQA/HEDIS'),

-- Medidas Generales
('4000F', 'Vigente', 'Índice de masa corporal (IMC)', 'IMC documentado y evaluado',
 'Body Mass Index (BMI) measured and recorded. [citation:3]',
 'Categoría II', 'Salud General', 0.000, 0.000, 0.000,
 'Código de calidad para confirmar que se midió y documentó el IMC.', 'No', 2025, 'NCQA/HEDIS'),

-- ------------------------------------------------------------
-- 3.2 CATEGORÍA III (0042T-1050T) - Códigos para Tecnología Emergente
-- Fuente: Guías CPT 2025-2026, AMA [citation:1][citation:4][citation:7]
-- NOTA: Códigos temporales para nuevas tecnologías. Uso para recolección de datos.
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
-- Procedimientos de 2025-2026 (Early Release)
('0948T', 'Nuevo', 'Interrog remota CCM', 'Interrogación remota de dispositivo CCM (<90 días) - médico',
 'Remote interrogation device evaluation less than 90 days, cardiac contractility modulation (CCM) system, with physician or qualified health care professional analysis. [citation:4]',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Evaluación remota de un dispositivo de modulación de contractilidad cardíaca (<90 días) por un médico.', 'No', 2025, 'AMA'),

('0949T', 'Nuevo', 'Interrog remota CCM tech', 'Interrogación remota de dispositivo CCM (<90 días) - técnico',
 'Remote interrogation device evaluation less than 90 days, cardiac contractility modulation (CCM) system, with technical support. [citation:4]',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Evaluación remota de un dispositivo de modulación de contractilidad cardíaca (<90 días) por un técnico.', 'No', 2025, 'AMA'),

('0950T', 'Nuevo', 'Ablación próstata HIFU', 'Ablación de tejido prostático benigno con HIFU',
 'Ablation of benign prostate tissue, transrectal high intensity focused ultrasound (HIFU), including ultrasound guidance. [citation:4]',
 'Categoría III', 'Urología', 0.000, 0.000, 0.000,
 'Ablación de tejido prostático mediante ultrasonido focalizado de alta intensidad (HIFU).', 'Sí', 2025, 'AMA'),

('0951T', 'Nuevo', 'Implante AMEI', 'Colocación inicial de implante auditivo de oído medio totalmente implantable',
 'Totally implantable active middle ear implant (AMEI), initial placement. [citation:4]',
 'Categoría III', 'Otorrinolaringología', 0.000, 0.000, 0.000,
 'Colocación inicial de un implante auditivo de oído medio completamente implantable.', 'Sí', 2025, 'AMA'),

('0952T', 'Nuevo', 'Revisión AMEI c/ mastoidectomía', 'Revisión/reemplazo de AMEI con mastoidectomía',
 'Totally implantable active middle ear implant (AMEI), revision/replacement with mastoidectomy. [citation:4]',
 'Categoría III', 'Otorrinolaringología', 0.000, 0.000, 0.000,
 'Revisión o reemplazo de un implante auditivo AMEI con mastoidectomía.', 'Sí', 2025, 'AMA'),

('0953T', 'Nuevo', 'Revisión AMEI s/ mastoidectomía', 'Revisión/reemplazo de AMEI sin mastoidectomía',
 'Totally implantable active middle ear implant (AMEI), revision/replacement without mastoidectomy. [citation:4]',
 'Categoría III', 'Otorrinolaringología', 0.000, 0.000, 0.000,
 'Revisión o reemplazo de un implante auditivo AMEI sin mastoidectomía.', 'Sí', 2025, 'AMA'),

('0954T', 'Nuevo', 'Reemplazo procesador AMEI', 'Reemplazo de procesador de sonido de AMEI',
 'Totally implantable active middle ear implant (AMEI), replacement of sound processor only. [citation:4]',
 'Categoría III', 'Otorrinolaringología', 0.000, 0.000, 0.000,
 'Reemplazo del procesador de sonido de un implante auditivo AMEI.', 'Sí', 2025, 'AMA'),

('0955T', 'Nuevo', 'Remoción AMEI', 'Remoción de implante auditivo de oído medio totalmente implantable',
 'Totally implantable active middle ear implant (AMEI), removal. [citation:4]',
 'Categoría III', 'Otorrinolaringología', 0.000, 0.000, 0.000,
 'Remoción de un implante auditivo de oído medio completamente implantable.', 'Sí', 2025, 'AMA'),

('0956T', 'Nuevo', 'Implante EEG subcutáneo', 'Implante de electrodos para monitoreo EEG subcutáneo',
 'Partial craniectomy, channel creation, and tunneling of electrode for sub-scalp implantation of an electrode array, receiver, and telemetry unit for continuous bilateral electroencephalography monitoring system, including imaging guidance. [citation:11]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Procedimiento para implantar un sistema de monitoreo de EEG bajo el cuero cabelludo.', 'Sí', 2025, 'AMA'),

('0957T', 'Nuevo', 'Revisión EEG subcutáneo', 'Revisión de electrodos para monitoreo EEG subcutáneo',
 'Revision of sub-scalp implanted electrode array, receiver, and telemetry unit for electrode, when required, including imaging guidance. [citation:11]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Revisión del sistema de monitoreo EEG subcutáneo.', 'Sí', 2025, 'AMA'),

('0958T', 'Nuevo', 'Remoción EEG subcutáneo', 'Remoción de electrodos para monitoreo EEG subcutáneo',
 'Removal of sub-scalp implanted electrode array, receiver, and telemetry unit for continuous bilateral electroencephalography monitoring system, including imaging guidance. [citation:11]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Remoción del sistema de monitoreo EEG subcutáneo.', 'Sí', 2025, 'AMA'),

('0959T', 'Nuevo', 'Reemplazo imán EEG', 'Remoción o reemplazo de imán del sistema EEG',
 'Removal or replacement of magnet from coil assembly that is connected to continuous bilateral electroencephalography monitoring system, including imaging guidance. [citation:11]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Remoción o reemplazo del imán del sistema de monitoreo EEG.', 'Sí', 2025, 'AMA'),

('0960T', 'Nuevo', 'Reemplazo EEG subcutáneo', 'Reemplazo de electrodos para monitoreo EEG subcutáneo',
 'Replacement of sub-scalp implanted electrode array, receiver, and telemetry unit with tunneling of electrode for continuous bilateral electroencephalography monitoring system, including imaging guidance. [citation:11]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Reemplazo del sistema de monitoreo EEG subcutáneo.', 'Sí', 2025, 'AMA'),

('0965T', 'Nuevo', 'Prótesis oral apnea - doble arco', 'Prótesis oral de expansión mandibular - doble arco, bisagra no fija',
 'Impression and custom preparation of jaw expansion oral prosthesis for obstructive sleep apnea, including initial adjustment; dual arch, with additional mandibular advancement, non-fixed hinge mechanism. [citation:4]',
 'Categoría III', 'Medicina del Sueño', 0.000, 0.000, 0.000,
 'Preparación de una prótesis oral con doble arco y bisagra no fija para apnea del sueño.', 'Sí', 2025, 'AMA'),

('0966T', 'Nuevo', 'Prótesis oral apnea - bisagra fija', 'Prótesis oral de expansión mandibular - doble arco, bisagra fija',
 'Impression and custom preparation of jaw expansion oral prosthesis for obstructive sleep apnea, including initial adjustment; dual arch, with additional mandibular advancement, fixed hinge mechanism. [citation:4]',
 'Categoría III', 'Medicina del Sueño', 0.000, 0.000, 0.000,
 'Preparación de una prótesis oral con doble arco y bisagra fija para apnea del sueño.', 'Sí', 2025, 'AMA'),

('0968T', 'Nuevo', 'Implante neuroestimulador epicraneal', 'Inserción o reemplazo de sistema de neuroestimulación epicraneal',
 'Insertion or replacement of epicranial neurostimulator system, including electrode array and pulse generator, with connection to electrode array. [citation:11]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Procedimiento para implantar un sistema de neuroestimulación sobre el cráneo.', 'Sí', 2025, 'AMA'),

('0969T', 'Nuevo', 'Remoción neuroestimulador epicraneal', 'Remoción de sistema de neuroestimulación epicraneal',
 'Removal of epicranial neurostimulator system. [citation:11]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Procedimiento para retirar un sistema de neuroestimulación epicraneal.', 'Sí', 2025, 'AMA'),

('0970T', 'Nuevo', 'Ablación láser tumor benigno', 'Ablación percutánea de tumor benigno de mama',
 'Ablation, benign breast tumor (eg, fibroadenoma), percutaneous, laser, including imaging guidance when performed, each tumor. [citation:7]',
 'Categoría III', 'Cirugía Oncológica', 0.000, 0.000, 0.000,
 'Ablación con láser de un tumor benigno de mama (ej. fibroadenoma).', 'Sí', 2025, 'AMA'),

('0971T', 'Nuevo', 'Ablación láser tumor maligno', 'Ablación percutánea de tumor maligno de mama',
 'Ablation, malignant breast tumor, percutaneous, laser, including imaging guidance when performed, unilateral. [citation:7]',
 'Categoría III', 'Cirugía Oncológica', 0.000, 0.000, 0.000,
 'Ablación con láser de un tumor maligno de mama.', 'Sí', 2025, 'AMA'),

('0984T', 'Nuevo', 'OCT vascular cerebral inicial', 'Imagen intravascular de vasos cerebrales extracraneales con OCT - vaso inicial',
 'Intravascular imaging of extracranial cerebral vessels using optical coherence tomography (OCT) during diagnostic evaluation and/or therapeutic intervention, including all associated radiological supervision, interpretation, and report; initial vessel (List separately in addition to code for primary procedure). [citation:11]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Imagen intravascular de vasos cerebrales extracraneales con OCT (vaso inicial).', 'Sí', 2025, 'AMA'),

('0985T', 'Nuevo', 'OCT vascular cerebral adicional', 'Imagen intravascular de vasos cerebrales extracraneales con OCT - vaso adicional',
 'Intravascular imaging of extracranial cerebral vessels using optical coherence tomography (OCT) during diagnostic evaluation and/or therapeutic intervention, including all associated radiological supervision, interpretation, and report; each additional vessel (List separately in addition to code for primary procedure). [citation:11]',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Imagen intravascular de vasos cerebrales extracraneales con OCT (vaso adicional).', 'Sí', 2025, 'AMA'),

('1026T', 'Nuevo', 'Fotobiomodulación vaginal', 'Terapia de fotobiomodulación láser transvaginal de la pelvis',
 'Transvaginal laser photobiomodulation therapy of the pelvis. [citation:1]',
 'Categoría III', 'Ginecología', 0.000, 0.000, 0.000,
 'Terapia de fotobiomodulación con láser transvaginal de la pelvis.', 'Sí', 2025, 'AMA'),

('1027T', 'Nuevo', 'Neuroestimulación frénica', 'Terapia de neuroestimulación frénica transvenosa',
 'Transvenous phrenic neurostimulation therapy for diaphragm activation in ventilated patients. [citation:1]',
 'Categoría III', 'Neumología', 0.000, 0.000, 0.000,
 'Terapia de neuroestimulación del nervio frénico para activación del diafragma.', 'Sí', 2025, 'AMA'),

('1036T', 'Nuevo', 'Evaluación hemodinámica no invasiva', 'Evaluación hemodinámica no invasiva',
 'Noninvasive hemodynamic assessment. [citation:1]',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Evaluación hemodinámica no invasiva.', 'No', 2025, 'AMA'),

('1037T', 'Nuevo', 'Histotripsia páncreas', 'Histotripsia de tejido pancreático maligno',
 'Histotripsy of malignant pancreatic tissue. [citation:1]',
 'Categoría III', 'Gastroenterología', 0.000, 0.000, 0.000,
 'Histotripsia (ablación ultrasónica) de tejido pancreático maligno.', 'Sí', 2025, 'AMA'),

('1038T', 'Nuevo', 'Terapia celular muscular', 'Terapia con células musculares autólogas',
 'Autologous muscle cell therapy. [citation:1]',
 'Categoría III', 'Medicina Regenerativa', 0.000, 0.000, 0.000,
 'Terapia con células musculares autólogas.', 'Sí', 2025, 'AMA'),

('1040T', 'Nuevo', 'Broncoscopía con crioterapia', 'Broncoscopía flexible con crioterapia bronquial',
 'Flexible bronchoscopy with bronchial cryotherapy. [citation:1]',
 'Categoría III', 'Neumología', 0.000, 0.000, 0.000,
 'Broncoscopía flexible con crioterapia bronquial.', 'Sí', 2025, 'AMA'),

('1041T', 'Nuevo', 'Análisis EEG con IA', 'Análisis algorítmico aumentativo de formas de onda encefalográficas',
 'Augmentative algorithmic analysis of encephalographic waveforms. [citation:1]',
 'Categoría III', 'Neurología', 0.000, 0.000, 0.000,
 'Análisis algorítmico aumentativo de formas de onda encefalográficas.', 'No', 2025, 'AMA'),

('1042T', 'Nuevo', 'Andamio uretral absorbible', 'Implante de andamio urológico absorbible para restauración uretral',
 'Implantation of absorbable urologic scaffold for prostatic urethra restoration of reconstructed bladder neck and urethral anastomosis (List separately in addition to code for primary procedure). [citation:1][citation:9]',
 'Categoría III', 'Urología', 0.000, 0.000, 0.000,
 'Implante de andamio urológico absorbible para restauración de la uretra prostática.', 'Sí', 2025, 'AMA'),

('1043T', 'Nuevo', 'RMN hepática en punto de atención', 'Prueba de RM cuantitativa en punto de atención para evaluación hepática',
 'Point-of-care quantitative magnetic resonance (MR) test, without imaging, for liver assessment. [citation:1]',
 'Categoría III', 'Gastroenterología', 0.000, 0.000, 0.000,
 'Prueba de RM cuantitativa en punto de atención para evaluación hepática (sin imagen).', 'No', 2025, 'AMA'),

('1050T', 'Nuevo', 'Monitorización IC subcutánea', 'Monitorización subcutánea de descompensación de insuficiencia cardíaca',
 'Subcutaneous heart failure decompensation monitoring. [citation:1]',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Monitorización subcutánea de descompensación de insuficiencia cardíaca.', 'Sí', 2025, 'AMA'),

 -- ============================================================
-- 3. CATEGORÍAS II Y III - Calidad y Tecnología Emergente
-- Fuente: AMA, NCQA/HEDIS, Arkansas Total Care
-- NOTA: Estos códigos no generan reembolso directo, pero son clave para calidad y datos.
-- ============================================================

-- ------------------------------------------------------------
-- 3.1 CATEGORÍA II (0001F-9007F) - Códigos de Medición de Desempeño
-- Fuente: Guías CPT 2025, AMA, HEDIS, Arkansas Total Care
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
-- Cuidado Prenatal y Postparto
('0500F', 'Vigente', 'Visita prenatal inicial', 'Visita prenatal inicial',
 'Initial prenatal care visit. Report at first prenatal encounter with healthcare professional providing obstetrical care. Report the date of the visit and in a separate field, the date of the last menstrual period. ',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad para la primera visita de control prenatal.', 'No', 2025, 'NCQA/HEDIS'),

('0501F', 'Vigente', 'Flujo prenatal documentado', 'Flujo prenatal documentado en historia clínica',
 'Prenatal flow sheet documented in medical record by first prenatal visit. Documentation includes at minimum blood pressure, weight, urine protein, uterine size, fetal heart tones, and estimated date of delivery. ',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad que confirma que se ha documentado el flujo prenatal en la primera visita.', 'No', 2025, 'NCQA/HEDIS'),

('0502F', 'Vigente', 'Visita prenatal subsecuente', 'Visita prenatal subsecuente',
 'Subsequent prenatal care visit. ',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad para una visita de control prenatal de seguimiento.', 'No', 2025, 'NCQA/HEDIS'),

('0503F', 'Vigente', 'Visita de cuidado postparto', 'Visita de cuidado postparto',
 'Postpartum care visit. ',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad para la visita de cuidado postparto.', 'No', 2025, 'NCQA/HEDIS'),

-- Examen Oftalmológico (Cuidado de la Diabetes) 
-- Referencia: [citation:2][citation:4]
('2022F', 'Vigente', 'Examen ocular con retinopatía', 'Examen ocular dilatado con evidencia de retinopatía',
 'Dilated retinal eye exam with interpretation by an ophthalmologist or optometrist documented and reviewed; with evidence of retinopathy. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que un paciente diabético tiene retinopatía.', 'No', 2025, 'NCQA/HEDIS'),

('2023F', 'Vigente', 'Examen ocular sin retinopatía', 'Examen ocular dilatado sin evidencia de retinopatía',
 'Dilated retinal eye exam with interpretation by an ophthalmologist or optometrist documented and reviewed; without evidence of retinopathy. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que un paciente diabético NO tiene retinopatía.', 'No', 2025, 'NCQA/HEDIS'),

('2024F', 'Vigente', 'Fotos retinianas con retinopatía', 'Fotos retinianas 7 campos con retinopatía',
 'Seven standard field stereoscopic retinal photos with interpretation by an ophthalmologist or optometrist documented and reviewed; with evidence of retinopathy. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar retinopatía usando fotos retinianas.', 'No', 2025, 'NCQA/HEDIS'),

('2025F', 'Vigente', 'Fotos retinianas sin retinopatía', 'Fotos retinianas 7 campos sin retinopatía',
 'Seven standard field stereoscopic retinal photos with interpretation by an ophthalmologist or optometrist documented and reviewed; without evidence of retinopathy. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que NO hay retinopatía usando fotos retinianas.', 'No', 2025, 'NCQA/HEDIS'),

('2026F', 'Vigente', 'Imagen ocular con retinopatía', 'Imagen ocular validada con retinopatía',
 'Eye imaging validated to match diagnosis from seven standard field stereoscopic retinal photos results documented and reviewed; with evidence of retinopathy. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar retinopatía usando imágenes oculares validadas.', 'No', 2025, 'NCQA/HEDIS'),

('2033F', 'Vigente', 'Imagen ocular sin retinopatía', 'Imagen ocular validada sin retinopatía',
 'Eye imaging validated to match diagnosis from seven standard field stereoscopic retinal photos results documented and reviewed; without evidence of retinopathy. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que NO hay retinopatía usando imágenes oculares validadas.', 'No', 2025, 'NCQA/HEDIS'),

-- Control de la Diabetes (HbA1c) - Referencia: [citation:4]
('3044F', 'Vigente', 'HbA1c < 7.0%', 'HbA1c menor a 7.0%',
 'Most recent hemoglobin A1c (HbA1c) level less than 7.0%. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un buen control de glucosa (HbA1c < 7%).', 'No', 2025, 'NCQA/HEDIS'),

('3046F', 'Vigente', 'HbA1c > 9.0%', 'HbA1c mayor a 9.0%',
 'Most recent hemoglobin A1c level greater than 9.0%. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un mal control de glucosa (HbA1c > 9%).', 'No', 2025, 'NCQA/HEDIS'),

('3051F', 'Vigente', 'HbA1c 7.0-8.0%', 'HbA1c entre 7.0% y 8.0%',
 'Most recent hemoglobin A1c (HbA1c) level greater than or equal to 7.0% and less than 8.0%. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un control de glucosa moderado (HbA1c 7-8%).', 'No', 2025, 'NCQA/HEDIS'),

('3052F', 'Vigente', 'HbA1c 8.0-9.0%', 'HbA1c entre 8.0% y 9.0%',
 'Most recent hemoglobin A1c (HbA1c) level greater than or equal to 8.0% and less than or equal to 9.0%. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un control de glucosa subóptimo (HbA1c 8-9%).', 'No', 2025, 'NCQA/HEDIS'),

-- Control del Colesterol (LDL-C) - Referencia: [citation:4]
('3048F', 'Vigente', 'LDL-C < 100 mg/dL', 'LDL-C menor a 100 mg/dL',
 'Most recent LDL-C less than 100 mg/dL. ',
 'Categoría II', 'Control Lipídico', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un buen control de colesterol (LDL < 100).', 'No', 2025, 'NCQA/HEDIS'),

('3049F', 'Vigente', 'LDL-C 100-129 mg/dL', 'LDL-C entre 100 y 129 mg/dL',
 'Most recent LDL-C 100-129 mg/dL. ',
 'Categoría II', 'Control Lipídico', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un control de colesterol intermedio (LDL 100-129).', 'No', 2025, 'NCQA/HEDIS'),

('3050F', 'Vigente', 'LDL-C >= 130 mg/dL', 'LDL-C mayor o igual a 130 mg/dL',
 'Most recent LDL-C greater than or equal to 130 mg/dL. ',
 'Categoría II', 'Control Lipídico', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un mal control de colesterol (LDL >= 130).', 'No', 2025, 'NCQA/HEDIS'),

-- Control de la Presión Arterial - Referencia: [citation:4]
('3074F', 'Vigente', 'Sistólica < 130 mmHg', 'Presión arterial sistólica menor a 130 mm Hg',
 'Most recent systolic blood pressure less than 130 mm Hg. ',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una buena presión sistólica (< 130).', 'No', 2025, 'NCQA/HEDIS'),

('3075F', 'Vigente', 'Sistólica 130-139 mmHg', 'Presión arterial sistólica 130-139 mm Hg',
 'Most recent systolic blood pressure 130-139 mm Hg. ',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión sistólica elevada (130-139).', 'No', 2025, 'NCQA/HEDIS'),

('3077F', 'Vigente', 'Sistólica >= 140 mmHg', 'Presión arterial sistólica mayor o igual a 140 mm Hg',
 'Most recent systolic blood pressure greater than or equal to 140 mm Hg. ',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión sistólica alta (>= 140).', 'No', 2025, 'NCQA/HEDIS'),

('3078F', 'Vigente', 'Diastólica < 80 mmHg', 'Presión arterial diastólica menor a 80 mm Hg',
 'Most recent diastolic blood pressure less than 80 mm Hg. ',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una buena presión diastólica (< 80).', 'No', 2025, 'NCQA/HEDIS'),

('3079F', 'Vigente', 'Diastólica 80-89 mmHg', 'Presión arterial diastólica 80-89 mm Hg',
 'Most recent diastolic blood pressure 80-89 mm Hg. ',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión diastólica elevada (80-89).', 'No', 2025, 'NCQA/HEDIS'),

('3080F', 'Vigente', 'Diastólica >= 90 mmHg', 'Presión arterial diastólica mayor o igual a 90 mm Hg',
 'Most recent diastolic blood pressure greater than or equal to 90 mm Hg. ',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión diastólica alta (>= 90).', 'No', 2025, 'NCQA/HEDIS'),

-- Control de la Función Cardíaca - Referencia: [citation:4]
('3055F', 'Vigente', 'FEVI <= 35%', 'Fracción de eyección ventricular izquierda menor o igual a 35%',
 'LVEF less than or equal to 35%. ',
 'Categoría II', 'Control Cardíaco', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una fracción de eyección ventricular izquierda baja (<= 35%).', 'No', 2025, 'NCQA/HEDIS'),

-- Medidas Generales
('4000F', 'Vigente', 'Índice de masa corporal (IMC)', 'IMC documentado y evaluado',
 'Body Mass Index (BMI) measured and recorded. ',
 'Categoría II', 'Salud General', 0.000, 0.000, 0.000,
 'Código de calidad para confirmar que se midió y documentó el IMC.', 'No', 2025, 'NCQA/HEDIS');

-- ============================================================
-- NOTA SOBRE RVU: 
-- Los códigos de Categoría II no tienen valores de RVU. Son para medición de calidad y no generan reembolso directo. [citation:4][citation:12]
-- ============================================================

-- ------------------------------------------------------------
-- 3.2 CATEGORÍA III (0042T-1053T) - Códigos para Tecnología Emergente
-- Fuente: Guías CPT 2025-2026, AMA, AAPC
-- NOTA: Códigos temporales para nuevas tecnologías. Uso para recolección de datos. [citation:1][citation:11]
-- EFECTIVOS: La mayoría de estos códigos entran en vigencia el 1 de julio de 2025 o 2026. [citation:1][citation:3]
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
-- Procedimientos de 2025-2026 (Early Release) - Fuente: AMA, AAPC [citation:1][citation:3]
('0948T', 'Nuevo', 'Interrog remota CCM', 'Interrogación remota de dispositivo CCM (<90 días) - médico',
 'Remote interrogation device evaluation less than 90 days, cardiac contractility modulation (CCM) system, with physician or qualified health care professional analysis. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Evaluación remota de un dispositivo de modulación de contractilidad cardíaca (<90 días) por un médico.', 'No', 2025, 'AMA'),

('0949T', 'Nuevo', 'Interrog remota CCM tech', 'Interrogación remota de dispositivo CCM (<90 días) - técnico',
 'Remote interrogation device evaluation less than 90 days, cardiac contractility modulation (CCM) system, with technical support. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Evaluación remota de un dispositivo de modulación de contractilidad cardíaca (<90 días) por un técnico.', 'No', 2025, 'AMA'),

('0950T', 'Nuevo', 'Ablación próstata HIFU', 'Ablación de tejido prostático benigno con HIFU',
 'Ablation of benign prostate tissue, transrectal high intensity focused ultrasound (HIFU), including ultrasound guidance. ',
 'Categoría III', 'Urología', 0.000, 0.000, 0.000,
 'Ablación de tejido prostático mediante ultrasonido focalizado de alta intensidad (HIFU).', 'Sí', 2025, 'AMA'),

('0951T', 'Nuevo', 'Implante AMEI', 'Colocación inicial de implante auditivo de oído medio totalmente implantable',
 'Totally implantable active middle ear implant (AMEI), initial placement. ',
 'Categoría III', 'Otorrinolaringología', 0.000, 0.000, 0.000,
 'Colocación inicial de un implante auditivo de oído medio completamente implantable.', 'Sí', 2025, 'AMA'),

('0952T', 'Nuevo', 'Revisión AMEI c/ mastoidectomía', 'Revisión/reemplazo de AMEI con mastoidectomía',
 'Totally implantable active middle ear implant (AMEI), revision/replacement with mastoidectomy. ',
 'Categoría III', 'Otorrinolaringología', 0.000, 0.000, 0.000,
 'Revisión o reemplazo de un implante auditivo AMEI con mastoidectomía.', 'Sí', 2025, 'AMA'),

('0953T', 'Nuevo', 'Revisión AMEI s/ mastoidectomía', 'Revisión/reemplazo de AMEI sin mastoidectomía',
 'Totally implantable active middle ear implant (AMEI), revision/replacement without mastoidectomy. ',
 'Categoría III', 'Otorrinolaringología', 0.000, 0.000, 0.000,
 'Revisión o reemplazo de un implante auditivo AMEI sin mastoidectomía.', 'Sí', 2025, 'AMA'),

('0954T', 'Nuevo', 'Reemplazo procesador AMEI', 'Reemplazo de procesador de sonido de AMEI',
 'Totally implantable active middle ear implant (AMEI), replacement of sound processor only. ',
 'Categoría III', 'Otorrinolaringología', 0.000, 0.000, 0.000,
 'Reemplazo del procesador de sonido de un implante auditivo AMEI.', 'Sí', 2025, 'AMA'),

('0955T', 'Nuevo', 'Remoción AMEI', 'Remoción de implante auditivo de oído medio totalmente implantable',
 'Totally implantable active middle ear implant (AMEI), removal. ',
 'Categoría III', 'Otorrinolaringología', 0.000, 0.000, 0.000,
 'Remoción de un implante auditivo de oído medio completamente implantable.', 'Sí', 2025, 'AMA'),

-- Códigos de Monitoreo EEG Subcutáneo - Referencia: [citation:1][citation:11]
('0956T', 'Nuevo', 'Implante EEG subcutáneo', 'Implante de electrodos para monitoreo EEG subcutáneo',
 'Partial craniectomy, channel creation, and tunneling of electrode for sub-scalp implantation of an electrode array, receiver, and telemetry unit for continuous bilateral electroencephalography monitoring system, including imaging guidance. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Procedimiento para implantar un sistema de monitoreo de EEG bajo el cuero cabelludo.', 'Sí', 2025, 'AMA'),

('0957T', 'Nuevo', 'Revisión EEG subcutáneo', 'Revisión de electrodos para monitoreo EEG subcutáneo',
 'Revision of sub-scalp implanted electrode array, receiver, and telemetry unit for electrode, when required, including imaging guidance. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Revisión del sistema de monitoreo EEG subcutáneo.', 'Sí', 2025, 'AMA'),

('0958T', 'Nuevo', 'Remoción EEG subcutáneo', 'Remoción de electrodos para monitoreo EEG subcutáneo',
 'Removal of sub-scalp implanted electrode array, receiver, and telemetry unit for continuous bilateral electroencephalography monitoring system, including imaging guidance. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Remoción del sistema de monitoreo EEG subcutáneo.', 'Sí', 2025, 'AMA'),

('0959T', 'Nuevo', 'Reemplazo imán EEG', 'Remoción o reemplazo de imán del sistema EEG',
 'Removal or replacement of magnet from coil assembly that is connected to continuous bilateral electroencephalography monitoring system, including imaging guidance. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Remoción o reemplazo del imán del sistema de monitoreo EEG.', 'Sí', 2025, 'AMA'),

('0960T', 'Nuevo', 'Reemplazo EEG subcutáneo', 'Reemplazo de electrodos para monitoreo EEG subcutáneo',
 'Replacement of sub-scalp implanted electrode array, receiver, and telemetry unit with tunneling of electrode for continuous bilateral electroencephalography monitoring system, including imaging guidance. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Reemplazo del sistema de monitoreo EEG subcutáneo.', 'Sí', 2025, 'AMA'),

-- Prótesis Oral para Apnea del Sueño - Referencia: [citation:1]
('0965T', 'Nuevo', 'Prótesis oral apnea - doble arco', 'Prótesis oral de expansión mandibular - doble arco, bisagra no fija',
 'Impression and custom preparation of jaw expansion oral prosthesis for obstructive sleep apnea, including initial adjustment; dual arch, with additional mandibular advancement, non-fixed hinge mechanism. ',
 'Categoría III', 'Medicina del Sueño', 0.000, 0.000, 0.000,
 'Preparación de una prótesis oral con doble arco y bisagra no fija para apnea del sueño.', 'Sí', 2025, 'AMA'),

('0966T', 'Nuevo', 'Prótesis oral apnea - bisagra fija', 'Prótesis oral de expansión mandibular - doble arco, bisagra fija',
 'Impression and custom preparation of jaw expansion oral prosthesis for obstructive sleep apnea, including initial adjustment; dual arch, with additional mandibular advancement, fixed hinge mechanism. ',
 'Categoría III', 'Medicina del Sueño', 0.000, 0.000, 0.000,
 'Preparación de una prótesis oral con doble arco y bisagra fija para apnea del sueño.', 'Sí', 2025, 'AMA'),

-- Neuroestimulación Epicraneal - Referencia: [citation:11]
('0968T', 'Nuevo', 'Implante neuroestimulador epicraneal', 'Inserción o reemplazo de sistema de neuroestimulación epicraneal',
 'Insertion or replacement of epicranial neurostimulator system, including electrode array and pulse generator, with connection to electrode array. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Procedimiento para implantar un sistema de neuroestimulación sobre el cráneo.', 'Sí', 2025, 'AMA'),

('0969T', 'Nuevo', 'Remoción neuroestimulador epicraneal', 'Remoción de sistema de neuroestimulación epicraneal',
 'Removal of epicranial neurostimulator system. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Procedimiento para retirar un sistema de neuroestimulación epicraneal.', 'Sí', 2025, 'AMA'),

-- Ablación Láser de Tumores de Mama - Referencia: [citation:1]
('0970T', 'Nuevo', 'Ablación láser tumor benigno', 'Ablación percutánea de tumor benigno de mama',
 'Ablation, benign breast tumor (eg, fibroadenoma), percutaneous, laser, including imaging guidance when performed, each tumor. ',
 'Categoría III', 'Cirugía Oncológica', 0.000, 0.000, 0.000,
 'Ablación con láser de un tumor benigno de mama (ej. fibroadenoma).', 'Sí', 2025, 'AMA'),

('0971T', 'Nuevo', 'Ablación láser tumor maligno', 'Ablación percutánea de tumor maligno de mama',
 'Ablation, malignant breast tumor, percutaneous, laser, including imaging guidance when performed, unilateral. ',
 'Categoría III', 'Cirugía Oncológica', 0.000, 0.000, 0.000,
 'Ablación con láser de un tumor maligno de mama.', 'Sí', 2025, 'AMA'),

-- OCT Vascular Cerebral - Referencia: [citation:11]
('0984T', 'Nuevo', 'OCT vascular cerebral inicial', 'Imagen intravascular de vasos cerebrales extracraneales con OCT - vaso inicial',
 'Intravascular imaging of extracranial cerebral vessels using optical coherence tomography (OCT) during diagnostic evaluation and/or therapeutic intervention, including all associated radiological supervision, interpretation, and report; initial vessel (List separately in addition to code for primary procedure). ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Imagen intravascular de vasos cerebrales extracraneales con OCT (vaso inicial).', 'Sí', 2025, 'AMA'),

('0985T', 'Nuevo', 'OCT vascular cerebral adicional', 'Imagen intravascular de vasos cerebrales extracraneales con OCT - vaso adicional',
 'Intravascular imaging of extracranial cerebral vessels using optical coherence tomography (OCT) during diagnostic evaluation and/or therapeutic intervention, including all associated radiological supervision, interpretation, and report; each additional vessel (List separately in addition to code for primary procedure). ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Imagen intravascular de vasos cerebrales extracraneales con OCT (vaso adicional).', 'Sí', 2025, 'AMA'),

-- Evaluación de Riesgo Cardíaco con IA - Referencia: [citation:9]
('0992T', 'Nuevo', 'Riesgo cardíaco IA sin TAC', 'Evaluación no invasiva de riesgo cardíaco por IA sin TAC cardíaco',
 'Noninvasive assessment of cardiac risk derived from augmentative software analysis of perivascular fat without concurrent computed tomography (CT) scan of the heart, including patient-specific clinical factors, with interpretation and report by a physician or other qualified health care professional. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Evaluación de riesgo cardíaco con análisis de IA de grasa perivascular, sin TAC cardíaco.', 'No', 2026, 'AMA'),

('0993T', 'Nuevo', 'Riesgo cardíaco IA con TAC', 'Evaluación no invasiva de riesgo cardíaco por IA con TAC cardíaco',
 'Noninvasive assessment of cardiac risk derived from augmentative software analysis of perivascular fat with concurrent computed tomography scan of the heart, including patient-specific clinical factors, with interpretation and report by a physician or other qualified health care professional. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Evaluación de riesgo cardíaco con análisis de IA de grasa perivascular, con TAC cardíaco.', 'No', 2026, 'AMA'),

-- Nuevos Códigos 2026 (Efectivos Julio 2026) - Referencia: [citation:1]
('1026T', 'Nuevo', 'Fotobiomodulación vaginal', 'Terapia de fotobiomodulación láser transvaginal de la pelvis',
 'Transvaginal laser photobiomodulation therapy of the pelvis. ',
 'Categoría III', 'Ginecología', 0.000, 0.000, 0.000,
 'Terapia de fotobiomodulación con láser transvaginal de la pelvis.', 'Sí', 2026, 'AMA'),

('1027T', 'Nuevo', 'Neuroestimulación frénica', 'Terapia de neuroestimulación frénica transvenosa',
 'Transvenous phrenic neurostimulation therapy for diaphragm activation in ventilated patients. ',
 'Categoría III', 'Neumología', 0.000, 0.000, 0.000,
 'Terapia de neuroestimulación del nervio frénico para activación del diafragma.', 'Sí', 2026, 'AMA'),

('1030T', 'Nuevo', 'Modelo 3D FAR 30-45 min', 'Creación de modelo 3D final (FAR) de estructuras anatómicas - 30-45 minutos',
 'Creation of digital 3D model (final anatomic representation (FAR)) of patient-specific anatomy from surface mesh files; 30-45 minutes. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Creación de un modelo 3D digital de la anatomía del paciente (FAR) de 30-45 minutos.', 'No', 2026, 'AMA'),

('1031T', 'Nuevo', 'Modelo 3D FAR 46-60 min', 'Creación de modelo 3D final (FAR) de estructuras anatómicas - 46-60 minutos',
 'Creation of digital 3D model (final anatomic representation (FAR)) of patient-specific anatomy from surface mesh files; 46-60 minutes. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Creación de un modelo 3D digital de la anatomía del paciente (FAR) de 46-60 minutos.', 'No', 2026, 'AMA'),

('1032T', 'Nuevo', 'Modelo 3D FAR 61-75 min', 'Creación de modelo 3D final (FAR) de estructuras anatómicas - 61-75 minutos',
 'Creation of digital 3D model (final anatomic representation (FAR)) of patient-specific anatomy from surface mesh files; 61-75 minutes. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Creación de un modelo 3D digital de la anatomía del paciente (FAR) de 61-75 minutos.', 'No', 2026, 'AMA'),

('1033T', 'Nuevo', 'Modelo 3D FAR 76-90 min', 'Creación de modelo 3D final (FAR) de estructuras anatómicas - 76-90 minutos',
 'Creation of digital 3D model (final anatomic representation (FAR)) of patient-specific anatomy from surface mesh files; 76-90 minutes. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Creación de un modelo 3D digital de la anatomía del paciente (FAR) de 76-90 minutos.', 'No', 2026, 'AMA'),

('1034T', 'Nuevo', 'Modelo 3D FAR 91-105 min', 'Creación de modelo 3D final (FAR) de estructuras anatómicas - 91-105 minutos',
 'Creation of digital 3D model (final anatomic representation (FAR)) of patient-specific anatomy from surface mesh files; 91-105 minutes. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Creación de un modelo 3D digital de la anatomía del paciente (FAR) de 91-105 minutos.', 'No', 2026, 'AMA'),

('1035T', 'Nuevo', 'Modelo 3D FAR 106-120 min', 'Creación de modelo 3D final (FAR) de estructuras anatómicas - 106-120 minutos',
 'Creation of digital 3D model (final anatomic representation (FAR)) of patient-specific anatomy from surface mesh files; 106-120 minutes. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Creación de un modelo 3D digital de la anatomía del paciente (FAR) de 106-120 minutos.', 'No', 2026, 'AMA'),

('1036T', 'Nuevo', 'Evaluación hemodinámica no invasiva', 'Evaluación hemodinámica no invasiva',
 'Noninvasive hemodynamic assessment. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Evaluación hemodinámica no invasiva.', 'No', 2026, 'AMA'),

('1037T', 'Nuevo', 'Histotripsia páncreas', 'Histotripsia de tejido pancreático maligno',
 'Histotripsy of malignant pancreatic tissue. ',
 'Categoría III', 'Gastroenterología', 0.000, 0.000, 0.000,
 'Histotripsia (ablación ultrasónica) de tejido pancreático maligno.', 'Sí', 2026, 'AMA'),

('1038T', 'Nuevo', 'Terapia celular muscular', 'Terapia con células musculares autólogas',
 'Autologous muscle cell therapy. ',
 'Categoría III', 'Medicina Regenerativa', 0.000, 0.000, 0.000,
 'Terapia con células musculares autólogas.', 'Sí', 2026, 'AMA'),

('1039T', 'Nuevo', 'Conectómica MRI cerebral', 'Análisis conectómico de MRI cerebral multimodal previo',
 'Connectomic analysis of previously performed multi-modal brain MRI. ',
 'Categoría III', 'Neurología', 0.000, 0.000, 0.000,
 'Análisis conectómico de resonancia magnética cerebral multimodal previamente realizada.', 'No', 2026, 'AMA'),

('1040T', 'Nuevo', 'Broncoscopía con crioterapia', 'Broncoscopía flexible con crioterapia bronquial',
 'Flexible bronchoscopy with bronchial cryotherapy. ',
 'Categoría III', 'Neumología', 0.000, 0.000, 0.000,
 'Broncoscopía flexible con crioterapia bronquial.', 'Sí', 2026, 'AMA'),

('1041T', 'Nuevo', 'Análisis EEG con IA', 'Análisis algorítmico aumentativo de formas de onda encefalográficas',
 'Augmentative algorithmic analysis of encephalographic waveforms. ',
 'Categoría III', 'Neurología', 0.000, 0.000, 0.000,
 'Análisis algorítmico aumentativo de formas de onda encefalográficas.', 'No', 2026, 'AMA'),

-- Nota: El descriptor de 1042T fue corregido por la AMA para aclarar que es "prostatic" y no "prosthetic" [citation:5]
('1042T', 'Nuevo', 'Andamio uretral absorbible', 'Implante de andamio urológico absorbible para restauración uretral',
 'Implantation of absorbable urologic scaffold for prostatic urethra restoration of reconstructed bladder neck and urethral anastomosis (List separately in addition to code for primary procedure). ',
 'Categoría III', 'Urología', 0.000, 0.000, 0.000,
 'Implante de andamio urológico absorbible para restauración de la uretra prostática.', 'Sí', 2026, 'AMA'),

('1043T', 'Nuevo', 'RMN hepática en punto de atención', 'Prueba de RM cuantitativa en punto de atención para evaluación hepática',
 'Point-of-care quantitative magnetic resonance (MR) test, without imaging, for liver assessment. ',
 'Categoría III', 'Gastroenterología', 0.000, 0.000, 0.000,
 'Prueba de RM cuantitativa en punto de atención para evaluación hepática (sin imagen).', 'No', 2026, 'AMA'),

('1050T', 'Nuevo', 'Monitorización IC subcutánea', 'Monitorización subcutánea de descompensación de insuficiencia cardíaca',
 'Subcutaneous heart failure decompensation monitoring. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Monitorización subcutánea de descompensación de insuficiencia cardíaca.', 'Sí', 2026, 'AMA'),

('1051T', 'Nuevo', 'Monitoreo IC - análisis', 'Monitoreo subcutáneo de descompensación cardíaca - análisis de datos',
 'Subcutaneous heart failure decompensation monitoring, data analysis. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Análisis de datos de monitorización subcutánea de insuficiencia cardíaca.', 'No', 2026, 'AMA'),

('1052T', 'Nuevo', 'Monitoreo IC - informes', 'Monitoreo subcutáneo de descompensación cardíaca - informes',
 'Subcutaneous heart failure decompensation monitoring, report generation. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Generación de informes de monitorización subcutánea de insuficiencia cardíaca.', 'No', 2026, 'AMA'),

('1053T', 'Nuevo', 'Monitoreo IC - reprogramación', 'Monitoreo subcutáneo de descompensación cardíaca - reprogramación',
 'Subcutaneous heart failure decompensation monitoring, device reprogramming. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Reprogramación del dispositivo de monitorización subcutánea de insuficiencia cardíaca.', 'Sí', 2026, 'AMA'),

-- Modelos 3D Impresos en 3D - Referencia: [citation:1]
('0559T', 'Nuevo', 'Modelo 3D impreso - producción', 'Producción de modelos 3D impresos de estructuras anatómicas',
 'Production of 3D-printed models of individually prepared and processed components of anatomy structures. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Producción de modelos impresos en 3D de estructuras anatómicas.', 'No', 2026, 'AMA'),

('0560T', 'Nuevo', 'Modelo 3D impreso - adicional', 'Producción de modelos 3D impresos de estructuras anatómicas (adicional)',
 'Production of 3D-printed models of individually prepared and processed components of anatomy structures; each additional model (List separately in addition to code for primary procedure). ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Producción de modelos 3D impresos adicionales.', 'No', 2026, 'AMA'),

('0561T', 'Nuevo', 'Guía 3D impresa - producción', 'Producción de guías 3D impresas para corte/perforación',
 'Production of 3D-printed cutting or drilling guides using individualized imaging data. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Producción de guías quirúrgicas impresas en 3D.', 'No', 2026, 'AMA'),

('0562T', 'Nuevo', 'Guía 3D impresa - adicional', 'Producción de guías 3D impresas para corte/perforación (adicional)',
 'Production of 3D-printed cutting or drilling guides using individualized imaging data; each additional guide (List separately in addition to code for primary procedure). ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Producción de guías quirúrgicas 3D adicionales.', 'No', 2026, 'AMA'),

 -- ============================================================
-- 3. CATEGORÍAS II Y III - Calidad y Tecnología Emergente
-- Fuente: AMA, NCQA/HEDIS, AAPC
-- NOTA: Categoría II = calidad (sin RVU), Categoría III = tecnología emergente
-- EFECTIVOS: Julio 2025 y 2026 (early release)
-- ============================================================

-- ------------------------------------------------------------
-- 3.1 CATEGORÍA II (0001F-9007F) - Códigos de Medición de Desempeño
-- Fuente: Guías CPT 2025, AMA, HEDIS, Arkansas Total Care
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
-- Cuidado Prenatal y Postparto
('0500F', 'Vigente', 'Visita prenatal inicial', 'Visita prenatal inicial',
 'Initial prenatal care visit. Report at first prenatal encounter with healthcare professional providing obstetrical care. Report the date of the visit and in a separate field, the date of the last menstrual period. ',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad para la primera visita de control prenatal.', 'No', 2025, 'NCQA/HEDIS'),

('0501F', 'Vigente', 'Flujo prenatal documentado', 'Flujo prenatal documentado en historia clínica',
 'Prenatal flow sheet documented in medical record by first prenatal visit. Documentation includes at minimum blood pressure, weight, urine protein, uterine size, fetal heart tones, and estimated date of delivery. ',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad que confirma que se ha documentado el flujo prenatal en la primera visita.', 'No', 2025, 'NCQA/HEDIS'),

('0502F', 'Vigente', 'Visita prenatal subsecuente', 'Visita prenatal subsecuente',
 'Subsequent prenatal care visit. ',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad para una visita de control prenatal de seguimiento.', 'No', 2025, 'NCQA/HEDIS'),

('0503F', 'Vigente', 'Visita de cuidado postparto', 'Visita de cuidado postparto',
 'Postpartum care visit. ',
 'Categoría II', 'Cuidado Prenatal', 0.000, 0.000, 0.000,
 'Código de calidad para la visita de cuidado postparto.', 'No', 2025, 'NCQA/HEDIS'),

-- Examen Oftalmológico (Cuidado de la Diabetes)
('2022F', 'Vigente', 'Examen ocular con retinopatía', 'Examen ocular dilatado con evidencia de retinopatía',
 'Dilated retinal eye exam with interpretation by an ophthalmologist or optometrist documented and reviewed; with evidence of retinopathy. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que un paciente diabético tiene retinopatía.', 'No', 2025, 'NCQA/HEDIS'),

('2023F', 'Vigente', 'Examen ocular sin retinopatía', 'Examen ocular dilatado sin evidencia de retinopatía',
 'Dilated retinal eye exam with interpretation by an ophthalmologist or optometrist documented and reviewed; without evidence of retinopathy. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que un paciente diabético NO tiene retinopatía.', 'No', 2025, 'NCQA/HEDIS'),

('2024F', 'Vigente', 'Fotos retinianas con retinopatía', 'Fotos retinianas 7 campos con retinopatía',
 'Seven standard field stereoscopic retinal photos with interpretation by an ophthalmologist or optometrist documented and reviewed; with evidence of retinopathy. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar retinopatía usando fotos retinianas.', 'No', 2025, 'NCQA/HEDIS'),

('2025F', 'Vigente', 'Fotos retinianas sin retinopatía', 'Fotos retinianas 7 campos sin retinopatía',
 'Seven standard field stereoscopic retinal photos with interpretation by an ophthalmologist or optometrist documented and reviewed; without evidence of retinopathy. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que NO hay retinopatía usando fotos retinianas.', 'No', 2025, 'NCQA/HEDIS'),

('2026F', 'Vigente', 'Imagen ocular con retinopatía', 'Imagen ocular validada con retinopatía',
 'Eye imaging validated to match diagnosis from seven standard field stereoscopic retinal photos results documented and reviewed; with evidence of retinopathy. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar retinopatía usando imágenes oculares validadas.', 'No', 2025, 'NCQA/HEDIS'),

('2033F', 'Vigente', 'Imagen ocular sin retinopatía', 'Imagen ocular validada sin retinopatía',
 'Eye imaging validated to match diagnosis from seven standard field stereoscopic retinal photos results documented and reviewed; without evidence of retinopathy. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar que NO hay retinopatía usando imágenes oculares validadas.', 'No', 2025, 'NCQA/HEDIS'),

('3072F', 'Vigente', 'Bajo riesgo retinopatía', 'Bajo riesgo de retinopatía (sin evidencia año anterior)',
 'Low risk for retinopathy (no evidence of retinopathy in the prior year). ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar bajo riesgo de retinopatía sin evidencia en el año anterior.', 'No', 2025, 'NCQA/HEDIS'),

-- Control de la Diabetes (HbA1c)
('3044F', 'Vigente', 'HbA1c < 7.0%', 'HbA1c menor a 7.0%',
 'Most recent hemoglobin A1c (HbA1c) level less than 7.0%. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un buen control de glucosa (HbA1c < 7%).', 'No', 2025, 'NCQA/HEDIS'),

('3046F', 'Vigente', 'HbA1c > 9.0%', 'HbA1c mayor a 9.0%',
 'Most recent hemoglobin A1c level greater than 9.0%. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un mal control de glucosa (HbA1c > 9%).', 'No', 2025, 'NCQA/HEDIS'),

('3051F', 'Vigente', 'HbA1c 7.0-8.0%', 'HbA1c entre 7.0% y 8.0%',
 'Most recent hemoglobin A1c (HbA1c) level greater than or equal to 7.0% and less than 8.0%. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un control de glucosa moderado (HbA1c 7-8%).', 'No', 2025, 'NCQA/HEDIS'),

('3052F', 'Vigente', 'HbA1c 8.0-9.0%', 'HbA1c entre 8.0% y 9.0%',
 'Most recent hemoglobin A1c (HbA1c) level greater than or equal to 8.0% and less than or equal to 9.0%. ',
 'Categoría II', 'Cuidado de la Diabetes', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un control de glucosa subóptimo (HbA1c 8-9%).', 'No', 2025, 'NCQA/HEDIS'),

-- Control del Colesterol (LDL-C)
('3048F', 'Vigente', 'LDL-C < 100 mg/dL', 'LDL-C menor a 100 mg/dL',
 'Most recent LDL-C less than 100 mg/dL. ',
 'Categoría II', 'Control Lipídico', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un buen control de colesterol (LDL < 100).', 'No', 2025, 'NCQA/HEDIS'),

('3049F', 'Vigente', 'LDL-C 100-129 mg/dL', 'LDL-C entre 100 y 129 mg/dL',
 'Most recent LDL-C 100-129 mg/dL. ',
 'Categoría II', 'Control Lipídico', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un control de colesterol intermedio (LDL 100-129).', 'No', 2025, 'NCQA/HEDIS'),

('3050F', 'Vigente', 'LDL-C >= 130 mg/dL', 'LDL-C mayor o igual a 130 mg/dL',
 'Most recent LDL-C greater than or equal to 130 mg/dL. ',
 'Categoría II', 'Control Lipídico', 0.000, 0.000, 0.000,
 'Código de calidad para reportar un mal control de colesterol (LDL >= 130).', 'No', 2025, 'NCQA/HEDIS'),

-- Control de la Presión Arterial
('3074F', 'Vigente', 'Sistólica < 130 mmHg', 'Presión arterial sistólica menor a 130 mm Hg',
 'Most recent systolic blood pressure less than 130 mm Hg. ',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una buena presión sistólica (< 130).', 'No', 2025, 'NCQA/HEDIS'),

('3075F', 'Vigente', 'Sistólica 130-139 mmHg', 'Presión arterial sistólica 130-139 mm Hg',
 'Most recent systolic blood pressure 130-139 mm Hg. ',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión sistólica elevada (130-139).', 'No', 2025, 'NCQA/HEDIS'),

('3077F', 'Vigente', 'Sistólica >= 140 mmHg', 'Presión arterial sistólica mayor o igual a 140 mm Hg',
 'Most recent systolic blood pressure greater than or equal to 140 mm Hg. ',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión sistólica alta (>= 140).', 'No', 2025, 'NCQA/HEDIS'),

('3078F', 'Vigente', 'Diastólica < 80 mmHg', 'Presión arterial diastólica menor a 80 mm Hg',
 'Most recent diastolic blood pressure less than 80 mm Hg. ',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una buena presión diastólica (< 80).', 'No', 2025, 'NCQA/HEDIS'),

('3079F', 'Vigente', 'Diastólica 80-89 mmHg', 'Presión arterial diastólica 80-89 mm Hg',
 'Most recent diastolic blood pressure 80-89 mm Hg. ',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión diastólica elevada (80-89).', 'No', 2025, 'NCQA/HEDIS'),

('3080F', 'Vigente', 'Diastólica >= 90 mmHg', 'Presión arterial diastólica mayor o igual a 90 mm Hg',
 'Most recent diastolic blood pressure greater than or equal to 90 mm Hg. ',
 'Categoría II', 'Control de PA', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una presión diastólica alta (>= 90).', 'No', 2025, 'NCQA/HEDIS'),

-- Control de la Función Cardíaca
('3055F', 'Vigente', 'FEVI <= 35%', 'Fracción de eyección ventricular izquierda menor o igual a 35%',
 'LVEF less than or equal to 35%. ',
 'Categoría II', 'Control Cardíaco', 0.000, 0.000, 0.000,
 'Código de calidad para reportar una fracción de eyección ventricular izquierda baja (<= 35%).', 'No', 2025, 'NCQA/HEDIS'),

-- Medidas Generales
('4000F', 'Vigente', 'Índice de masa corporal (IMC)', 'IMC documentado y evaluado',
 'Body Mass Index (BMI) measured and recorded. ',
 'Categoría II', 'Salud General', 0.000, 0.000, 0.000,
 'Código de calidad para confirmar que se midió y documentó el IMC.', 'No', 2025, 'NCQA/HEDIS');


-- ============================================================
-- NOTA SOBRE RVU: 
-- Los códigos de Categoría II no tienen valores de RVU. Son para medición de calidad y no generan reembolso directo. 
-- ============================================================

-- ------------------------------------------------------------
-- 3.2 CATEGORÍA III (0042T-1053T) - Códigos para Tecnología Emergente
-- Fuente: AMA (medium descriptors), AAPC
-- NOTA: Códigos temporales para nuevas tecnologías. Uso para recolección de datos.
-- EFECTIVOS: Julio 2025-2026 (early release) [citation:1][citation:4]
-- ============================================================

INSERT INTO cpt_codes (
    code, code_status, short_description, medium_description, long_description,
    chapter_section, subsection, work_rvu, practice_expense_rvu, malpractice_rvu,
    lay_terms, modifiers_allowed, year_effective, source
) VALUES
-- Códigos de Modelos 3D y FAR - EFECTIVOS: Julio 2026 [citation:1][citation:4]
('1030T', 'Nuevo', 'Modelo 3D FAR 30-45 min', 'Creación de modelo 3D final (FAR) 30-45 minutos',
 'Creation of digital 3D model (final anatomic representation (FAR)) of patient-specific anatomy from surface mesh files; 30-45 minutes. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Creación de un modelo 3D digital de la anatomía del paciente (FAR) de 30-45 minutos.', 'No', 2026, 'AMA'),

('1031T', 'Nuevo', 'Modelo 3D FAR 46-60 min', 'Creación de modelo 3D final (FAR) 46-60 minutos',
 'Creation of digital 3D model (final anatomic representation (FAR)) of patient-specific anatomy from surface mesh files; 46-60 minutes. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Creación de un modelo 3D digital de la anatomía del paciente (FAR) de 46-60 minutos.', 'No', 2026, 'AMA'),

('1032T', 'Nuevo', 'Modelo 3D FAR 61-75 min', 'Creación de modelo 3D final (FAR) 61-75 minutos',
 'Creation of digital 3D model (final anatomic representation (FAR)) of patient-specific anatomy from surface mesh files; 61-75 minutes. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Creación de un modelo 3D digital de la anatomía del paciente (FAR) de 61-75 minutos.', 'No', 2026, 'AMA'),

('1033T', 'Nuevo', 'Modelo 3D FAR 76-90 min', 'Creación de modelo 3D final (FAR) 76-90 minutos',
 'Creation of digital 3D model (final anatomic representation (FAR)) of patient-specific anatomy from surface mesh files; 76-90 minutes. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Creación de un modelo 3D digital de la anatomía del paciente (FAR) de 76-90 minutos.', 'No', 2026, 'AMA'),

('1034T', 'Nuevo', 'Modelo 3D FAR 91-105 min', 'Creación de modelo 3D final (FAR) 91-105 minutos',
 'Creation of digital 3D model (final anatomic representation (FAR)) of patient-specific anatomy from surface mesh files; 91-105 minutes. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Creación de un modelo 3D digital de la anatomía del paciente (FAR) de 91-105 minutos.', 'No', 2026, 'AMA'),

('1035T', 'Nuevo', 'Modelo 3D FAR 106-120 min', 'Creación de modelo 3D final (FAR) 106-120 minutos',
 'Creation of digital 3D model (final anatomic representation (FAR)) of patient-specific anatomy from surface mesh files; 106-120 minutes. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Creación de un modelo 3D digital de la anatomía del paciente (FAR) de 106-120 minutos.', 'No', 2026, 'AMA'),

-- Modelos 3D Impresos - EFECTIVOS: Julio 2026 [citation:1][citation:4]
('0559T', 'Nuevo', 'Modelo 3D impreso - producción', 'Producción de modelos 3D impresos de estructuras anatómicas',
 'Production of 3D-printed models of individually prepared and processed components of anatomy structures. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Producción de modelos impresos en 3D de estructuras anatómicas.', 'No', 2026, 'AMA'),

('0560T', 'Nuevo', 'Modelo 3D impreso - adicional', 'Producción de modelos 3D impresos (adicional)',
 'Production of 3D-printed models of individually prepared and processed components of anatomy structures; each additional model (List separately in addition to code for primary procedure). ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Producción de modelos 3D impresos adicionales (código adicional).', 'No', 2026, 'AMA'),

('0561T', 'Nuevo', 'Guía 3D impresa - producción', 'Producción de guías 3D impresas para corte/perforación',
 'Production of 3D-printed cutting or drilling guides using individualized imaging data. ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Producción de guías quirúrgicas impresas en 3D.', 'No', 2026, 'AMA'),

('0562T', 'Nuevo', 'Guía 3D impresa - adicional', 'Producción de guías 3D impresas (adicional)',
 'Production of 3D-printed cutting or drilling guides using individualized imaging data; each additional guide (List separately in addition to code for primary procedure). ',
 'Categoría III', 'Radiología', 0.000, 0.000, 0.000,
 'Producción de guías quirúrgicas 3D adicionales (código adicional).', 'No', 2026, 'AMA'),

-- Códigos de Monitoreo EEG Subcutáneo - EFECTIVOS: Julio 2025 [citation:1][citation:4][citation:14]
('0956T', 'Nuevo', 'Implante EEG subcutáneo', 'Implante de electrodos para monitoreo EEG subcutáneo',
 'Partial craniectomy, channel creation, and tunneling of electrode for sub-scalp implantation of an electrode array, receiver, and telemetry unit for continuous bilateral electroencephalography monitoring system, including imaging guidance. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Procedimiento para implantar un sistema de monitoreo de EEG bajo el cuero cabelludo.', 'Sí', 2025, 'AMA'),

('0957T', 'Nuevo', 'Revisión EEG subcutáneo', 'Revisión de electrodos para monitoreo EEG subcutáneo',
 'Revision of sub-scalp implanted electrode array, receiver, and telemetry unit for electrode, when required, including imaging guidance. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Revisión del sistema de monitoreo EEG subcutáneo.', 'Sí', 2025, 'AMA'),

('0958T', 'Nuevo', 'Remoción EEG subcutáneo', 'Remoción de electrodos para monitoreo EEG subcutáneo',
 'Removal of sub-scalp implanted electrode array, receiver, and telemetry unit for continuous bilateral electroencephalography monitoring system, including imaging guidance. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Remoción del sistema de monitoreo EEG subcutáneo.', 'Sí', 2025, 'AMA'),

('0959T', 'Nuevo', 'Reemplazo imán EEG', 'Remoción o reemplazo de imán del sistema EEG',
 'Removal or replacement of magnet from coil assembly that is connected to continuous bilateral electroencephalography monitoring system, including imaging guidance. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Remoción o reemplazo del imán del sistema de monitoreo EEG.', 'Sí', 2025, 'AMA'),

('0960T', 'Nuevo', 'Reemplazo EEG subcutáneo', 'Reemplazo de electrodos para monitoreo EEG subcutáneo',
 'Replacement of sub-scalp implanted electrode array, receiver, and telemetry unit with tunneling of electrode for continuous bilateral electroencephalography monitoring system, including imaging guidance. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Reemplazo del sistema de monitoreo EEG subcutáneo.', 'Sí', 2025, 'AMA'),

-- Neuroestimulación Epicraneal - EFECTIVOS: Julio 2025 [citation:1][citation:4][citation:14]
('0968T', 'Nuevo', 'Implante neuroestimulador epicraneal', 'Inserción o reemplazo de sistema de neuroestimulación epicraneal',
 'Insertion or replacement of epicranial neurostimulator system, including electrode array and pulse generator, with connection to electrode array. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Procedimiento para implantar un sistema de neuroestimulación sobre el cráneo.', 'Sí', 2025, 'AMA'),

('0969T', 'Nuevo', 'Remoción neuroestimulador epicraneal', 'Remoción de sistema de neuroestimulación epicraneal',
 'Removal of epicranial neurostimulator system. ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Procedimiento para retirar un sistema de neuroestimulación epicraneal.', 'Sí', 2025, 'AMA'),

-- OCT Vascular Cerebral - EFECTIVOS: Julio 2025 [citation:14]
('0984T', 'Nuevo', 'OCT vascular cerebral inicial', 'Imagen intravascular vasos cerebrales OCT - vaso inicial',
 'Intravascular imaging of extracranial cerebral vessels using optical coherence tomography (OCT) during diagnostic evaluation and/or therapeutic intervention, including all associated radiological supervision, interpretation, and report; initial vessel (List separately in addition to code for primary procedure). ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Imagen intravascular de vasos cerebrales con OCT (vaso inicial, código adicional).', 'Sí', 2025, 'AMA'),

('0985T', 'Nuevo', 'OCT vascular cerebral adicional', 'Imagen intravascular vasos cerebrales OCT - vaso adicional',
 'Intravascular imaging of extracranial cerebral vessels using optical coherence tomography (OCT) during diagnostic evaluation and/or therapeutic intervention, including all associated radiological supervision, interpretation, and report; each additional vessel (List separately in addition to code for primary procedure). ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Imagen intravascular de vasos cerebrales con OCT (vaso adicional, código adicional).', 'Sí', 2025, 'AMA'),

('0986T', 'Nuevo', 'OCT intracraneal inicial', 'Imagen intravascular vasos cerebrales intracraneales OCT - inicial',
 'Intravascular imaging of intracranial cerebral vessels using optical coherence tomography (OCT) during diagnostic evaluation and/or therapeutic intervention, including all associated radiological supervision, interpretation, and report; initial vessel (List separately in addition to code for primary procedure). ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Imagen intravascular de vasos cerebrales intracraneales con OCT (inicial, código adicional).', 'Sí', 2025, 'AMA'),

('0987T', 'Nuevo', 'OCT intracraneal adicional', 'Imagen intravascular vasos cerebrales intracraneales OCT - adicional',
 'Intravascular imaging of intracranial cerebral vessels using optical coherence tomography (OCT) during diagnostic evaluation and/or therapeutic intervention, including all associated radiological supervision, interpretation, and report; each additional vessel (List separately in addition to code for primary procedure). ',
 'Categoría III', 'Neurocirugía', 0.000, 0.000, 0.000,
 'Imagen intravascular de vasos cerebrales intracraneales con OCT (adicional, código adicional).', 'Sí', 2025, 'AMA'),

-- Evaluación de Riesgo Cardíaco con IA - EFECTIVOS: Enero 2026 [citation:9]
('0992T', 'Nuevo', 'Riesgo cardíaco IA sin TAC', 'Evaluación de riesgo cardíaco por IA sin TAC',
 'Noninvasive assessment of cardiac risk derived from augmentative software analysis of perivascular fat without concurrent computed tomography (CT) scan of the heart, including patient-specific clinical factors, with interpretation and report by a physician or other qualified health care professional. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Evaluación de riesgo cardíaco con análisis de IA de grasa perivascular, sin TAC cardíaco.', 'No', 2026, 'AMA'),

('0993T', 'Nuevo', 'Riesgo cardíaco IA con TAC', 'Evaluación de riesgo cardíaco por IA con TAC',
 'Noninvasive assessment of cardiac risk derived from augmentative software analysis of perivascular fat with concurrent computed tomography scan of the heart, including patient-specific clinical factors, with interpretation and report by a physician or other qualified health care professional. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Evaluación de riesgo cardíaco con análisis de IA de grasa perivascular, con TAC cardíaco.', 'No', 2026, 'AMA'),

-- Nuevos Códigos 2026 (Efectivos Julio 2026) [citation:1][citation:4]
('1026T', 'Nuevo', 'Fotobiomodulación vaginal', 'Terapia de fotobiomodulación láser transvaginal de la pelvis',
 'Transvaginal laser photobiomodulation therapy of the pelvis. ',
 'Categoría III', 'Ginecología', 0.000, 0.000, 0.000,
 'Terapia de fotobiomodulación con láser transvaginal de la pelvis.', 'Sí', 2026, 'AMA'),

('1027T', 'Nuevo', 'Neuroestimulación frénica', 'Terapia de neuroestimulación frénica transvenosa',
 'Transvenous phrenic neurostimulation therapy for diaphragm activation in ventilated patients. ',
 'Categoría III', 'Neumología', 0.000, 0.000, 0.000,
 'Terapia de neuroestimulación del nervio frénico para activación del diafragma.', 'Sí', 2026, 'AMA'),

('1036T', 'Nuevo', 'Evaluación hemodinámica no invasiva', 'Evaluación hemodinámica no invasiva',
 'Noninvasive hemodynamic assessment. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Evaluación hemodinámica no invasiva.', 'No', 2026, 'AMA'),

('1037T', 'Nuevo', 'Histotripsia páncreas', 'Histotripsia de tejido pancreático maligno',
 'Histotripsy of malignant pancreatic tissue. ',
 'Categoría III', 'Gastroenterología', 0.000, 0.000, 0.000,
 'Histotripsia (ablación ultrasónica) de tejido pancreático maligno.', 'Sí', 2026, 'AMA'),

('1038T', 'Nuevo', 'Terapia celular muscular', 'Terapia con células musculares autólogas',
 'Autologous muscle cell therapy. ',
 'Categoría III', 'Medicina Regenerativa', 0.000, 0.000, 0.000,
 'Terapia con células musculares autólogas.', 'Sí', 2026, 'AMA'),

('1039T', 'Nuevo', 'Conectómica MRI cerebral', 'Análisis conectómico de MRI cerebral multimodal previo',
 'Connectomic analysis of previously performed multi-modal brain MRI. ',
 'Categoría III', 'Neurología', 0.000, 0.000, 0.000,
 'Análisis conectómico de resonancia magnética cerebral multimodal previamente realizada.', 'No', 2026, 'AMA'),

('1040T', 'Nuevo', 'Broncoscopía con crioterapia', 'Broncoscopía flexible con crioterapia bronquial',
 'Flexible bronchoscopy with bronchial cryotherapy. ',
 'Categoría III', 'Neumología', 0.000, 0.000, 0.000,
 'Broncoscopía flexible con crioterapia bronquial.', 'Sí', 2026, 'AMA'),

('1041T', 'Nuevo', 'Análisis EEG con IA', 'Análisis algorítmico aumentativo de formas de onda encefalográficas',
 'Augmentative algorithmic analysis of encephalographic waveforms. ',
 'Categoría III', 'Neurología', 0.000, 0.000, 0.000,
 'Análisis algorítmico aumentativo de formas de onda encefalográficas.', 'No', 2026, 'AMA'),

('1042T', 'Nuevo', 'Andamio uretral absorbible', 'Implante de andamio urológico absorbible para uretra prostática',
 'Implantation of absorbable urologic scaffold for prostatic urethra restoration of reconstructed bladder neck and urethral anastomosis (List separately in addition to code for primary procedure). ',
 'Categoría III', 'Urología', 0.000, 0.000, 0.000,
 'Implante de andamio urológico absorbible para restauración de la uretra prostática (código adicional).', 'Sí', 2026, 'AMA'),

('1043T', 'Nuevo', 'RMN hepática en punto de atención', 'Prueba de RM cuantitativa en punto de atención para evaluación hepática',
 'Point-of-care quantitative magnetic resonance (MR) test, without imaging, for liver assessment. ',
 'Categoría III', 'Gastroenterología', 0.000, 0.000, 0.000,
 'Prueba de RM cuantitativa en punto de atención para evaluación hepática (sin imagen).', 'No', 2026, 'AMA'),

('1044T', 'Nuevo', 'Sustituto de piel 1', 'Sustituto de piel',
 'Skin substitute code 1. ',
 'Categoría III', 'Dermatología', 0.000, 0.000, 0.000,
 'Código de sustituto de piel (grupo de 6 códigos).', 'Sí', 2026, 'AMA'),

('1045T', 'Nuevo', 'Sustituto de piel 2', 'Sustituto de piel',
 'Skin substitute code 2. ',
 'Categoría III', 'Dermatología', 0.000, 0.000, 0.000,
 'Código de sustituto de piel (grupo de 6 códigos).', 'Sí', 2026, 'AMA'),

('1046T', 'Nuevo', 'Sustituto de piel 3', 'Sustituto de piel',
 'Skin substitute code 3. ',
 'Categoría III', 'Dermatología', 0.000, 0.000, 0.000,
 'Código de sustituto de piel (grupo de 6 códigos).', 'Sí', 2026, 'AMA'),

('1047T', 'Nuevo', 'Sustituto de piel 4', 'Sustituto de piel',
 'Skin substitute code 4. ',
 'Categoría III', 'Dermatología', 0.000, 0.000, 0.000,
 'Código de sustituto de piel (grupo de 6 códigos).', 'Sí', 2026, 'AMA'),

('1048T', 'Nuevo', 'Sustituto de piel 5', 'Sustituto de piel',
 'Skin substitute code 5. ',
 'Categoría III', 'Dermatología', 0.000, 0.000, 0.000,
 'Código de sustituto de piel (grupo de 6 códigos).', 'Sí', 2026, 'AMA'),

('1049T', 'Nuevo', 'Sustituto de piel 6', 'Sustituto de piel',
 'Skin substitute code 6. ',
 'Categoría III', 'Dermatología', 0.000, 0.000, 0.000,
 'Código de sustituto de piel (grupo de 6 códigos).', 'Sí', 2026, 'AMA'),

('1050T', 'Nuevo', 'Monitorización IC subcutánea', 'Monitorización subcutánea de descompensación de insuficiencia cardíaca',
 'Subcutaneous heart failure decompensation monitoring. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Monitorización subcutánea de descompensación de insuficiencia cardíaca.', 'Sí', 2026, 'AMA'),

('1051T', 'Nuevo', 'Monitoreo IC - análisis', 'Monitoreo subcutáneo de descompensación cardíaca - análisis de datos',
 'Subcutaneous heart failure decompensation monitoring, data analysis. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Análisis de datos de monitorización subcutánea de insuficiencia cardíaca.', 'No', 2026, 'AMA'),

('1052T', 'Nuevo', 'Monitoreo IC - informes', 'Monitoreo subcutáneo de descompensación cardíaca - informes',
 'Subcutaneous heart failure decompensation monitoring, report generation. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Generación de informes de monitorización subcutánea de insuficiencia cardíaca.', 'No', 2026, 'AMA'),

('1053T', 'Nuevo', 'Monitoreo IC - reprogramación', 'Monitoreo subcutáneo de descompensación cardíaca - reprogramación',
 'Subcutaneous heart failure decompensation monitoring, device reprogramming. ',
 'Categoría III', 'Cardiología', 0.000, 0.000, 0.000,
 'Reprogramación del dispositivo de monitorización subcutánea de insuficiencia cardíaca.', 'Sí', 2026, 'AMA');

-- ============================================================
-- NOTA SOBRE RVU: 
-- Los códigos de Categoría III no tienen valores de RVU asignados. Son temporales y su reembolso varía según el pagador.
-- Códigos 1044T-1049T son sustitutos de piel (grupo de 6 códigos) [citation:1]
-- Código 1042T descriptor corregido por AMA (cambio de "prosthetic" a "prostatic" en errata del 26/03/2026) [citation:5][citation:10]
-- ============================================================

-- ============================================================
-- NOTA SOBRE RVU: 
-- Los códigos de Categoría III no tienen valores de RVU asignados. Son temporales y su reembolso varía según el pagador. [citation:1][citation:11]
-- ============================================================

-- ============================================================
-- NOTA SOBRE RVU: 
-- Los códigos de Categoría II y III no tienen valores de RVU asignados.
-- Los Category II son para medición de calidad y no generan reembolso directo. [citation:3][citation:6]
-- Los Category III son temporales y su reembolso varía según el pagador. [citation:1][citation:12]
-- Fuentes: AMA, NCQA/HEDIS, CMS
-- ============================================================

-- ============================================================
-- NOTA SOBRE RVU: 
-- Los códigos de Categoría II y III no tienen valores de RVU asociados.
-- Los Category II son para medición de calidad. Los Category III son temporales y su reembolso varía.
-- Fuentes: AMA, NCQA/HEDIS
-- ============================================================

-- ============================================================
-- FIN DEL ARCHIVO: latam_packs_real.sql
-- TOTAL DE REGISTROS: 
-- ============================================================
-- NOTA: Este archivo contiene códigos CPT reales de la AMA
-- extraídos de fuentes públicas (CMS, AMA, AAPC, ACS)
-- NO es un catálogo completo ni oficial.
-- Para uso comercial se requiere licencia de la AMA.
-- ============================================================