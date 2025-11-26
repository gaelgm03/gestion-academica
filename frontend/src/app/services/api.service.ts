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

  // URLs para exportación CSV (se abren directamente en el navegador)
  getExportUrl(tipo: 'incidencias' | 'docentes' | 'estadisticas', periodo: string = 'todo', fechaInicio?: string, fechaFin?: string): string {
    const token = localStorage.getItem('access_token');
    let url = `${API_URL}/api/reportes.php?tipo=exportar_${tipo}&periodo=${periodo}`;
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
}

