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
('direccion');

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
('daniel.rosas@up.edu.mx', 'Daniel Rosas', 3);

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
('reporte', 'exportar');

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
(6, 'Licenciatura en Arte', 'Inglés', 0, 'https://cvup.mx/daniel-rosas', 'activo');

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
('Arte y Humanidades');

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
('Aclaración de calificación', 6, 'Historia del Arte', 'Alta', '24h', 3, 'evidencia_historia.pdf', 'cerrado');

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
-- FIN DEL DUMP
-- ============================================================

