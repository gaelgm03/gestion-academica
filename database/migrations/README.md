# 📦 Migraciones de Base de Datos

## 🎯 Migración 002: Categorías Predefinidas de Incidencias

### ✅ Descripción

Esta migración convierte el campo `tipo` de la tabla `incidencia` de un campo de texto libre (VARCHAR) a una relación con una tabla de catálogo `tipo_incidencia` que contiene las 5 categorías especificadas en los requisitos del proyecto:

1. Cambio de calificación
2. Cambio de fecha de examen
3. Integridad académica
4. Reporte disciplinar a profesor
5. Incidencia de pago

### 🚀 Cómo ejecutar la migración

#### Opción 1: Desde phpMyAdmin

1. Abrir phpMyAdmin en `http://localhost/phpmyadmin`
2. Seleccionar la base de datos `gestion_academica`
3. Ir a la pestaña "SQL"
4. Copiar y pegar todo el contenido del archivo `002_categorias_incidencias.sql`
5. Hacer clic en "Continuar"
6. Verificar que todas las consultas se ejecutaron correctamente

#### Opción 2: Desde línea de comandos

```bash
# Navegar al directorio de migraciones
cd c:\wamp64\www\gestion_academica\database\migrations

# Ejecutar la migración
mysql -u root -p gestion_academica < 002_categorias_incidencias.sql
```

#### Opción 3: Desde PowerShell (WAMP)

```powershell
# Navegar al directorio de migraciones
cd c:\wamp64\www\gestion_academica\database\migrations

# Ejecutar la migración
& "C:\wamp64\bin\mysql\mysql8.0.31\bin\mysql.exe" -u root gestion_academica < 002_categorias_incidencias.sql
```

### 📋 Qué hace la migración

1. **Crea tabla `tipo_incidencia`:**
   - `id`: Primary key
   - `nombre`: Nombre de la categoría (UNIQUE)
   - `descripcion`: Descripción de la categoría
   - `activo`: Indicador si está activa
   - `orden`: Orden de visualización

2. **Inserta las 5 categorías requeridas**

3. **Agrega columna `tipo_id` a la tabla `incidencia`**

4. **Migra datos existentes:**
   - Intenta hacer match inteligente de texto a IDs
   - Los registros sin match se asignan a "Cambio de calificación"

5. **Renombra columna antigua:**
   - `tipo` → `tipo_old` (se mantiene temporalmente para referencia)
   - `tipo_id` se hace obligatorio (NOT NULL)

6. **Agrega foreign key** hacia `tipo_incidencia`

7. **Crea índice** para optimizar consultas

### ✅ Verificación

Después de ejecutar la migración, verifica:

```sql
-- 1. Ver los tipos de incidencia creados
SELECT * FROM tipo_incidencia ORDER BY orden;

-- 2. Ver distribución de incidencias por tipo
SELECT 
    ti.nombre as tipo,
    COUNT(i.id) as cantidad
FROM tipo_incidencia ti
LEFT JOIN incidencia i ON ti.id = i.tipo_id
GROUP BY ti.id, ti.nombre
ORDER BY ti.orden;

-- 3. Verificar que todas las incidencias tienen tipo_id
SELECT COUNT(*) as total_sin_tipo
FROM incidencia
WHERE tipo_id IS NULL;
-- Debe retornar 0

-- 4. Ver muestra de datos migrados
SELECT 
    i.id,
    i.tipo_old as tipo_anterior,
    ti.nombre as tipo_nuevo
FROM incidencia i
INNER JOIN tipo_incidencia ti ON i.tipo_id = ti.id
LIMIT 10;
```

### 🗑️ Limpieza (Opcional)

Una vez verificado que la migración fue exitosa, puedes eliminar la columna temporal `tipo_old`:

```sql
ALTER TABLE incidencia DROP COLUMN tipo_old;
```

**⚠️ IMPORTANTE:** Solo ejecuta esto después de verificar que todo funciona correctamente.

### ⏪ Rollback (Reversión)

