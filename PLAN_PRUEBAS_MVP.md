# Plan de Pruebas MVP - Sistema de Gestión Académica

## Fecha: Noviembre 2025
## Versión: 1.0

---

## 1. PRUEBAS FUNCIONALES

### 1.1 Módulo de Autenticación

| ID | Caso de Prueba | Pasos | Resultado Esperado | Prioridad |
|----|----------------|-------|-------------------|-----------|
| AUTH-01 | Login exitoso | 1. Ir a /login<br>2. Ingresar email válido (@up.edu.mx)<br>3. Ingresar contraseña (test123)<br>4. Clic en "Iniciar Sesión" | Redirección a /dashboard, token almacenado | Alta |
| AUTH-02 | Login con credenciales inválidas | 1. Ir a /login<br>2. Ingresar email inválido<br>3. Clic en "Iniciar Sesión" | Mensaje de error "Credenciales inválidas" | Alta |
| AUTH-03 | Validación de email | 1. Intentar login con email sin @<br>2. Intentar login con email sin dominio | Mensaje "Ingresa un email válido" | Media |
| AUTH-04 | Logout | 1. Estando autenticado, clic en "Cerrar sesión" | Redirección a /login, tokens eliminados | Alta |
| AUTH-05 | Refresh token automático | 1. Esperar expiración del access_token<br>2. Realizar una petición | Token renovado automáticamente | Alta |
| AUTH-06 | Protección de rutas | 1. Sin autenticación, acceder a /docentes | Redirección a /login | Alta |
| AUTH-07 | Acceso denegado por rol | 1. Login como "docente"<br>2. Intentar acceder a funciones de admin | Redirección a /unauthorized | Media |

### 1.2 Módulo de Docentes

| ID | Caso de Prueba | Pasos | Resultado Esperado | Prioridad |
|----|----------------|-------|-------------------|-----------|
| DOC-01 | Listar docentes | 1. Ir a /docentes | Tabla con lista de docentes | Alta |
| DOC-02 | Crear docente | 1. Clic "Nuevo Docente"<br>2. Completar formulario<br>3. Guardar | Docente creado, aparece en lista | Alta |
| DOC-03 | Editar docente | 1. Clic en editar docente<br>2. Modificar datos<br>3. Guardar | Datos actualizados | Alta |
| DOC-04 | Eliminar docente | 1. Clic en eliminar<br>2. Confirmar | Docente eliminado de la lista | Alta |
| DOC-05 | Filtrar por estatus | 1. Seleccionar filtro "Activo" | Solo docentes activos visibles | Media |
| DOC-06 | Filtrar por SNI | 1. Seleccionar filtro "SNI: Sí" | Solo docentes SNI visibles | Media |
| DOC-07 | Buscar docente | 1. Escribir nombre en búsqueda | Resultados filtrados por nombre | Media |
| DOC-08 | Ver detalle docente | 1. Clic en "Ver" docente | Modal con perfil completo, áreas, evaluaciones | Alta |
| DOC-09 | Asignar áreas especialidad | 1. Editar docente<br>2. Seleccionar áreas<br>3. Guardar | Áreas asignadas correctamente | Media |
| DOC-10 | Validación de email único | 1. Crear docente con email existente | Error "El email ya está registrado" | Alta |

### 1.3 Módulo de Incidencias

| ID | Caso de Prueba | Pasos | Resultado Esperado | Prioridad |
|----|----------------|-------|-------------------|-----------|
| INC-01 | Listar incidencias | 1. Ir a /incidencias | Tabla con incidencias ordenadas | Alta |
| INC-02 | Crear incidencia | 1. Clic "Nueva Incidencia"<br>2. Seleccionar tipo<br>3. Completar datos<br>4. Guardar | Incidencia creada con status "abierto" | Alta |
| INC-03 | Validar tipo requerido | 1. Intentar guardar sin tipo | Error de validación | Alta |
| INC-04 | Cambiar estado | 1. Editar incidencia<br>2. Cambiar status<br>3. Guardar | Estado actualizado, historial registrado | Alta |
| INC-05 | Filtrar por estado | 1. Seleccionar "Abierto" | Solo incidencias abiertas | Media |
| INC-06 | Filtrar por prioridad | 1. Seleccionar "Alta" | Solo incidencias alta prioridad | Media |
| INC-07 | Filtrar por tipo | 1. Seleccionar tipo | Solo incidencias de ese tipo | Media |
| INC-08 | Adjuntar evidencia | 1. En incidencia, clic "Subir archivo"<br>2. Seleccionar archivo | Archivo adjuntado y visible | Alta |
| INC-09 | Ver historial | 1. Abrir detalle incidencia<br>2. Ver pestaña historial | Lista de cambios con usuario y fecha | Alta |
| INC-10 | Asignar a usuario | 1. Editar incidencia<br>2. Seleccionar "Asignado a"<br>3. Guardar | Usuario asignado, historial registrado | Media |

