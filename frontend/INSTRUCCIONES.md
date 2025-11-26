# Instrucciones para Probar el Frontend

## Configuración Inicial

1. **Instalar dependencias:**
   ```bash
   cd frontend
   npm install
   ```

2. **Configurar la URL del API:**
   - La URL del API ya está configurada en `src/app/services/api.service.ts`
   - URL actual:
     ```typescript
     const API_URL = 'http://localhost/gestion_academica/backend';
     ```
   - Si tu proyecto está en otra ruta, ajusta la URL en este archivo

3. **Asegúrate de que el backend PHP esté corriendo:**
   - El backend debe estar accesible en la URL configurada
   - Verifica que CORS esté habilitado en el backend

## Ejecutar la Aplicación

```bash
npm start
# o
ng serve
```

La aplicación estará disponible en: `http://localhost:4200`

## Funcionalidades Implementadas

### 1. Dashboard
- Muestra estadísticas generales del sistema
- Total de docentes, activos, SNI
- Total de incidencias y abiertas
- Gráficos de incidencias por estado y prioridad
- Docentes por estatus

### 2. Docentes
- **Listar:** Ver todos los docentes con filtros
- **Crear:** Agregar nuevo docente
- **Editar:** Modificar información de docente
- **Eliminar:** Soft delete (cambia estatus a inactivo)
- **Filtros:**
  - Por estatus (activo/inactivo)
  - Por SNI (sí/no)
  - Búsqueda por nombre, email o grados

### 3. Incidencias
- **Listar:** Ver todas las incidencias con filtros
- **Crear:** Registrar nueva incidencia
- **Editar:** Actualizar incidencia
- **Eliminar:** Eliminar incidencia
- **Filtros:**
  - Por estado (abierto/en proceso/cerrado)
  - Por prioridad (Alta/Media/Baja)
  - Por tipo de incidencia

## Notas Importantes

1. **CORS:** Asegúrate de que el backend tenga CORS habilitado para `http://localhost:4200`

2. **Base de Datos:** La base de datos debe estar configurada y con datos de prueba

3. **Errores:** Si ves errores de conexión:
   - Verifica que el backend esté corriendo
   - Revisa la URL en `api.service.ts`
   - Abre la consola del navegador (F12) para ver errores detallados

4. **Pruebas:**
   - Prueba crear un docente nuevo
   - Prueba crear una incidencia
   - Verifica que los filtros funcionen
   - Revisa que el dashboard muestre datos correctos

## Estructura de Archivos

```
frontend/src/app/
├── services/
│   └── api.service.ts      # Servicio HTTP para comunicación con API
├── dashboard/              # Componente Dashboard
├── docentes/               # Componente Docentes (CRUD)
├── incidencias/            # Componente Incidencias (CRUD)
├── app.ts                  # Componente principal
├── app.routes.ts           # Rutas de la aplicación
└── app.config.ts           # Configuración de la app
```

## Solución de Problemas

### Error: "Cannot find module '@angular/common/http'"
```bash
npm install @angular/common
```

### Error de CORS
- Verifica que el backend tenga los headers CORS correctos
- Revisa que la URL del API sea correcta

### No se cargan los datos
- Abre la consola del navegador (F12)
- Revisa la pestaña Network para ver las peticiones HTTP
- Verifica que el backend responda correctamente