Si necesitas revertir la migración:

```sql
-- 1. Eliminar FK
ALTER TABLE incidencia DROP FOREIGN KEY fk_incidencia_tipo;

-- 2. Eliminar índice
DROP INDEX idx_incidencia_tipo ON incidencia;

-- 3. Restaurar columna tipo desde tipo_old
ALTER TABLE incidencia 
    ADD COLUMN tipo VARCHAR(200) AFTER id,
    DROP COLUMN tipo_id;

UPDATE incidencia SET tipo = tipo_old;

ALTER TABLE incidencia DROP COLUMN tipo_old;

-- 4. Eliminar tabla de tipos
DROP TABLE tipo_incidencia;
```

### 📊 Impacto en el código

Esta migración requiere cambios en:

- ✅ **Backend:**
  - `backend/models/Incidencia.php` - Actualizado para usar `tipo_id`
  - `backend/api/incidencias.php` - Endpoint `?action=tipos` agregado

- ✅ **Frontend:**
  - `frontend/src/app/services/api.service.ts` - Nueva interface `TipoIncidencia`
  - `frontend/src/app/incidencias/incidencias.ts` - Carga de tipos
  - `frontend/src/app/incidencias/incidencias.html` - Select en lugar de input

**Todos estos cambios ya están implementados.**

### 🎯 Resultado

Después de la migración:

- ✅ Las incidencias solo pueden tener uno de los 5 tipos predefinidos
- ✅ La validación de datos es más estricta
- ✅ Los reportes y filtros son más precisos
- ✅ Se cumple con el requisito especificado en el proyecto
- ✅ El sistema es más fácil de mantener

---

---

## 🎯 Migración 003: Áreas de Especialidad para Docentes

### ✅ Descripción

Esta migración agrega el catálogo de áreas de especialidad y su relación muchos-a-muchos con docentes, permitiendo:

- Definir áreas de conocimiento especializadas
- Asignar múltiples áreas a cada docente
- Especificar nivel de dominio y años de experiencia por área

### � Cómo ejecutar la migración

```bash
# Desde línea de comandos
mysql -u root -p gestion_academica < 003_areas_especialidad.sql
```

### 📋 Qué hace la migración

1. **Crea tabla `area_especialidad`:**
   - 20 áreas predefinidas (IA, Desarrollo, Bases de Datos, etc.)

2. **Crea tabla `docente_area_especialidad`:**
   - Relación muchos-a-muchos
   - Campo `nivel`: básico, intermedio, avanzado, experto
   - Campo `anios_experiencia`: años de experiencia en el área

3. **Inserta datos de ejemplo** para algunos docentes existentes

### ✅ Verificación

```sql
-- Ver áreas disponibles
SELECT * FROM area_especialidad ORDER BY nombre;

-- Ver docentes con sus áreas
SELECT 
    u.nombre as docente,
    GROUP_CONCAT(ae.nombre) as areas
FROM docente d
INNER JOIN usuario u ON d.id_usuario = u.id
LEFT JOIN docente_area_especialidad dae ON d.id = dae.docente_id
LEFT JOIN area_especialidad ae ON dae.area_id = ae.id
GROUP BY d.id, u.nombre;
```

### 📊 Impacto en el código

- ✅ **Backend:** `models/Docente.php` - Métodos para gestionar áreas
- ✅ **Backend:** `api/docentes.php` - Endpoint `?action=areas`
- ✅ **Frontend:** `api.service.ts` - Interface y métodos para áreas
- ✅ **Frontend:** `docentes.ts/html` - Selector de áreas en formulario

---

## �📝 Historial de Migraciones

| # | Nombre | Fecha | Descripción |
|---|--------|-------|-------------|
| 001 | `schema.sql` | 2025-11-25 | Schema inicial del proyecto |
| 002 | `002_categorias_incidencias.sql` | 2025-11-25 | Categorías predefinidas para incidencias |
| 003 | `003_areas_especialidad.sql` | 2025-11-25 | Áreas de especialidad para docentes |