### 1.4 Módulo de Cursos

| ID | Caso de Prueba | Pasos | Resultado Esperado | Prioridad |
|----|----------------|-------|-------------------|-----------|
| CUR-01 | Listar cursos | 1. Ir a /cursos | Tabla con catálogo de cursos | Alta |
| CUR-02 | Crear curso | 1. Clic "Nuevo Curso"<br>2. Completar datos<br>3. Guardar | Curso creado | Alta |
| CUR-03 | Validar código único | 1. Crear curso con código existente | Error de duplicado | Alta |
| CUR-04 | Asignar docente a curso | 1. Ver curso<br>2. Clic "Asignar docente"<br>3. Seleccionar docente | Docente asignado al curso | Alta |
| CUR-05 | Filtrar por academia | 1. Seleccionar academia | Cursos de esa academia | Media |
| CUR-06 | Filtrar por modalidad | 1. Seleccionar "Virtual" | Solo cursos virtuales | Media |

### 1.5 Módulo de Evaluaciones

| ID | Caso de Prueba | Pasos | Resultado Esperado | Prioridad |
|----|----------------|-------|-------------------|-----------|
| EVA-01 | Listar evaluaciones | 1. Ir a /evaluaciones | Tabla de evaluaciones | Alta |
| EVA-02 | Crear evaluación | 1. Clic "Nueva Evaluación"<br>2. Seleccionar docente<br>3. Calificar criterios<br>4. Guardar | Evaluación creada | Alta |
| EVA-03 | Ver resumen docente | 1. En /docentes, ver detalle<br>2. Ver pestaña evaluaciones | Promedio y desglose por criterio | Alta |
| EVA-04 | Filtrar por tipo evaluador | 1. Seleccionar "Alumno" | Solo evaluaciones de alumnos | Media |

### 1.6 Dashboard y Reportes

| ID | Caso de Prueba | Pasos | Resultado Esperado | Prioridad |
|----|----------------|-------|-------------------|-----------|
| REP-01 | Cargar dashboard | 1. Ir a /dashboard | KPIs y gráficas visibles | Alta |
| REP-02 | Filtrar por período | 1. Seleccionar "Última semana" | Datos actualizados al período | Alta |
| REP-03 | Filtrar fechas personalizadas | 1. Seleccionar "Personalizado"<br>2. Ingresar fechas<br>3. Aplicar | Datos del rango seleccionado | Media |
| REP-04 | Exportar CSV incidencias | 1. Clic "Exportar CSV" | Archivo .csv descargado | Alta |
| REP-05 | Exportar XLSX docentes | 1. Clic "Exportar Excel" | Archivo .xlsx descargado | Alta |
| REP-06 | Exportar PDF dashboard | 1. Clic "Exportar PDF" | PDF generado con estadísticas | Alta |
| REP-07 | Reporte por materia | 1. Ir a /reporte-materias | Datos por curso/materia | Alta |

---

## 2. PRUEBAS DE FLUJO DE USUARIO

### 2.1 Flujo Completo: Gestionar Incidencia

```
1. Login como usuario "academia"
2. Ir a Incidencias
3. Crear nueva incidencia tipo "Cambio de calificación"
4. Asignar a un profesor
5. Adjuntar evidencia (imagen o PDF)
6. Guardar
7. Verificar que aparece en la lista
8. Editar la incidencia
9. Cambiar status a "en proceso"
10. Verificar historial de cambios
11. Cerrar incidencia
12. Verificar exportación en reporte
```

### 2.2 Flujo Completo: Alta de Docente

```
1. Login como admin
2. Ir a Docentes
3. Crear nuevo docente con todos los campos
4. Asignar áreas de especialidad
5. Guardar
6. Ir a Cursos
7. Asignar el docente a un curso
8. Ir a Evaluaciones
9. Crear evaluación para el docente
10. Verificar resumen de evaluaciones en perfil
11. Exportar datos del docente a PDF
```

