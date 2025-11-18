-- ============================================================
--  Proyecto Final: Gestión Académica
--  Estructura: Usuario, Rol, Permiso, Docente, Academia, Incidencia
--  Fecha: Noviembre 2025
-- ============================================================

CREATE DATABASE IF NOT EXISTS gestion_academica;
USE gestion_academica;

-- ============================================================
-- 1. Tabla ROL
-- ============================================================
CREATE TABLE rol (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO rol (nombre) VALUES
('admin'),
('academia'),
('direccion'),
('docente'),
('coordinador');

-- ============================================================
-- 2. Tabla USUARIO
-- ============================================================
CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    rol_id INT,
    FOREIGN KEY (rol_id) REFERENCES rol(id)
);

INSERT INTO usuario (email, nombre, rol_id) VALUES
('ana.lopez@up.edu.mx', 'Ana López', 1),
('carlos.jimenez@up.edu.mx', 'Carlos Jiménez', 2),
('sofia.torres@up.edu.mx', 'Sofía Torres', 3),
('marco.hernandez@up.edu.mx', 'Marco Hernández', 2),
('isabel.gomez@up.edu.mx', 'Isabel Gómez', 1),
('daniel.rosas@up.edu.mx', 'Daniel Rosas', 3),
('laura.martinez@up.edu.mx', 'Laura Martínez', 4),
('pedro.sanchez@up.edu.mx', 'Pedro Sánchez', 4),
('maria.rodriguez@up.edu.mx', 'María Rodríguez', 5),
('juan.perez@up.edu.mx', 'Juan Pérez', 4),
('carmen.diaz@up.edu.mx', 'Carmen Díaz', 2),
('roberto.garcia@up.edu.mx', 'Roberto García', 4),
('elena.morales@up.edu.mx', 'Elena Morales', 4),
('luis.vargas@up.edu.mx', 'Luis Vargas', 5),
('patricia.cruz@up.edu.mx', 'Patricia Cruz', 4),
('fernando.ruiz@up.edu.mx', 'Fernando Ruiz', 3),
('diana.castillo@up.edu.mx', 'Diana Castillo', 4),
('jorge.mendoza@up.edu.mx', 'Jorge Mendoza', 4),
('andrea.luna@up.edu.mx', 'Andrea Luna', 2),
('miguel.reyes@up.edu.mx', 'Miguel Reyes', 4);

-- ============================================================
-- 3. Tabla PERMISO
-- ============================================================
CREATE TABLE permiso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scope VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL
);

INSERT INTO permiso (scope, action) VALUES
('docente', 'crear'),
('docente', 'editar'),
('docente', 'eliminar'),
('docente', 'ver'),
('incidencia', 'registrar'),
('incidencia', 'actualizar'),
('incidencia', 'eliminar'),
('incidencia', 'ver'),
('reporte', 'exportar'),
('reporte', 'ver'),
('academia', 'gestionar'),
('usuario', 'gestionar'),
('rol', 'asignar');

-- ============================================================
-- 3B. Tabla ROL_PERMISO (Relación muchos a muchos)
-- ============================================================
CREATE TABLE rol_permiso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    FOREIGN KEY (rol_id) REFERENCES rol(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permiso(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rol_permiso (rol_id, permiso_id)
);

-- Asignación de permisos a roles
INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
-- Admin: todos los permisos
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 11), (1, 12), (1, 13),
-- Academia: gestión de incidencias y reportes
(2, 4), (2, 5), (2, 6), (2, 8), (2, 9), (2, 10), (2, 11),
-- Dirección: ver todo y exportar reportes
(3, 4), (3, 8), (3, 9), (3, 10),
-- Docente: ver y registrar incidencias propias
(4, 4), (4, 5), (4, 8),
-- Coordinador: gestión completa de academia
(5, 1), (5, 2), (5, 4), (5, 5), (5, 6), (5, 8), (5, 9), (5, 10), (5, 11);

-- ============================================================
-- 4. Tabla DOCENTE
-- ============================================================
CREATE TABLE docente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    grados VARCHAR(100),
    idioma VARCHAR(50),
    sni BOOLEAN,
    cvlink VARCHAR(255),
    estatus ENUM('activo', 'inactivo') DEFAULT 'activo',
    FOREIGN KEY (id_usuario) REFERENCES usuario(id)
);

