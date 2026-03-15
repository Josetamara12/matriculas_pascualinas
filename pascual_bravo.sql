CREATE DATABASE IF NOT EXISTS pascualino_db;
USE pascualino_db;

-- Usuarios del sistema (acceso al portal)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL
);

-- Programas académicos (carreras)
CREATE TABLE IF NOT EXISTS programas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL
);

-- Periodos académicos (semestres habilitados para matrícula)
CREATE TABLE IF NOT EXISTS periodos_academicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    anio INT NOT NULL,
    semestre TINYINT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('abierto', 'cerrado') NOT NULL DEFAULT 'abierto',
    UNIQUE KEY uk_periodo (anio, semestre)
);

-- Estudiantes (vinculados a un usuario)
CREATE TABLE IF NOT EXISTS estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    documento_tipo VARCHAR(10) NOT NULL DEFAULT 'CC',
    documento_numero VARCHAR(20) NOT NULL,
    programa_id INT NOT NULL,
    estado ENUM('activo', 'inactivo', 'egresado') NOT NULL DEFAULT 'activo',
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE RESTRICT
);

-- Materias (asignaturas ofertadas)
CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    creditos INT NOT NULL,
    cupos INT NOT NULL DEFAULT 0,
    programa_id INT NULL,
    FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE SET NULL
);

-- Matrículas (una por estudiante por periodo)
CREATE TABLE IF NOT EXISTS matriculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    periodo_id INT NOT NULL,
    fecha_matricula DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('confirmada', 'anulada') NOT NULL DEFAULT 'confirmada',
    total_creditos INT NOT NULL DEFAULT 0,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE RESTRICT,
    FOREIGN KEY (periodo_id) REFERENCES periodos_academicos(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_estudiante_periodo (estudiante_id, periodo_id)
);

-- Detalle de matrícula (materias inscritas en cada matrícula)
CREATE TABLE IF NOT EXISTS detalle_matricula (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula_id INT NOT NULL,
    materia_id INT NOT NULL,
    estado ENUM('inscrito', 'cancelado') NOT NULL DEFAULT 'inscrito',
    FOREIGN KEY (matricula_id) REFERENCES matriculas(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_matricula_materia (matricula_id, materia_id)
);

-- Índices útiles
CREATE INDEX idx_matriculas_estudiante ON matriculas(estudiante_id);
CREATE INDEX idx_matriculas_periodo ON matriculas(periodo_id);
CREATE INDEX idx_detalle_matricula_matricula ON detalle_matricula(matricula_id);

-- Datos iniciales: usuarios de prueba
INSERT INTO usuarios (usuario, password) VALUES
('pascual', '2024*'),
('maria', '2024*'),
('carlos', '2024*')
ON DUPLICATE KEY UPDATE usuario = usuario;

-- Si ya existía ING-SIST, actualizarlo a Ingeniería de Software
UPDATE programas SET codigo = 'ING-SOF', nombre = 'Ingeniería de Software' WHERE codigo = 'ING-SIST';

INSERT IGNORE INTO programas (codigo, nombre) VALUES
('ING-SOF', 'Ingeniería de Software'),
('ING-IND', 'Diseño Industrial');

INSERT INTO periodos_academicos (codigo, anio, semestre, fecha_inicio, fecha_fin, estado) VALUES
('2026-1', 2026, 1, '2026-01-15', '2026-06-15', 'abierto'),
('2025-2', 2025, 2, '2025-07-20', '2025-12-10', 'cerrado');

-- Estudiante de prueba vinculado al usuario 'pascual', programa Ingeniería de Software
INSERT IGNORE INTO estudiantes (usuario_id, codigo, nombres, apellidos, documento_tipo, documento_numero, programa_id)
SELECT u.id, '20201001234', 'Pascual', 'Bravo', 'CC', '12345678', (SELECT id FROM programas WHERE codigo = 'ING-SOF' LIMIT 1)
FROM usuarios u WHERE u.usuario = 'pascual' LIMIT 1;

-- Asegurar que el estudiante pascual esté en Ingeniería de Software (no en Diseño Industrial)
UPDATE estudiantes e
JOIN usuarios u ON e.usuario_id = u.id
JOIN programas p ON p.codigo = 'ING-SOF'
SET e.programa_id = p.id
WHERE u.usuario = 'pascual';

-- Estudiante de prueba: Diseño Industrial (usuario maria)
INSERT IGNORE INTO estudiantes (usuario_id, codigo, nombres, apellidos, documento_tipo, documento_numero, programa_id)
SELECT u.id, '20202005678', 'María', 'García', 'CC', '87654321', (SELECT id FROM programas WHERE codigo = 'ING-IND' LIMIT 1)
FROM usuarios u WHERE u.usuario = 'maria' LIMIT 1;

-- Estudiante de prueba: Ingeniería de Software (usuario carlos)
INSERT IGNORE INTO estudiantes (usuario_id, codigo, nombres, apellidos, documento_tipo, documento_numero, programa_id)
SELECT u.id, '20203007890', 'Carlos', 'López', 'CC', '11223344', (SELECT id FROM programas WHERE codigo = 'ING-SOF' LIMIT 1)
FROM usuarios u WHERE u.usuario = 'carlos' LIMIT 1;

INSERT INTO materias (codigo, nombre, creditos, cupos, programa_id) VALUES 
('CAL-DIF', 'Cálculo Diferencial', 4, 20, 1),
('PRU-SOF', 'Pruebas de Software', 3, 15, 1),
('HER-PRO3', 'Herramientas de Programación III', 4, 10, 1),
('BD-I', 'Base de Datos I', 3, 0, 1),
('ETICA', 'Ética Profesional', 2, 30, NULL),
('ING-SOF1', 'Ingeniería de Software I', 4, 25, 1),

('ALG-LIN', 'Álgebra Lineal', 4, 15, 1),
('FIS-I', 'Física I', 4, 20, 1),
('PROG-I', 'Programación I', 3, 25, 1),
('COM-ESC', 'Comunicación Escrita', 2, 30, NULL),
('EST-I', 'Estadística I', 3, 20, 1),
('REDES-I', 'Redes de Computadores', 4, 18, 1),
('SIST-OP', 'Sistemas Operativos', 3, 15, 1),

-- Materias del programa Diseño Industrial (programa_id = 2, ING-IND)
('DIB-BAS', 'Diseño Básico', 3, 18, (SELECT id FROM programas WHERE codigo = 'ING-IND' LIMIT 1)),
('ERGON', 'Ergonomía', 3, 15, (SELECT id FROM programas WHERE codigo = 'ING-IND' LIMIT 1)),
('MAT-IND', 'Materiales para Diseño', 4, 12, (SELECT id FROM programas WHERE codigo = 'ING-IND' LIMIT 1)),
('DIB-TEC', 'Dibujo Técnico', 4, 20, (SELECT id FROM programas WHERE codigo = 'ING-IND' LIMIT 1)),
('PROD-IND', 'Procesos de Producción', 3, 15, (SELECT id FROM programas WHERE codigo = 'ING-IND' LIMIT 1)),
('HIST-DIS', 'Historia del Diseño', 2, 25, (SELECT id FROM programas WHERE codigo = 'ING-IND' LIMIT 1)),
('SEMIOT', 'Semiótica y Comunicación Visual', 3, 18, (SELECT id FROM programas WHERE codigo = 'ING-IND' LIMIT 1));