### 2.3 Flujo Completo: Consulta de Dirección

```
1. Login como usuario "direccion"
2. Ir a Dashboard
3. Revisar KPIs
4. Filtrar por último mes
5. Ir a Reporte por Materias
6. Revisar distribución por academia
7. Exportar a Excel
8. Verificar permisos (no puede crear/editar)
```

---

## 3. PRUEBAS DE USABILIDAD

| ID | Aspecto | Criterio | Método |
|----|---------|----------|--------|
| USA-01 | Navegación | Menús accesibles en máximo 2 clics | Observación |
| USA-02 | Feedback visual | Indicadores de carga, éxito y error | Observación |
| USA-03 | Formularios | Labels claros, validaciones en tiempo real | Observación |
| USA-04 | Responsive | Funcional en tablet (768px) | Test en dispositivo |
| USA-05 | Accesibilidad | Contraste de colores, textos legibles | Lighthouse |
| USA-06 | Rendimiento | Dashboard carga en < 3 segundos | DevTools |

---

## 4. PRUEBAS DE ESTABILIDAD

### 4.1 Pruebas de Carga

| ID | Prueba | Configuración | Criterio Éxito |
|----|--------|---------------|----------------|
| CAR-01 | Múltiples usuarios | 10 usuarios concurrentes | Tiempo respuesta < 2s |
| CAR-02 | Carga de datos | 1000 docentes, 5000 incidencias | Sin degradación |

### 4.2 Pruebas de Errores

| ID | Escenario | Acción | Resultado Esperado |
|----|-----------|--------|-------------------|
| ERR-01 | BD no disponible | Desconectar MySQL | Mensaje de error, sin crash |
| ERR-02 | Token expirado | Esperar expiración | Refresh automático o redirect login |
| ERR-03 | API timeout | Simular latencia alta | Mensaje "Error de conexión" |
| ERR-04 | Archivo muy grande | Subir archivo > 10MB | Mensaje de límite |

---

## 5. MATRIZ DE PRIORIDADES

| Prioridad | Cantidad | Descripción |
|-----------|----------|-------------|
| Alta | 28 casos | Funcionalidad core del MVP |
| Media | 15 casos | Mejoras y filtros |
| Baja | 5 casos | Nice to have |

---

## 6. CHECKLIST PRE-PRODUCCIÓN

- [ ] Todos los tests de prioridad Alta pasados
- [ ] JWT_SECRET cambiado en producción
- [ ] CORS configurado para dominio de producción
- [ ] SSL/HTTPS habilitado
- [ ] Logs configurados correctamente
- [ ] Backup de BD configurado
- [ ] Variables de entorno de producción
- [ ] Prueba de login con LDAP real

---

## 7. COMANDOS PARA EJECUTAR PRUEBAS

### Backend (PHP)
```bash
# Verificar conexión a BD
php -r "require 'backend/config/db.php'; echo 'Conexión OK';"

# Verificar endpoints
curl http://localhost/gestion_academica/backend/api/auth.php?action=check
```

### Frontend (Angular)
```bash
# Iniciar servidor de desarrollo
cd frontend
npm install
ng serve

# Ejecutar tests unitarios (si se implementan)
ng test

# Build de producción
ng build --configuration=production
```

---

## 8. CREDENCIALES DE PRUEBA

| Usuario | Email | Password | Rol |
|---------|-------|----------|-----|
| Admin | ana.lopez@up.edu.mx | test123 | admin |
| Academia | carlos.jimenez@up.edu.mx | test123 | academia |
| Dirección | sofia.torres@up.edu.mx | test123 | direccion |
| Docente | laura.martinez@up.edu.mx | test123 | docente |
| Coordinador | maria.rodriguez@up.edu.mx | test123 | coordinador |

---

## 9. REPORTE DE DEFECTOS

### Plantilla
```
ID: [DEF-XXX]
Título: [Descripción breve]
Severidad: [Crítico/Mayor/Menor/Cosmético]
Módulo: [Nombre del módulo]
Pasos para reproducir: [Lista numerada]
Resultado actual: [Descripción]
Resultado esperado: [Descripción]
Evidencia: [Screenshot/Video]
```

---

**Documento preparado para el Sistema de Gestión Académica**
**Versión MVP - Noviembre 2025**
