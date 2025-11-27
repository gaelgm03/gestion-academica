import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';

const API_URL = 'http://localhost/gestion_academica/backend';

// NOTA: Si hay problemas con .htaccess, usar estas URLs directas:
// const API_URL_DOCENTES = 'http://localhost/gestion_academica/backend/api/docentes.php';
// const API_URL_INCIDENCIAS = 'http://localhost/gestion_academica/backend/api/incidencias.php';
// const API_URL_REPORTES = 'http://localhost/gestion_academica/backend/api/reportes.php';

export interface AreaEspecialidad {
  id: number;
  nombre: string;
  descripcion?: string;
  nivel?: 'básico' | 'intermedio' | 'avanzado' | 'experto';
  anios_experiencia?: number;
  fecha_asignacion?: string;
}

export interface Docente {
  id?: number;
  id_usuario?: number;
  nombre?: string;
  email?: string;
  grados?: string;
  idioma?: string;
  sni?: boolean;
  cvlink?: string;
  estatus?: 'activo' | 'inactivo';
  academias?: string;
  academia_ids?: number[];
  areas_especialidad?: string;
  area_ids?: number[];
}

export interface TipoIncidencia {
  id: number;
  nombre: string;
  descripcion?: string;
}

export interface Incidencia {
  id?: number;
  tipo_id?: number;
  tipo?: string;
  tipo_nombre?: string;
  profesor?: number;
  profesor_nombre?: string;
  profesor_email?: string;
  curso?: string;
  prioridad?: 'Alta' | 'Media' | 'Baja';
  sla?: string;
  asignadoA?: number;
  asignado_nombre?: string;
  evidencias?: string;
  status?: 'abierto' | 'en proceso' | 'cerrado';
  fecha_creacion?: string;
}

export interface UploadedFile {
  filename: string;
  original_name?: string;
  size: number;
  mime_type?: string;
  path: string;
  modified?: string;
}

export interface HistorialItem {
  id: number;
  campo_modificado: string;
  valor_anterior?: string;
  valor_nuevo?: string;
  accion: 'crear' | 'editar' | 'eliminar' | 'cambio_status' | 'asignar';
  fecha_cambio: string;
  ip_address?: string;
  usuario_nombre: string;
  usuario_email?: string;
}

export interface Periodo {
  id: string;
  nombre: string;
}

export interface PeriodoInfo {
  inicio: string;
  fin: string;
  periodo: string;
  descripcion: string;
}

export interface Dashboard {
  periodo?: PeriodoInfo;
  dashboard: {
    total_docentes: number;
    docentes_sni: number;
    docentes_activos: number;
    total_incidencias: number;
    incidencias_abiertas: number;
  };
  incidencias_por_estado: Array<{ status: string; cantidad: number }>;
  incidencias_por_prioridad: Array<{ prioridad: string; cantidad: number }>;
  docentes_por_estatus: Array<{ estatus: string; cantidad: number }>;
  incidencias_periodo?: number;
  tendencia_diaria?: Array<{ fecha: string; cantidad: number }>;
}

export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
}

// ========== REPORTE POR MATERIA ==========
export interface CursoConIncidencias {
  id: number;
  codigo: string;
  materia: string;
  academia?: string;
  semestre?: number;
  modalidad?: string;
  total_incidencias: number;
  incidencias_abiertas: number;
  incidencias_en_proceso: number;
  incidencias_cerradas: number;
  prioridad_alta: number;
  prioridad_media: number;
  prioridad_baja: number;
}

export interface CursoConDocentes {
  id: number;
  codigo: string;
  materia: string;
  academia?: string;
  total_docentes: number;
  promedio_evaluacion?: number;
  total_evaluaciones: number;
}

export interface TopMateriaEvaluacion {
  codigo: string;
  materia: string;
  promedio: number;
  total_evaluaciones: number;
  calificacion_min: number;
  calificacion_max: number;
}

