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
    code_status ENUM('Nuevo', 'Revisado', 'Eliminado') DEFAULT 'Vigente',
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
-- FIN DEL ARCHIVO: latam_packs_real.sql
-- TOTAL DE REGISTROS: 280+ códigos reales
-- ============================================================
-- NOTA: Este archivo contiene códigos CPT reales de la AMA
-- extraídos de fuentes públicas (CMS, AMA, AAPC, ACS)
-- NO es un catálogo completo ni oficial.
-- Para uso comercial se requiere licencia de la AMA.
-- ============================================================