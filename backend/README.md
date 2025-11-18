# Backend - Sistema de Gestión Académica

API REST desarrollada en PHP 8.x para el sistema de gestión académica.

## 📋 Requisitos

- PHP 8.0 o superior
- MySQL 8.0 o superior
- Extensiones PHP:
  - PDO
  - pdo_mysql
  - json
  - mbstring
  - openssl (para JWT)
  - ldap (para autenticación)

## 🚀 Instalación

### 1. Configurar Variables de Entorno

Copia el archivo de ejemplo y configura tus credenciales:

```bash
cp .env.example .env
```

Edita el archivo `.env` con tus credenciales:

```env
# Base de datos (WAMP64 por defecto)
DB_HOST=localhost
DB_NAME=gestion_academica
DB_USER=root
DB_PASS=

# LDAP (si tienes servidor institucional)
LDAP_HOST=ldap://tu-servidor-ldap
LDAP_PORT=389
LDAP_BASE_DN=dc=universidad,dc=edu,dc=mx

# JWT Secret (cambiar en producción)
JWT_SECRET=genera_un_token_seguro_aqui
```

### 2. Importar Base de Datos

```bash
# Desde la raíz del proyecto
cd database
mysql -u root -p < schema.sql
```

O importa `schema.sql` desde phpMyAdmin.

### 3. Probar Conexión

Ejecuta el script de prueba para verificar que todo esté configurado:

```bash
# Desde línea de comandos
php test_connection.php

# O desde el navegador
http://localhost/gestion_academica/backend/test_connection.php
```

### 4. Iniciar el Servidor

#### Opción A: WAMP64/XAMPP/MAMP
Ya está configurado si lo colocaste en `www` o `htdocs`.

#### Opción B: Servidor Integrado de PHP
```bash
php -S localhost:8000
```

## 📁 Estructura del Proyecto

```
backend/
├── api/                    # Endpoints de la API
│   ├── docentes.php       # CRUD de docentes
│   ├── inidencias.php     # CRUD de incidencias
│   └── reportes.php       # Generación de reportes
├── config/                 # Configuración
│   ├── db.php             # Conexión a la base de datos
│   └── env.php            # Cargador de variables de entorno
├── models/                 # Modelos de datos
│   ├── docente.php
│   ├── incidencia.php
│   └── reporte.php
├── .env                    # Variables de entorno (NO subir a git)
├── .env.example           # Plantilla de variables de entorno
├── .gitignore             # Archivos ignorados por git
├── index.php              # Punto de entrada
└── test_connection.php    # Script de prueba
```

## 🔧 Variables de Entorno Disponibles

### Base de Datos
- `DB_HOST`: Host de la base de datos (default: localhost)
- `DB_NAME`: Nombre de la base de datos (default: gestion_academica)
- `DB_USER`: Usuario de MySQL (default: root)
- `DB_PASS`: Contraseña de MySQL (default: vacío)

### LDAP
- `LDAP_HOST`: Servidor LDAP institucional
- `LDAP_PORT`: Puerto LDAP (default: 389)
- `LDAP_BASE_DN`: Distinguished Name base
- `LDAP_ADMIN_DN`: DN del administrador
- `LDAP_ADMIN_PASS`: Contraseña del administrador

### JWT
- `JWT_SECRET`: Clave secreta para firmar tokens
- `JWT_ALGORITHM`: Algoritmo de encriptación (default: HS256)
- `JWT_EXPIRATION`: Tiempo de expiración en segundos (default: 3600)

### Aplicación
- `APP_ENV`: Entorno (development/production)
- `APP_DEBUG`: Modo debug (true/false)
- `APP_TIMEZONE`: Zona horaria (default: America/Mexico_City)

### CORS
- `CORS_ALLOWED_ORIGINS`: Orígenes permitidos (default: http://localhost:4200)
- `CORS_ALLOWED_METHODS`: Métodos HTTP permitidos
- `CORS_ALLOWED_HEADERS`: Headers permitidos

## 🔒 Seguridad

### Producción
En producción, asegúrate de:

1. **Cambiar el JWT_SECRET** a un valor aleatorio y seguro
2. **Configurar APP_ENV=production** y **APP_DEBUG=false**
3. **Usar HTTPS** (SESSION_SECURE=true)
4. **Configurar CORS** apropiadamente
5. **Proteger el archivo .env** (permisos 600)
6. **Nunca subir .env a git** (ya está en .gitignore)

### Generar JWT Secret Seguro
```bash
# Opción 1: OpenSSL
openssl rand -base64 32

# Opción 2: PHP
php -r "echo bin2hex(random_bytes(32));"
```

## 📚 Uso de la Conexión a Base de Datos

```php
<?php
// En tus archivos PHP, simplemente requiere db.php
require_once __DIR__ . '/config/db.php';

// Ya tienes acceso a la conexión PDO
$stmt = $pdo->prepare("SELECT * FROM docente WHERE id = ?");
$stmt->execute([$id]);
$docente = $stmt->fetch();

// O usa la función helper
$db = getDB();
$result = $db->query("SELECT * FROM usuario")->fetchAll();
?>
```

## 🧪 Testing

```bash
# Probar conexión y configuración
php test_connection.php

# Debería mostrar:
# ✓ Archivo .env encontrado
# ✓ Conexión exitosa
# ✓ Todas las pruebas pasaron
```

## 🐛 Troubleshooting

### Error: "Archivo .env no encontrado"
- Verifica que copiaste `.env.example` a `.env`
- Asegúrate de estar en el directorio `backend`

### Error: "Access denied for user"
- Verifica tus credenciales en `.env`
- Asegúrate de que MySQL esté corriendo
- Verifica que el usuario tenga permisos sobre la base de datos

### Error: "Unknown database 'gestion_academica'"
- Importa el archivo `database/schema.sql`
- O crea la base de datos manualmente

### Error: "Call to undefined function env()"
- Verifica que `config/env.php` esté incluido
- Asegúrate de que el archivo exista en `backend/config/`

## 📞 Soporte

Para reportar bugs o solicitar features, abre un issue en el repositorio.

---

**Desarrollado con ❤️ por el equipo de Gestión Académica**
