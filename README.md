# 🎓 Sistema de Gestión Académica

> Plataforma modular diseñada para transformar la gestión administrativa de la Secretaría Académica, centralizando información docente, formalizando el manejo de incidencias y proporcionando herramientas de análisis para la toma de decisiones.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.x%2B-blue)](https://www.php.net/)
[![Angular](https://img.shields.io/badge/Angular-20.x-red)](https://angular.io/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-orange)](https://www.mysql.com/)

---

## 📑 Tabla de Contenidos

- [Contexto y Diagnóstico](#-contexto-y-diagnóstico)
- [La Solución](#-la-solución)
- [Arquitectura](#-arquitectura)
- [Módulos del Sistema](#-módulos-del-sistema)
- [Instalación](#-instalación-y-configuración)
- [Modelo de Datos](#-modelo-de-datos)
- [Roles y Permisos](#-sistema-de-roles-y-permisos)
- [Equipo](#-equipo-de-desarrollo)
- [Licencia](#-licencia)

---

## 🔍 Contexto y Diagnóstico

### Problemática Actual

La Secretaría Académica enfrenta desafíos críticos que afectan la eficiencia operativa:

#### 1. **Procesos Desestructurados**
- Gestión informal mediante correos electrónicos, WhatsApp y comunicación verbal
- Ausencia de protocolos uniformes para reportar incidencias y cambios
- Registros descentralizados por cada jefe de academia, causando duplicidad y errores

#### 2. **Información Fragmentada**
- No existe una base de datos centralizada de profesores (currículum, historial, incidencias, evaluaciones)
- Documentos clave dispersos sin control de versiones
- Obtención de numeralia básica requiere solicitudes individuales a cada responsable

#### 3. **Impacto Operativo**
- **Pérdida de tiempo:** Reportes manuales que deben recrearse constantemente
- **Errores recurrentes:** Acuerdos no comunicados, duplicidad en contrataciones, inconsistencias en pagos
- **Desgaste del equipo:** Exceso de mensajes informales y tareas repetitivas

#### 4. **Necesidad Urgente**
La Secretaría requiere un sistema que:
- ✅ Centralice la base de datos docente
- ✅ Formalice la gestión de incidencias
- ✅ Garantice comunicación estructurada
- ✅ Evite retrasos y errores en pagos y planeación académica

---

## 💡 La Solución

### Plataforma de Gestión Académica a la Medida

Sistema web modular diseñado específicamente para resolver los puntos de dolor identificados, con tres pilares fundamentales:

1. **Centralización de Información Docente** - Base de datos única y confiable
2. **Formalización de Incidencias** - Sistema de tickets con trazabilidad completa
3. **Inteligencia de Negocio** - Dashboards y reportes para la toma de decisiones

---

## 🏗️ Arquitectura

### Patrón Arquitectónico
**Modular Monolith** - Módulos bien separados en un mismo repositorio, facilitando el desarrollo inicial y permitiendo escalabilidad futura.

### Stack Tecnológico

#### **Frontend**
- **Framework:** Angular 20.x
- **Lenguaje:** TypeScript
- **UI/UX:** Diseño institucional con componentes modulares
- **Características:** Formularios reactivos, acceso basado en roles

#### **Backend (API)**
- **Lenguaje:** PHP 8.x+
- **Arquitectura:** RESTful API
- **Autenticación:** LDAP Institucional
- **Seguridad:** JWT tokens, validación de permisos por rol

#### **Base de Datos**
- **Motor:** MySQL 8.0+ / MSSQL Server
- **ORM:** PDO con prepared statements
- **Diseño:** Normalizado con integridad referencial

### Diagrama de Arquitectura

```
┌─────────────────┐
│   Frontend      │
│   (Angular)     │
└────────┬────────┘
         │ HTTP/REST
         │
┌────────▼────────┐      ┌─────────────┐
│   Backend API   │◄─────┤ LDAP Server │
│   (PHP 8.x)     │      │ (Auth)      │
└────────┬────────┘      └─────────────┘
         │
         │ PDO
         │
┌────────▼────────┐
│   MySQL/MSSQL   │
│   Database      │
└─────────────────┘
```

---

## 📦 Módulos del Sistema

### 1. 👨‍🏫 Base de Datos Docente
**Piedra angular del sistema**

**Funcionalidades:**
- **Perfil único del profesor:**
  - Currículum vitae
  - Grados académicos
  - Áreas de especialización
  - Idiomas
  - Sistema Nacional de Investigadores (SNI)
  - Historial de clases impartidas
  - Promedios de evaluación docente
  - Registro de incidencias

- **Numeralia en tiempo real:**
  - Profesores activos/inactivos
  - Distribución por grados académicos
  - Dominio de idiomas
  - Miembros SNI
  - Estadísticas consolidadas

- **Gestión centralizada:**
  - Tablero para listar y filtrar profesores
  - Búsqueda avanzada por múltiples criterios
  - Exportación de datos

### 2. 🎫 Tickets & Incidencias
**Formalización de la gestión de reportes**

**Categorías de Incidencias:**
- 📝 Cambios de calificación
- 📅 Cambios en fecha de examen
- ⚠️ Reportes de integridad académica
- 👤 Reporte disciplinar a profesor
- 💰 Incidencia de pago (a favor/en contra)

**Características:**
- Sistema de prioridades (alta, media, baja)
- Asignación de responsables
- Trazabilidad completa (historial de cambios)
- Adjuntar evidencias (documentos, imágenes)
- Filtros por profesor, categoría, fecha y prioridad
- SLA (Service Level Agreement) para seguimiento

### 3. 📊 Dashboards y Reportes
**Visualización ejecutiva e inteligencia de negocio**

**Reportes Académicos:**
- 📚 Por materia
- 🎓 Por grado investigador SNI
- 📋 Por número de incidencias
- ⭐ Por satisfacción académica (evaluación docente)

**KPIs para Dirección:**
- Filtros personalizables (período semanal/mensual/anual)
- Indicadores clave de desempeño
- Exportación múltiple (CSV, XLSX, PDF)
- Visualizaciones interactivas (gráficas, tablas dinámicas)

---

## 🚀 Instalación y Configuración

### Prerrequisitos

Asegúrate de tener instalado:
- **Node.js** 18+ y npm
- **PHP** 8.0+
- **MySQL** 8.0+ o **MSSQL Server**
- **Composer** (gestor de dependencias PHP)
- **Angular CLI** 20.x
- **Git**

### 1️⃣ Clonar el Repositorio

```bash
git clone https://github.com/gaelgm03/gestion-academica.git
cd gestion-academica
```

### 2️⃣ Configurar Base de Datos

```bash
cd database

# MySQL
mysql -u root -p < schema.sql

# O importar desde tu gestor favorito (phpMyAdmin, DBeaver, MySQL Workbench)
```

### 3️⃣ Configurar Backend

```bash
cd backend

# Instalar dependencias (si aplica)
composer install

# Configurar variables de entorno
cp .env.example .env

# Editar .env con tus credenciales:
# - Conexión a base de datos
# - Configuración LDAP
# - Secretos JWT
```

**Ejemplo `.env`:**
```env
DB_HOST=localhost
DB_NAME=gestion_academica
DB_USER=root
DB_PASS=tu_password

LDAP_HOST=ldap://tu-servidor-ldap
LDAP_PORT=389
LDAP_BASE_DN=dc=universidad,dc=edu,dc=mx

JWT_SECRET=tu_secreto_seguro_aqui
```

### 4️⃣ Configurar Frontend

```bash
cd frontend

# Instalar dependencias
npm install

# Configurar API endpoint
# Editar src/environments/environment.ts

# Modo desarrollo
ng serve

# La aplicación estará disponible en http://localhost:4200/
```

### 5️⃣ Iniciar el Backend

```bash
cd backend

# Opción 1: PHP Built-in Server (desarrollo)
php -S localhost:8000

# Opción 2: WAMP/XAMPP/MAMP
# Configurar Virtual Host apuntando a la carpeta backend

# Opción 3: Docker (si aplica)
docker-compose up
```

### 6️⃣ Verificar Instalación

1. Accede a `http://localhost:4200/`
2. Inicia sesión con credenciales LDAP institucionales
3. Verifica que los módulos carguen correctamente

---

## 🗄️ Modelo de Datos

### Entidades Principales

```
┌─────────────┐       ┌──────────────┐       ┌─────────────┐
│   Usuario   │───────│     Rol      │───────│   Permiso   │
├─────────────┤       ├──────────────┤       ├─────────────┤
│ id          │       │ id           │       │ id          │
│ email       │       │ nombre       │       │ scope       │
│ nombre      │       └──────────────┘       │ action      │
│ rol_id (FK) │                              └─────────────┘
└──────┬──────┘
       │
       │ 1:1
       │
┌──────▼──────┐       ┌──────────────┐       ┌─────────────┐
│   Docente   │───────│   Academia   │       │ Incidencia  │
├─────────────┤       ├──────────────┤       ├─────────────┤
│ id          │       │ id           │       │ id          │
│ usuario_id  │       │ nombre       │       │ tipo        │
│ grados      │       │ descripcion  │       │ profesor_id │
│ idioma      │       └──────────────┘       │ curso_id    │
│ sni         │                              │ prioridad   │
│ cv_link     │                              │ sla         │
│ estatus     │                              │ asignado_a  │
│ academia_id │                              │ evidencias  │
└─────────────┘                              │ status      │
                                             └─────────────┘
```

### Relaciones Clave

- **Usuario - Rol:** Muchos a uno (varios usuarios pueden tener el mismo rol)
- **Rol - Permiso:** Muchos a muchos (tabla intermedia `rol_permiso`)
- **Usuario - Docente:** Uno a uno (un usuario puede ser docente)
- **Docente - Academia:** Muchos a uno (varios docentes pertenecen a una academia)
- **Incidencia - Docente:** Muchos a uno (varias incidencias pueden estar asociadas a un docente)

---

## 👥 Sistema de Roles y Permisos

### Matriz de Permisos

| Rol | Docentes | Incidencias | Reportes | Usuarios | Academias |
|-----|----------|-------------|----------|----------|-----------|
| **Admin** | CRUD completo | CRUD completo | Ver y exportar | Gestionar | Gestionar |
| **Secretario Académico** | Ver, crear, editar | CRUD completo | Ver y exportar | - | Ver |
| **Jefe de Academia** | Ver (su academia) | Crear, ver | Ver (su academia) | - | Ver (su academia) |
| **Director** | Ver | Ver | Ver y exportar | - | Ver |
| **Docente** | Ver (propio perfil) | Ver (asignadas) | - | - | - |

### Permisos Detallados

**Admin:**
- Acceso total al sistema
- Gestión de usuarios y roles
- Configuración del sistema

**Secretario Académico:**
- Gestión completa de docentes
- Gestión completa de incidencias
- Generación de reportes
- Consulta de información de todas las academias

**Jefe de Academia:**
- Consulta de docentes de su academia
- Creación y seguimiento de incidencias
- Reportes de su academia

**Director:**
- Dashboards ejecutivos
- Reportes consolidados
- Exportación de información

**Docente:**
- Visualización de perfil propio
- Consulta de incidencias asignadas
- Actualización de información personal

---

## 👨‍💻 Equipo de Desarrollo

Este proyecto fue desarrollado por:

<table>
  <tr>
    <td align="center">
      <img src="https://github.com/gaelgm03.png?size=100" width="100px;" alt="Gael Guzmán"/><br />
      <sub><b>Gael Guzmán</b></sub><br />
      <sub>Frontend Developer</sub><br />
      <sub>Angular · TypeScript · UI/UX</sub>
    </td>
    <td align="center">
      <img src="https://github.com/rogelionava.png?size=100" width="100px;" alt="Rogelio Nava"/><br />
      <sub><b>Rogelio Nava</b></sub><br />
      <sub>Backend Developer</sub><br />
      <sub>PHP · API REST · LDAP</sub>
    </td>
    <td align="center">
      <img src="https://github.com/antonioannese.png?size=100" width="100px;" alt="Antonio Annese"/><br />
      <sub><b>Antonio Annese</b></sub><br />
      <sub>Database Engineer</sub><br />
      <sub>MySQL · Data Modeling</sub>
    </td>
  </tr>
</table>

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

---

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add: nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📞 Contacto

Para consultas o soporte, contacta al equipo de desarrollo a través de los issues de GitHub.

---

<div align="center">
  <p>Desarrollado con ❤️ por el equipo de Gestión Académica</p>
  <p>© 2025 - Universidad Panamericana</p>
</div>