INSERT INTO docente (id_usuario, grados, idioma, sni, cvlink, estatus) VALUES
(1, 'Doctorado en Educación', 'Inglés', 1, 'https://cvup.mx/ana-lopez', 'activo'),
(2, 'Maestría en Sistemas', 'Francés', 0, 'https://cvup.mx/carlos-jimenez', 'activo'),
(3, 'Licenciatura en Psicología', 'Inglés', 0, 'https://cvup.mx/sofia-torres', 'activo'),
(4, 'Doctorado en Comunicación', 'Alemán', 1, 'https://cvup.mx/marco-hernandez', 'activo'),
(5, 'Maestría en Economía', 'Español', 0, 'https://cvup.mx/isabel-gomez', 'inactivo'),
(6, 'Licenciatura en Arte', 'Inglés', 0, 'https://cvup.mx/daniel-rosas', 'activo'),
(7, 'Doctorado en Ingeniería', 'Inglés', 1, 'https://cvup.mx/laura-martinez', 'activo'),
(8, 'Maestría en Matemáticas', 'Inglés', 0, 'https://cvup.mx/pedro-sanchez', 'activo'),
(10, 'Doctorado en Física', 'Italiano', 1, 'https://cvup.mx/juan-perez', 'activo'),
(12, 'Maestría en Química', 'Inglés', 0, 'https://cvup.mx/roberto-garcia', 'activo'),
(13, 'Licenciatura en Biología', 'Español', 0, 'https://cvup.mx/elena-morales', 'activo'),
(15, 'Doctorado en Literatura', 'Francés', 1, 'https://cvup.mx/patricia-cruz', 'activo'),
(17, 'Maestría en Filosofía', 'Inglés', 0, 'https://cvup.mx/diana-castillo', 'activo'),
(18, 'Licenciatura en Historia', 'Español', 0, 'https://cvup.mx/jorge-mendoza', 'activo'),
(20, 'Doctorado en Sociología', 'Portugués', 1, 'https://cvup.mx/miguel-reyes', 'activo');

-- ============================================================
-- 5. Tabla ACADEMIA
-- ============================================================
CREATE TABLE academia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

INSERT INTO academia (nombre) VALUES
('Ingeniería'),
('Psicología'),
('Comunicación'),
('Economía'),
('Arte y Humanidades'),
('Ciencias Exactas'),
('Ciencias Naturales'),
('Ciencias Sociales'),
('Idiomas'),
('Negocios y Administración');

-- ============================================================
-- 5B. Tabla DOCENTE_ACADEMIA (Relación muchos a muchos)
-- ============================================================
CREATE TABLE docente_academia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    academia_id INT NOT NULL,
    fecha_asignacion DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (docente_id) REFERENCES docente(id) ON DELETE CASCADE,
    FOREIGN KEY (academia_id) REFERENCES academia(id) ON DELETE CASCADE,
    UNIQUE KEY unique_docente_academia (docente_id, academia_id)
);

-- Asignación de docentes a academias
INSERT INTO docente_academia (docente_id, academia_id) VALUES
-- Ana López: Ingeniería y Ciencias Exactas
(1, 1), (1, 6),
-- Carlos Jiménez: Ingeniería
(2, 1),
-- Sofía Torres: Psicología y Ciencias Sociales
(3, 2), (3, 8),
-- Marco Hernández: Comunicación
(4, 3),
-- Isabel Gómez: Economía y Negocios
(5, 4), (5, 10),
-- Daniel Rosas: Arte y Humanidades
(6, 5),
-- Laura Martínez: Ingeniería
(7, 1),
-- Pedro Sánchez: Ciencias Exactas
(8, 6),
-- Juan Pérez: Ciencias Exactas
(9, 6),
-- Roberto García: Ciencias Naturales
(10, 7),
-- Elena Morales: Ciencias Naturales
(11, 7),
-- Patricia Cruz: Arte y Humanidades e Idiomas
(12, 5), (12, 9),
-- Diana Castillo: Arte y Humanidades
(13, 5),
-- Jorge Mendoza: Ciencias Sociales
(14, 8),
-- Miguel Reyes: Ciencias Sociales y Psicología
(15, 8), (15, 2);

-- ============================================================
-- 6. Tabla INCIDENCIA
-- ============================================================
CREATE TABLE incidencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(100) NOT NULL,
    profesor INT,
    curso VARCHAR(100),
    prioridad ENUM('Alta','Media','Baja') DEFAULT 'Media',
    sla VARCHAR(20),
    asignadoA INT,
    evidencias VARCHAR(255),
    status ENUM('abierto','en proceso','cerrado') DEFAULT 'abierto',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profesor) REFERENCES docente(id),
    FOREIGN KEY (asignadoA) REFERENCES usuario(id)
);

