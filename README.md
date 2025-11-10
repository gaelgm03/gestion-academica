# 🎓 Sistema de Gestión Académica

Plataforma web modular para centralizar la administración docente, gestión de incidencias y reportes académicos.

## 📋 Descripción del Proyecto

Sistema desarrollado para la Secretaría Académica que permite:
- Centralizar información de la base de datos docente
- Gestionar tickets e incidencias académicas
- Generar reportes y dashboards para la dirección

## 🛠️ Stack Tecnológico

### Frontend
- **Framework:** AngularJS
- **Lenguaje:** TypeScript
- **Estilos:** CSS/SCSS

### Backend
- **Lenguaje:** PHP 8.x+
- **API:** RESTful
- **Autenticación:** LDAP Institucional

### Base de Datos
- **Motor:** MySQL / MSSQL
- **ORM:** PDO

## 📦 Módulos del Sistema (MVP)

### 1. Base de Datos Docente 👨‍🏫
**Objetivo:** Piedra angular del sistema

**Funcionalidades:**
- Perfil único del profesor (CV, grados académicos, idiomas, SNI)
- Historial de clases y evaluaciones
- Registro de incidencias
- Numeralia en tiempo real (profesores activos/inactivos, estadísticas)

### 2. Tickets & Incidencias 🎫
**Objetivo:** Formalizar la gestión de reportes

**Categorías:**
- Cambios de calificación
- Cambios en fecha de examen
- Reportes de integridad académica
- Reporte disciplinar a profesor
- Incidencias de pago (a favor/en contra)

**Funcionalidades:**
- Sistema de prioridades
- Asignación de responsables
- Trazabilidad completa
- Adjuntar evidencias

### 3. Dashboards & Reportes 📊
**Objetivo:** Visualización ejecutiva de datos

**Reportes Académicos:**
- Por materia
- Por grado investigador SNI
- Por número de incidencias
- Por satisfacción académica (evaluación docente)

**KPIs para Dirección:**
- Filtros por periodo (semanal/mensual)
- Exportación (CSV, XLSX, PDF)

## 🚀 Instalación y Configuración

### Prerrequisitos
- Node.js 16+ y npm
- PHP 8.0+
- MySQL 8.0+ o MSSQL Server
- Git

### 1. Clonar el repositorio
```bash
git clone 
cd gestion-academica
```

### 2. Configurar Backend
```bash
cd backend
cp .env.example .env
# Editar .env con tus credenciales de BD y LDAP
```

### 3. Configurar Frontend
```bash
cd frontend
npm install
ng serve
```

### 4. Base de Datos
```bash
cd database
mysql -u root -p < schema.sql
# O importar desde tu gestor favorito (phpMyAdmin, DBeaver, etc.)
```

## 👥 Sistema de Roles y Permisos

| Rol | Permisos |
|-----|----------|
| **Admin** | Acceso completo al sistema |
| **Secretario Académico** | Gestión de docentes, incidencias y reportes |
| **Jefe de Academia** | Ver docentes de su academia, crear incidencias |
| **Director** | Acceso a dashboards y reportes ejecutivos |
| **Profesor** | Ver su perfil, responder incidencias asignadas |


## 👨‍💻 Equipo de Desarrollo

- **Desarrollador 1:** Gael Guzmán - Frontend
- **Desarrollador 2:** Rogelio Nava - Backend
- **Desarrollador 3:** Antonio Annese - Base de Datos