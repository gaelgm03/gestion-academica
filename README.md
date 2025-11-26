<div align="center">

# 🎓 Sistema de Gestión Académica

**Plataforma web full-stack para la gestión integral de instituciones educativas**

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Angular](https://img.shields.io/badge/Angular-20-DD0031?style=for-the-badge&logo=angular&logoColor=white)](https://angular.io)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.x-3178C6?style=for-the-badge&logo=typescript&logoColor=white)](https://typescriptlang.org)

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](LICENSE)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)](https://github.com/gaelgm03/gestion-academica/pulls)

[Características](#-características) •
[Demo](#-demo) •
[Instalación](#-instalación-rápida) •
[Stack](#%EF%B8%8F-stack-tecnológico) •
[Equipo](#-equipo-de-desarrollo)

</div>

---

## ✨ Características

<table>
<tr>
<td width="50%">

### 👨‍🏫 Gestión de Docentes
- Perfiles completos (CV, grados, SNI, idiomas)
- Áreas de especialización con niveles
- Historial de cursos impartidos
- Estadísticas y métricas en tiempo real

</td>
<td width="50%">

### 🎫 Sistema de Tickets
- 5 categorías de incidencias predefinidas
- Prioridades y SLAs configurables
- Trazabilidad completa con historial
- Adjuntar evidencias (documentos/imágenes)

</td>
</tr>
<tr>
<td width="50%">

### 📊 Dashboards & Reportes
- KPIs ejecutivos con filtros temporales
- Gráficas interactivas (Chart.js)
- Exportación múltiple (CSV, XLSX, PDF)
- Reportes por materia, docente, academia

</td>
<td width="50%">

### ⭐ Evaluación Docente
- Criterios ponderados por categoría
- Evaluaciones por alumno/par/coordinador
- Promedios automáticos
- Historial de evaluaciones

</td>
</tr>
</table>

### 🔐 Seguridad & Autenticación
- **JWT** para autenticación stateless
- **LDAP** institucional integrado
- **5 roles** con permisos granulares (Admin, Academia, Dirección, Docente, Coordinador)
- Validación de permisos por scope/action

---

## 🖼️ Demo

<div align="center">

| Dashboard | Gestión Docentes | Sistema de Tickets |
|:---------:|:----------------:|:------------------:|
| ![Dashboard](https://via.placeholder.com/280x180/1a1a2e/ffffff?text=Dashboard) | ![Docentes](https://via.placeholder.com/280x180/16213e/ffffff?text=Docentes) | ![Tickets](https://via.placeholder.com/280x180/0f3460/ffffff?text=Tickets) |

</div>

---

## 🛠️ Stack Tecnológico

<div align="center">

| Frontend | Backend | Database | Tools |
|:--------:|:-------:|:--------:|:-----:|
| ![Angular](https://img.shields.io/badge/-Angular-DD0031?style=flat-square&logo=angular&logoColor=white) | ![PHP](https://img.shields.io/badge/-PHP%208-777BB4?style=flat-square&logo=php&logoColor=white) | ![MySQL](https://img.shields.io/badge/-MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white) | ![Git](https://img.shields.io/badge/-Git-F05032?style=flat-square&logo=git&logoColor=white) |
| ![TypeScript](https://img.shields.io/badge/-TypeScript-3178C6?style=flat-square&logo=typescript&logoColor=white) | ![JWT](https://img.shields.io/badge/-JWT-000000?style=flat-square&logo=jsonwebtokens&logoColor=white) | ![PDO](https://img.shields.io/badge/-PDO-777BB4?style=flat-square&logo=php&logoColor=white) | ![npm](https://img.shields.io/badge/-npm-CB3837?style=flat-square&logo=npm&logoColor=white) |
| ![Chart.js](https://img.shields.io/badge/-Chart.js-FF6384?style=flat-square&logo=chartdotjs&logoColor=white) | ![REST](https://img.shields.io/badge/-REST%20API-009688?style=flat-square&logo=fastapi&logoColor=white) | | |

</div>

---

## 🚀 Instalación Rápida

### Prerrequisitos

- Node.js 18+ & npm
- PHP 8.0+ con extensiones: PDO, pdo_mysql, json, mbstring, openssl
- MySQL 8.0+
- Angular CLI (`npm install -g @angular/cli`)

### 1. Clonar repositorio

```bash
git clone https://github.com/gaelgm03/gestion-academica.git
cd gestion-academica
```

### 2. Configurar Base de Datos

```bash
# Importar schema completo
mysql -u root -p < database/schema.sql
```

### 3. Configurar Backend

```bash
cd backend
cp .env.example .env
# Editar .env con tus credenciales
```

### 4. Iniciar Frontend

```bash
cd frontend
npm install
ng serve
```

### 5. ¡Listo!

Abre `http://localhost:4200` en tu navegador.

---

## 📁 Estructura del Proyecto

```
gestion-academica/
├── 📂 backend/
│   ├── 📂 api/           # Endpoints REST
│   ├── 📂 auth/          # JWT & LDAP handlers
│   ├── 📂 config/        # Configuración DB & env
│   ├── 📂 models/        # Modelos de datos
│   └── 📂 utils/         # Utilidades (XLSX export)
│
├── 📂 database/
│   └── 📄 schema.sql     # Schema completo (19 tablas)
│
├── 📂 frontend/
│   └── 📂 src/app/
│       ├── 📂 dashboard/     # KPIs y gráficas
│       ├── 📂 docentes/      # CRUD docentes
│       ├── 📂 incidencias/   # Sistema de tickets
│       ├── 📂 evaluaciones/  # Evaluación docente
│       ├── 📂 cursos/        # Gestión de materias
│       └── 📂 services/      # API & PDF services
│
└── 📄 README.md
```

---

## 📊 Modelo de Datos

El sistema cuenta con **19 tablas** organizadas en módulos:

| Módulo | Tablas |
|--------|--------|
| **Usuarios** | `rol`, `usuario`, `permiso`, `rol_permiso` |
| **Docentes** | `docente`, `academia`, `docente_academia`, `area_especialidad`, `docente_area_especialidad` |
| **Cursos** | `curso`, `docente_curso`, `periodo_academico` |
| **Incidencias** | `tipo_incidencia`, `incidencia`, `incidencia_historial` |
| **Evaluaciones** | `criterio_evaluacion`, `periodo_evaluacion`, `evaluacion_docente`, `evaluacion_detalle` |

---

## 🔑 API Endpoints

| Módulo | Endpoint | Métodos |
|--------|----------|---------|
| Auth | `/api/auth.php` | POST (login, refresh, logout) |
| Docentes | `/api/docentes.php` | GET, POST, PUT, DELETE |
| Incidencias | `/api/incidencias.php` | GET, POST, PUT, DELETE |
| Cursos | `/api/cursos.php` | GET, POST, PUT, DELETE |
| Evaluaciones | `/api/evaluaciones.php` | GET, POST, PUT, DELETE |
| Reportes | `/api/reportes.php` | GET (múltiples tipos) |
| Upload | `/api/upload.php` | POST, GET, DELETE |

---

## 👥 Roles y Permisos

| Rol | Docentes | Incidencias | Reportes | Usuarios |
|:---:|:--------:|:-----------:|:--------:|:--------:|
| **Admin** | ✅ CRUD | ✅ CRUD | ✅ Export | ✅ Gestionar |
| **Academia** | 👁️ Ver | ✅ CRUD | ✅ Export | ❌ |
| **Dirección** | 👁️ Ver | 👁️ Ver | ✅ Export | ❌ |
| **Docente** | 👁️ Propio | 📝 Crear | ❌ | ❌ |
| **Coordinador** | ✏️ Editar | ✅ CRUD | ✅ Export | ❌ |

---

## 👨‍💻 Equipo de Desarrollo

<div align="center">

| Frontend | Backend | Base de Datos |
|:--------:|:-------:|:-------------:|
| [![GitHub](https://img.shields.io/badge/-@gaelgm03-181717?style=for-the-badge&logo=github&logoColor=white)](https://github.com/gaelgm03) | [![GitHub](https://img.shields.io/badge/-@RogelioNava-181717?style=for-the-badge&logo=github&logoColor=white)](https://github.com/RogelioNava) | [![GitHub](https://img.shields.io/badge/-@antonioannese-181717?style=for-the-badge&logo=github&logoColor=white)](https://github.com/antonioannese) |
| **Gael Guzmán** | **Rogelio Nava** | **Antonio Annese** |
| Angular, TypeScript | PHP, REST API, JWT | MySQL, Schema Design |

</div>

---

## 📝 Licencia

Distribuido bajo la Licencia MIT. Ver [`LICENSE`](LICENSE) para más información.

---

<div align="center">

⭐ **Si este proyecto te fue útil, considera darle una estrella** ⭐

</div>