INSERT INTO incidencia (tipo, profesor, curso, prioridad, sla, asignadoA, evidencias, status) VALUES
('Cambio de fecha de examen', 1, 'Cálculo Integral', 'Alta', '48h', 2, 'evidencia1.pdf', 'abierto'),
('Reporte de integridad académica', 2, 'Bases de Datos', 'Media', '72h', 1, 'plagio_casoA.docx', 'en proceso'),
('Incidencia de pago pendiente', 3, 'Psicología General', 'Alta', '24h', 3, 'comprobante_pago.pdf', 'cerrado'),
('Cambio de profesor', 4, 'Comunicación Oral', 'Media', '48h', 2, 'solicitud_cambio.docx', 'abierto'),
('Reporte disciplinar', 5, 'Microeconomía', 'Baja', '72h', 1, 'nota_dis.pdf', 'en proceso'),
('Aclaración de calificación', 6, 'Historia del Arte', 'Alta', '24h', 3, 'evidencia_historia.pdf', 'cerrado'),
('Solicitud de material didáctico', 7, 'Programación Avanzada', 'Baja', '96h', 2, NULL, 'abierto'),
('Ausencia justificada', 8, 'Álgebra Lineal', 'Media', '48h', 9, 'justificante_medico.pdf', 'en proceso'),
('Cambio de horario', 9, 'Termodinámica', 'Alta', '24h', 1, 'solicitud_horario.docx', 'abierto'),
('Revisión de examen', 10, 'Química Orgánica', 'Media', '72h', 11, 'examen_revision.pdf', 'abierto'),
('Reporte de equipo dañado', 11, 'Biología Celular', 'Alta', '24h', 2, 'foto_equipo.jpg', 'en proceso'),
('Solicitud de prórroga', 12, 'Literatura Contemporánea', 'Baja', '96h', 9, NULL, 'cerrado'),
('Aclaración de asistencia', 13, 'Filosofía Moderna', 'Media', '48h', 1, 'lista_asistencia.pdf', 'abierto'),
('Cambio de aula', 14, 'Historia Universal', 'Alta', '24h', 16, 'solicitud_aula.docx', 'en proceso'),
('Reporte de plagio', 15, 'Antropología Social', 'Alta', '48h', 1, 'evidencia_plagio.pdf', 'abierto'),
('Solicitud de tutoría', 1, 'Cálculo Diferencial', 'Baja', '120h', 2, NULL, 'abierto'),
('Problema de acceso al sistema', 2, 'Estructuras de Datos', 'Alta', '24h', 1, 'captura_error.png', 'cerrado'),
('Solicitud de constancia', 3, 'Psicología Clínica', 'Media', '72h', 3, NULL, 'cerrado'),
('Reporte de acoso', 4, 'Redacción Periodística', 'Alta', '12h', 1, 'testimonio.docx', 'en proceso'),
('Solicitud de examen extraordinario', 7, 'Circuitos Eléctricos', 'Media', '96h', 2, 'solicitud_extra.pdf', 'abierto');

-- ============================================================
-- 7. VISTA DE DASHBOARD SIMPLE (para tus reportes PHP)
-- ============================================================
CREATE OR REPLACE VIEW vista_dashboard AS
SELECT
    COUNT(DISTINCT docente.id) AS total_docentes,
    SUM(CASE WHEN docente.sni = 1 THEN 1 ELSE 0 END) AS docentes_sni,
    SUM(CASE WHEN docente.estatus = 'activo' THEN 1 ELSE 0 END) AS docentes_activos,
    COUNT(incidencia.id) AS total_incidencias,
    SUM(CASE WHEN incidencia.status = 'abierto' THEN 1 ELSE 0 END) AS incidencias_abiertas
FROM docente
LEFT JOIN incidencia ON docente.id = incidencia.profesor;

-- ============================================================
-- 8. ÍNDICES PARA OPTIMIZACIÓN DE CONSULTAS
-- ============================================================
CREATE INDEX idx_usuario_email ON usuario(email);
CREATE INDEX idx_usuario_rol ON usuario(rol_id);
CREATE INDEX idx_docente_usuario ON docente(id_usuario);
CREATE INDEX idx_docente_estatus ON docente(estatus);
CREATE INDEX idx_incidencia_profesor ON incidencia(profesor);
CREATE INDEX idx_incidencia_asignado ON incidencia(asignadoA);
CREATE INDEX idx_incidencia_status ON incidencia(status);
CREATE INDEX idx_incidencia_prioridad ON incidencia(prioridad);
CREATE INDEX idx_incidencia_fecha ON incidencia(fecha_creacion);

-- ============================================================
-- FIN DEL DUMP
-- ============================================================

