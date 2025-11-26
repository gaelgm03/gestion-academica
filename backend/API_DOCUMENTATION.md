# Documentación de API REST
Sistema de Gestión Académica

## URL Base
```
http://localhost/gestion_academica/backend
```

---

## 📚 Endpoints Disponibles

### 1. API Docentes

#### Listar todos los docentes
```http
GET /api/docentes
```

**Filtros opcionales (query parameters):**
- `estatus`: activo | inactivo
- `sni`: 0 | 1
- `academia_id`: ID de la academia
- `search`: Búsqueda por nombre, email o grados

**Ejemplo:**
```
GET /api/docentes?estatus=activo&sni=1
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Lista de docentes",
  "data": [
    {
      "id": 1,
      "id_usuario": 1,
      "nombre": "Ana López",
      "email": "ana.lopez@up.edu.mx",
      "grados": "Doctorado en Educación",
      "idioma": "Inglés",
      "sni": 1,
      "cvlink": "https://cvup.mx/ana-lopez",
      "estatus": "activo",
      "academias": "Ingeniería, Ciencias Exactas",
      "academia_ids": "1,6"
    }
  ]
}
```

#### Obtener un docente por ID
```http
GET /api/docentes/{id}
```

**Ejemplo:**
```
GET /api/docentes/1
```

#### Crear un nuevo docente
```http
POST /api/docentes
Content-Type: application/json
```

**Body:**
```json
{
  "nombre": "Juan Pérez",
  "email": "juan.perez@up.edu.mx",
  "grados": "Maestría en Computación",
  "idioma": "Inglés",
  "sni": 0,
  "cvlink": "https://cvup.mx/juan-perez",
  "estatus": "activo",
  "academia_ids": [1, 6]
}
```

#### Actualizar un docente
```http
PUT /api/docentes/{id}
Content-Type: application/json
```

**Body (campos opcionales):**
```json
{
  "nombre": "Juan Pérez Actualizado",
  "estatus": "inactivo"
}
```

#### Eliminar un docente (soft delete)
```http
DELETE /api/docentes/{id}
```

#### Obtener estadísticas de docentes
```http
GET /api/docentes/stats
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Estadísticas obtenidas",
  "data": {
    "total": 15,
    "activos": 14,
    "inactivos": 1,
    "con_sni": 6
  }
}
```

---

### 2. API Incidencias

#### Listar todas las incidencias
```http
GET /api/incidencias
```

**Filtros opcionales:**
- `status`: abierto | en proceso | cerrado
- `prioridad`: Alta | Media | Baja
- `profesor`: ID del docente
- `asignadoA`: ID del usuario asignado
- `tipo`: Texto del tipo de incidencia
- `fecha_desde`: YYYY-MM-DD
- `fecha_hasta`: YYYY-MM-DD

**Ejemplo:**
```
GET /api/incidencias?status=abierto&prioridad=Alta
```

#### Obtener una incidencia por ID
```http
GET /api/incidencias/{id}
```

#### Crear una nueva incidencia
```http
POST /api/incidencias
Content-Type: application/json
```

**Body:**
```json
{
  "tipo": "Cambio de calificación",
  "profesor": 1,
  "curso": "Cálculo Integral",
  "prioridad": "Alta",
  "sla": "48h",
  "asignadoA": 2,
  "evidencias": "evidencia.pdf",
  "status": "abierto"
}
```

#### Actualizar una incidencia
```http
PUT /api/incidencias/{id}
Content-Type: application/json
```

**Body:**
```json
{
  "status": "cerrado"
}
```

#### Eliminar una incidencia
```http
DELETE /api/incidencias/{id}
```

#### Obtener estadísticas de incidencias
```http
GET /api/incidencias/stats
```

---

### 3. API Reportes

Todos los reportes usan el método GET con parámetro `tipo`.

#### Dashboard principal
```http
GET /api/reportes?tipo=dashboard
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Dashboard obtenido exitosamente",
  "data": {
    "dashboard": {
      "total_docentes": 15,
      "docentes_sni": 6,
      "docentes_activos": 14,
      "total_incidencias": 20,
      "incidencias_abiertas": 8
    },
    "incidencias_por_estado": [...],
    "incidencias_por_prioridad": [...],
    "docentes_por_estatus": [...]
  }
}
```

#### Otros tipos de reportes disponibles:

1. **Docentes por academia**
```http
GET /api/reportes?tipo=docentes_por_academia
```

2. **Incidencias por tipo**
```http
GET /api/reportes?tipo=incidencias_por_tipo
```

3. **Docentes con más incidencias**
```http
GET /api/reportes?tipo=docentes_con_mas_incidencias&limit=10
```

4. **Incidencias por fecha**
```http
GET /api/reportes?tipo=incidencias_por_fecha&fecha_inicio=2024-01-01&fecha_fin=2024-12-31
```

5. **Distribución de grados**
```http
GET /api/reportes?tipo=distribucion_grados
```

6. **Distribución de idiomas**
```http
GET /api/reportes?tipo=distribucion_idiomas
```

7. **Usuarios con más asignaciones**
```http
GET /api/reportes?tipo=usuarios_mas_asignaciones&limit=10
```

8. **Resumen ejecutivo completo**
```http
GET /api/reportes?tipo=resumen_ejecutivo
```

---

## 🔧 Pruebas con cURL

### Listar docentes activos
```bash
curl "http://localhost/gestion_academica/backend/api/docentes?estatus=activo"
```

### Crear una incidencia
```bash
curl -X POST http://localhost/gestion_academica/backend/api/incidencias \
  -H "Content-Type: application/json" \
  -d '{
    "tipo": "Reporte de prueba",
    "profesor": 1,
    "curso": "Test",
    "prioridad": "Media"
  }'
```

### Obtener dashboard
```bash
curl "http://localhost/gestion_academica/backend/api/reportes?tipo=dashboard"
```

---

## 🧪 Pruebas con Postman

1. Importa la colección desde este archivo
2. Ajusta la variable de entorno `BASE_URL` a tu servidor local
3. Ejecuta las peticiones de prueba

---

## 📝 Notas Importantes

1. **CORS**: La API está configurada para aceptar peticiones desde `http://localhost:4200`
2. **Formato de respuesta**: Todas las respuestas siguen el formato:
   ```json
   {
     "success": boolean,
     "message": string,
     "data": object|array|null
   }
   ```
3. **Códigos HTTP**:
   - `200`: Operación exitosa
   - `201`: Recurso creado
   - `400`: Petición incorrecta
   - `404`: Recurso no encontrado
   - `405`: Método no permitido
   - `409`: Conflicto (ej: email duplicado)
   - `500`: Error del servidor

---

## 🐛 Troubleshooting

### Error: "No se puede conectar a la API"
- Verifica que WAMP esté corriendo
- Asegúrate de que la base de datos esté importada
- Revisa el archivo `.env` con las credenciales correctas

### Error: "CORS"
- Verifica que el archivo `.htaccess` esté en la carpeta `backend`
- Asegúrate de que `mod_rewrite` y `mod_headers` estén habilitados en Apache

### Error: "Call to undefined function env()"
- Verifica que el archivo `.env` exista en `backend/`
- Asegúrate de que `config/env.php` se esté cargando correctamente

---

**Desarrollado con ❤️ por el equipo de Gestión Académica**