export interface ReportePorMateria {
  periodo: PeriodoInfo;
  resumen: {
    total_cursos: number;
    cursos_activos: number;
    cursos_inactivos: number;
    total_incidencias_periodo: number;
  };
  cursos_con_incidencias: CursoConIncidencias[];
  cursos_con_docentes: CursoConDocentes[];
  distribucion_modalidad: Array<{ modalidad: string; cantidad: number }>;
  distribucion_academia: Array<{ academia: string; cantidad: number }>;
  distribucion_semestre: Array<{ semestre: number; cantidad: number }>;
  top_por_evaluacion: TopMateriaEvaluacion[];
}

// ========== CURSOS ==========
export interface Curso {
  id?: number;
  codigo: string;
  nombre: string;
  descripcion?: string;
  creditos?: number;
  horas_semana?: number;
  semestre?: number;
  modalidad?: 'presencial' | 'virtual' | 'hibrido';
  academia_id?: number;
  academia_nombre?: string;
  estatus?: 'activo' | 'inactivo';
  fecha_creacion?: string;
  docentes_asignados?: number;
  total_inscritos?: number;
  docentes?: DocenteCurso[];
}

export interface DocenteCurso {
  asignacion_id?: number;
  docente_id: number;
  docente_nombre?: string;
  docente_email?: string;
  curso_id: number;
  curso_codigo?: string;
  curso_nombre?: string;
  periodo: string;
  grupo?: string;
  horario?: string;
  aula?: string;
  cupo_maximo?: number;
  inscritos?: number;
  estatus?: 'activo' | 'finalizado' | 'cancelado';
  fecha_asignacion?: string;
}

export interface PeriodoAcademico {
  id: number;
  codigo: string;
  nombre: string;
  fecha_inicio: string;
  fecha_fin: string;
  estatus: 'planificacion' | 'activo' | 'finalizado';
}

export interface CursoStats {
  total_cursos: number;
  cursos_activos: number;
  asignaciones_activas: number;
  total_inscritos: number;
  cursos_por_modalidad: Array<{ modalidad: string; cantidad: number }>;
  cursos_por_academia: Array<{ academia: string; cantidad: number }>;
}

// ========== EVALUACIONES DOCENTES ==========
export interface CriterioEvaluacion {
  id: number;
  nombre: string;
  descripcion?: string;
  categoria: 'conocimiento' | 'metodologia' | 'comunicacion' | 'puntualidad' | 'material' | 'evaluacion' | 'otro';
  peso: number;
  orden: number;
}

export interface PeriodoEvaluacion {
  id: number;
  nombre: string;
  fecha_inicio: string;
  fecha_fin: string;
  estatus: 'programado' | 'activo' | 'cerrado';
}

export interface EvaluacionDetalle {
  id?: number;
  criterio_id: number;
  criterio_nombre?: string;
  categoria?: string;
  peso?: number;
  calificacion: number;
  comentario?: string;
}

export interface EvaluacionDocente {
  id?: number;
  docente_id: number;
  docente_nombre?: string;
  docente_email?: string;
  curso_id?: number;
  curso_codigo?: string;
  curso_nombre?: string;
  periodo_evaluacion_id?: number;
  periodo_nombre?: string;
  evaluador_id?: number;
  evaluador_nombre?: string;
  tipo_evaluador: 'alumno' | 'par' | 'coordinador' | 'autoevaluacion';
  calificacion_global?: number;
  comentarios?: string;
  fortalezas?: string;
  areas_mejora?: string;
  recomendaciones?: string;
  fecha_evaluacion?: string;
  estatus: 'borrador' | 'completada' | 'revisada';
  detalles?: EvaluacionDetalle[];
}

export interface ResumenEvaluacionDocente {
  total_evaluaciones: number;
  promedio_global: number;
  calificacion_minima: number;
  calificacion_maxima: number;
  eval_alumnos: number;
  eval_pares: number;
  eval_coordinadores: number;
  autoevaluaciones: number;
  promedios_criterios: Array<{
    criterio_id: number;
    criterio: string;
    categoria: string;
    promedio: number;
    total_respuestas: number;
  }>;
  evolucion: Array<{
    periodo: string;
    promedio: number;
    total_evaluaciones: number;
  }>;
}

export interface EvaluacionStats {
  total_evaluaciones: number;
  promedio_general: number;
  por_tipo_evaluador: Array<{ tipo_evaluador: string; cantidad: number; promedio: number }>;
  top_docentes: Array<{ docente: string; promedio: number; total_evaluaciones: number }>;
  distribucion_calificaciones: Array<{ rango: string; cantidad: number }>;
}

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  constructor(private http: HttpClient) {}

  // ========== DOCENTES ==========
  getDocentes(filters?: {
    estatus?: string;
    sni?: number;
    academia_id?: number;
    area_id?: number;
    search?: string;
  }): Observable<ApiResponse<Docente[]>> {
    let params = new HttpParams();
    if (filters) {
      Object.keys(filters).forEach(key => {
        const value = filters[key as keyof typeof filters];
        if (value !== undefined && value !== null && value !== '') {
          params = params.append(key, value.toString());
        }
      });
    }
    return this.http.get<ApiResponse<Docente[]>>(`${API_URL}/api/docentes.php`, { params });
  }

  getDocente(id: number): Observable<ApiResponse<Docente>> {
    return this.http.get<ApiResponse<Docente>>(`${API_URL}/api/docentes.php?id=${id}`);
  }

  createDocente(docente: Docente): Observable<ApiResponse<Docente>> {
    return this.http.post<ApiResponse<Docente>>(`${API_URL}/api/docentes.php`, docente);
  }

  updateDocente(id: number, docente: Partial<Docente>): Observable<ApiResponse<Docente>> {
    return this.http.put<ApiResponse<Docente>>(`${API_URL}/api/docentes.php?id=${id}`, docente);
  }

  deleteDocente(id: number): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${API_URL}/api/docentes.php?id=${id}`);
  }

  getDocentesStats(): Observable<ApiResponse<any>> {
    return this.http.get<ApiResponse<any>>(`${API_URL}/api/docentes.php?action=stats`);
  }

  // ========== ÁREAS DE ESPECIALIDAD ==========
  getAreasEspecialidad(): Observable<ApiResponse<AreaEspecialidad[]>> {
    return this.http.get<ApiResponse<AreaEspecialidad[]>>(`${API_URL}/api/docentes.php?action=areas`);
  }

  getAreasDelDocente(docenteId: number): Observable<ApiResponse<AreaEspecialidad[]>> {
    return this.http.get<ApiResponse<AreaEspecialidad[]>>(`${API_URL}/api/docentes.php?action=areas&id=${docenteId}`);
  }

  // ========== INCIDENCIAS ==========
  getIncidencias(filters?: {
    status?: string;
    prioridad?: string;
    profesor?: number;
    asignadoA?: number;
    tipo?: string;
    fecha_desde?: string;
    fecha_hasta?: string;
  }): Observable<ApiResponse<Incidencia[]>> {
    let params = new HttpParams();
    if (filters) {
      Object.keys(filters).forEach(key => {
        const value = filters[key as keyof typeof filters];
        if (value !== undefined && value !== null && value !== '') {
          params = params.append(key, value.toString());
        }
      });
    }
    return this.http.get<ApiResponse<Incidencia[]>>(`${API_URL}/api/incidencias.php`, { params });
  }

  getIncidencia(id: number): Observable<ApiResponse<Incidencia>> {
    return this.http.get<ApiResponse<Incidencia>>(`${API_URL}/api/incidencias.php?id=${id}`);
  }

  createIncidencia(incidencia: Incidencia): Observable<ApiResponse<Incidencia>> {
    return this.http.post<ApiResponse<Incidencia>>(`${API_URL}/api/incidencias.php`, incidencia);
  }

  updateIncidencia(id: number, incidencia: Partial<Incidencia>): Observable<ApiResponse<Incidencia>> {
    return this.http.put<ApiResponse<Incidencia>>(`${API_URL}/api/incidencias.php?id=${id}`, incidencia);
  }

  deleteIncidencia(id: number): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${API_URL}/api/incidencias.php?id=${id}`);
  }

  getIncidenciasStats(): Observable<ApiResponse<any>> {
    return this.http.get<ApiResponse<any>>(`${API_URL}/api/incidencias.php?action=stats`);
  }

  getTiposIncidencia(): Observable<ApiResponse<TipoIncidencia[]>> {
    return this.http.get<ApiResponse<TipoIncidencia[]>>(`${API_URL}/api/incidencias.php?action=tipos`);
  }

  getIncidenciaHistorial(incidenciaId: number, limit: number = 50): Observable<ApiResponse<HistorialItem[]>> {
    return this.http.get<ApiResponse<HistorialItem[]>>(
      `${API_URL}/api/incidencias.php?action=historial&incidencia_id=${incidenciaId}&limit=${limit}`
    );
  }

  // ========== REPORTES ==========
  getPeriodos(): Observable<ApiResponse<Periodo[]>> {
    return this.http.get<ApiResponse<Periodo[]>>(`${API_URL}/api/reportes.php?tipo=periodos`);
  }

  getDashboard(periodo: string = 'todo', fechaInicio?: string, fechaFin?: string): Observable<ApiResponse<Dashboard>> {
    let params = new HttpParams().set('tipo', 'dashboard').set('periodo', periodo);
    if (fechaInicio) params = params.set('fecha_inicio', fechaInicio);
    if (fechaFin) params = params.set('fecha_fin', fechaFin);
    return this.http.get<ApiResponse<Dashboard>>(`${API_URL}/api/reportes.php`, { params });
  }

  // Reporte por Materia (Requisito MVP)
  getReportePorMateria(periodo: string = 'todo', fechaInicio?: string, fechaFin?: string): Observable<ApiResponse<ReportePorMateria>> {
    let params = new HttpParams().set('tipo', 'reporte_por_materia').set('periodo', periodo);
    if (fechaInicio) params = params.set('fecha_inicio', fechaInicio);
    if (fechaFin) params = params.set('fecha_fin', fechaFin);
    return this.http.get<ApiResponse<ReportePorMateria>>(`${API_URL}/api/reportes.php`, { params });
  }

  // URLs para exportación (se abren directamente en el navegador)
  getExportUrl(
    tipo: 'incidencias' | 'docentes' | 'estadisticas' | 'materias', 
    periodo: string = 'todo', 
    fechaInicio?: string, 
    fechaFin?: string,
    formato: 'csv' | 'xlsx' = 'csv'
  ): string {
    const token = localStorage.getItem('access_token');
    const suffix = formato === 'xlsx' ? '_xlsx' : '';
    let url = `${API_URL}/api/reportes.php?tipo=exportar_${tipo}${suffix}&periodo=${periodo}`;
    if (fechaInicio) url += `&fecha_inicio=${fechaInicio}`;
    if (fechaFin) url += `&fecha_fin=${fechaFin}`;
    if (token) url += `&token=${encodeURIComponent(token)}`;
    return url;
  }

  // ========== UPLOAD DE ARCHIVOS ==========
  uploadFile(file: File, incidenciaId?: number): Observable<ApiResponse<UploadedFile>> {
    const formData = new FormData();
    formData.append('file', file);
    if (incidenciaId) {
      formData.append('incidencia_id', incidenciaId.toString());
    }
    return this.http.post<ApiResponse<UploadedFile>>(`${API_URL}/api/upload.php`, formData);
  }

  getIncidenciaFiles(incidenciaId: number): Observable<ApiResponse<{ files: UploadedFile[], total: number }>> {
    return this.http.get<ApiResponse<{ files: UploadedFile[], total: number }>>(
      `${API_URL}/api/upload.php?incidencia_id=${incidenciaId}`
    );
  }

  deleteFile(filename: string, incidenciaId?: number): Observable<ApiResponse<any>> {
    let url = `${API_URL}/api/upload.php?filename=${encodeURIComponent(filename)}`;
    if (incidenciaId) {
      url += `&incidencia_id=${incidenciaId}`;
    }
    return this.http.delete<ApiResponse<any>>(url);
  }

  getFileUrl(path: string): string {
    return `${API_URL}/${path}`;
  }

  getDownloadUrl(incidenciaId: number, filename: string): string {
    return `${API_URL}/api/download.php?incidencia_id=${incidenciaId}&file=${encodeURIComponent(filename)}`;
  }

  // ========== CURSOS ==========
  getCursos(filters?: {
    estatus?: string;
    academia_id?: number;
    semestre?: number;
    modalidad?: string;
    search?: string;
  }): Observable<ApiResponse<Curso[]>> {
    let params = new HttpParams();
    if (filters) {
      Object.keys(filters).forEach(key => {
        const value = (filters as any)[key];
        if (value !== undefined && value !== null && value !== '') {
          params = params.set(key, value.toString());
        }
      });
    }
    return this.http.get<ApiResponse<Curso[]>>(`${API_URL}/api/cursos.php`, { params });
  }

  getCurso(id: number): Observable<ApiResponse<Curso>> {
    return this.http.get<ApiResponse<Curso>>(`${API_URL}/api/cursos.php?id=${id}`);
  }

  createCurso(data: Curso): Observable<ApiResponse<Curso>> {
    return this.http.post<ApiResponse<Curso>>(`${API_URL}/api/cursos.php`, data);
  }

  updateCurso(id: number, data: Partial<Curso>): Observable<ApiResponse<Curso>> {
    return this.http.put<ApiResponse<Curso>>(`${API_URL}/api/cursos.php?id=${id}`, data);
  }

  deleteCurso(id: number): Observable<ApiResponse<any>> {
    return this.http.delete<ApiResponse<any>>(`${API_URL}/api/cursos.php?id=${id}`);
  }

  getCursosStats(): Observable<ApiResponse<CursoStats>> {
    return this.http.get<ApiResponse<CursoStats>>(`${API_URL}/api/cursos.php?action=stats`);
  }

  getCursosParaSelector(search?: string): Observable<ApiResponse<Curso[]>> {
    let url = `${API_URL}/api/cursos.php?action=selector`;
    if (search) {
      url += `&search=${encodeURIComponent(search)}`;
    }
    return this.http.get<ApiResponse<Curso[]>>(url);
  }

  getAcademias(): Observable<ApiResponse<{ id: number; nombre: string }[]>> {
    return this.http.get<ApiResponse<{ id: number; nombre: string }[]>>(`${API_URL}/api/cursos.php?action=academias`);
  }

  // ========== PERÍODOS ACADÉMICOS ==========
  getPeriodosAcademicos(estatus?: string): Observable<ApiResponse<PeriodoAcademico[]>> {
    let url = `${API_URL}/api/cursos.php?action=periodos`;
    if (estatus) {
      url += `&estatus=${estatus}`;
    }
    return this.http.get<ApiResponse<PeriodoAcademico[]>>(url);
  }

  getPeriodoActivo(): Observable<ApiResponse<PeriodoAcademico>> {
    return this.http.get<ApiResponse<PeriodoAcademico>>(`${API_URL}/api/cursos.php?action=periodo_activo`);
  }

  // ========== ASIGNACIONES DOCENTE-CURSO ==========
  getDocentesDelCurso(cursoId: number, periodo?: string): Observable<ApiResponse<DocenteCurso[]>> {
    let url = `${API_URL}/api/cursos.php?action=docentes&id=${cursoId}`;
    if (periodo) {
      url += `&periodo=${encodeURIComponent(periodo)}`;
    }
    return this.http.get<ApiResponse<DocenteCurso[]>>(url);
  }

  getCursosDelDocente(docenteId: number, periodo?: string): Observable<ApiResponse<DocenteCurso[]>> {
    let url = `${API_URL}/api/cursos.php?action=cursos_docente&docente_id=${docenteId}`;
    if (periodo) {
      url += `&periodo=${encodeURIComponent(periodo)}`;
    }
    return this.http.get<ApiResponse<DocenteCurso[]>>(url);
  }

  asignarDocenteACurso(data: DocenteCurso): Observable<ApiResponse<any>> {
    return this.http.post<ApiResponse<any>>(`${API_URL}/api/cursos.php?action=asignar_docente`, data);
  }

  actualizarAsignacion(id: number, data: Partial<DocenteCurso>): Observable<ApiResponse<any>> {
    return this.http.put<ApiResponse<any>>(`${API_URL}/api/cursos.php?action=asignacion&id=${id}`, data);
  }

  eliminarAsignacion(id: number): Observable<ApiResponse<any>> {
    return this.http.delete<ApiResponse<any>>(`${API_URL}/api/cursos.php?action=asignacion&id=${id}`);
  }

  // ========== EVALUACIONES DOCENTES ==========
  getEvaluaciones(filters?: {
    docente_id?: number;
    curso_id?: number;
    periodo_id?: number;
    tipo_evaluador?: string;
    estatus?: string;
  }): Observable<ApiResponse<EvaluacionDocente[]>> {
    let params = new HttpParams();
    if (filters) {
      Object.keys(filters).forEach(key => {
        const value = (filters as any)[key];
        if (value !== undefined && value !== null && value !== '') {
          params = params.set(key, value.toString());
        }
      });
    }
    return this.http.get<ApiResponse<EvaluacionDocente[]>>(`${API_URL}/api/evaluaciones.php`, { params });
  }

  getEvaluacion(id: number): Observable<ApiResponse<EvaluacionDocente>> {
    return this.http.get<ApiResponse<EvaluacionDocente>>(`${API_URL}/api/evaluaciones.php?id=${id}`);
  }

  createEvaluacion(data: EvaluacionDocente): Observable<ApiResponse<EvaluacionDocente>> {
    return this.http.post<ApiResponse<EvaluacionDocente>>(`${API_URL}/api/evaluaciones.php`, data);
  }

  updateEvaluacion(id: number, data: Partial<EvaluacionDocente>): Observable<ApiResponse<EvaluacionDocente>> {
    return this.http.put<ApiResponse<EvaluacionDocente>>(`${API_URL}/api/evaluaciones.php?id=${id}`, data);
  }

  deleteEvaluacion(id: number): Observable<ApiResponse<any>> {
    return this.http.delete<ApiResponse<any>>(`${API_URL}/api/evaluaciones.php?id=${id}`);
  }

  getEvaluacionesStats(): Observable<ApiResponse<EvaluacionStats>> {
    return this.http.get<ApiResponse<EvaluacionStats>>(`${API_URL}/api/evaluaciones.php?action=stats`);
  }

  getCriteriosEvaluacion(): Observable<ApiResponse<CriterioEvaluacion[]>> {
    return this.http.get<ApiResponse<CriterioEvaluacion[]>>(`${API_URL}/api/evaluaciones.php?action=criterios`);
  }

  getPeriodosEvaluacion(estatus?: string): Observable<ApiResponse<PeriodoEvaluacion[]>> {
    let url = `${API_URL}/api/evaluaciones.php?action=periodos`;
    if (estatus) {
      url += `&estatus=${estatus}`;
    }
    return this.http.get<ApiResponse<PeriodoEvaluacion[]>>(url);
  }

  getResumenEvaluacionDocente(docenteId: number): Observable<ApiResponse<ResumenEvaluacionDocente>> {
    return this.http.get<ApiResponse<ResumenEvaluacionDocente>>(
      `${API_URL}/api/evaluaciones.php?action=resumen_docente&docente_id=${docenteId}`
    );
  }

  getEvaluacionesDocente(docenteId: number): Observable<ApiResponse<EvaluacionDocente[]>> {
    return this.http.get<ApiResponse<EvaluacionDocente[]>>(
      `${API_URL}/api/evaluaciones.php?action=docente&docente_id=${docenteId}`
    );
  }
}

