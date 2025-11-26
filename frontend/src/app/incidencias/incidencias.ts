
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService, Incidencia, Docente, TipoIncidencia, UploadedFile, HistorialItem, Curso } from '../services/api.service';
import { PdfService } from '../services/pdf.service';

@Component({
  selector: 'app-incidencias',
  imports: [CommonModule, FormsModule],
  templateUrl: './incidencias.html',
  styleUrl: './incidencias.css',
})
export class Incidencias implements OnInit {
  incidencias: Incidencia[] = [];
  docentes: Docente[] = [];
  tiposIncidencia: TipoIncidencia[] = [];
  cursos: Curso[] = [];
  loading = true;
  error: string | null = null;
  showForm = false;
  editingIncidencia: Incidencia | null = null;
  
  // Vista detalle
  showDetail = false;
  selectedIncidencia: Incidencia | null = null;
  detailFiles: UploadedFile[] = [];
  detailHistorial: HistorialItem[] = [];
  loadingDetail = false;
  loadingHistorial = false;
  
  // Upload de archivos
  uploadedFiles: UploadedFile[] = [];
  uploading = false;
  selectedFile: File | null = null;
  formData: Incidencia = {
    tipo_id: undefined,
    profesor: undefined,
    curso: '',
    prioridad: 'Media',
    sla: '',
    asignadoA: undefined,
    evidencias: '',
    status: 'abierto'
  };

  // Validación de formulario
  formErrors: { [key: string]: string } = {};
  formTouched: { [key: string]: boolean } = {};
  formSubmitted = false;

  filters = {
    status: '',
    prioridad: '',
    tipo_id: ''
  };

  constructor(private apiService: ApiService, private pdfService: PdfService) {}

  ngOnInit() {
    this.loadIncidencias();
    this.loadDocentes();
    this.loadTiposIncidencia();
    this.loadCursos();
  }

  loadIncidencias() {
    this.loading = true;
    this.error = null;
    
    const filters: any = {};
    if (this.filters.status) filters.status = this.filters.status;
    if (this.filters.prioridad) filters.prioridad = this.filters.prioridad;
    if (this.filters.tipo_id) filters.tipo_id = this.filters.tipo_id;

    this.apiService.getIncidencias(filters).subscribe({
      next: (response) => {
        if (response.success) {
          this.incidencias = response.data;
        } else {
          this.error = response.message;
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Error al cargar incidencias: ' + (err.message || 'Error desconocido');
        this.loading = false;
      }
    });
  }

  loadDocentes() {
    this.apiService.getDocentes().subscribe({
      next: (response) => {
        if (response.success) {
          this.docentes = response.data;
        }
      },
      error: (err) => {
        // Error silenciado - los docentes son opcionales en el formulario
      }
    });
  }

  loadTiposIncidencia() {
    this.apiService.getTiposIncidencia().subscribe({
      next: (response) => {
        if (response.success) {
          this.tiposIncidencia = response.data;
        }
      },
      error: (err) => {
        // Error silenciado - los tipos se cargan de forma opcional
      }
    });
  }

  loadCursos() {
    this.apiService.getCursosParaSelector().subscribe({
      next: (response) => {
        if (response.success) {
          this.cursos = response.data;
        }
      },
      error: (err) => {
        // Error silenciado - los cursos se cargan de forma opcional
      }
    });
  }

  openForm(incidencia?: Incidencia) {
    if (incidencia) {
      this.editingIncidencia = incidencia;
      this.formData = { ...incidencia };
      // Cargar archivos si es edición
      if (incidencia.id) {
        this.loadFiles(incidencia.id);
      }
    } else {
      this.resetForm();
      this.uploadedFiles = [];
    }
    this.showForm = true;
  }

  closeForm() {
    this.showForm = false;
    this.editingIncidencia = null;
    this.resetForm();
  }

  resetForm() {
    this.formData = {
      tipo_id: undefined,
      profesor: undefined,
      curso: '',
      prioridad: 'Media',
      sla: '',
      asignadoA: undefined,
      evidencias: '',
      status: 'abierto'
    };
    this.editingIncidencia = null;
    // Limpiar validaciones
    this.formErrors = {};
    this.formTouched = {};
    this.formSubmitted = false;
  }

  // ========== VALIDACIÓN ==========
  validateField(field: string): boolean {
    this.formTouched[field] = true;
    delete this.formErrors[field];

    switch (field) {
      case 'tipo_id':
        if (!this.formData.tipo_id) {
          this.formErrors[field] = 'Selecciona un tipo de incidencia';
          return false;
        }
        break;
      case 'curso':
        if (!this.formData.curso || this.formData.curso.trim().length < 2) {
          this.formErrors[field] = 'El curso debe tener al menos 2 caracteres';
          return false;
        }
        break;
      case 'prioridad':
        if (!this.formData.prioridad) {
          this.formErrors[field] = 'Selecciona una prioridad';
          return false;
        }
        break;
    }
    return true;
  }

  validateForm(): boolean {
    this.formSubmitted = true;
    let isValid = true;

    if (!this.validateField('tipo_id')) isValid = false;
    if (!this.validateField('curso')) isValid = false;
    if (!this.validateField('prioridad')) isValid = false;

    return isValid;
  }

  hasError(field: string): boolean {
    return !!(this.formErrors[field] && (this.formTouched[field] || this.formSubmitted));
  }

  getError(field: string): string {
    return this.formErrors[field] || '';
  }

  saveIncidencia() {
    if (!this.validateForm()) {
      return;
    }

    if (this.editingIncidencia && this.editingIncidencia.id) {
      // Actualizar
      this.apiService.updateIncidencia(this.editingIncidencia.id, this.formData).subscribe({
        next: (response) => {
          if (response.success) {
            alert('✓ Incidencia actualizada exitosamente');
            this.loadIncidencias();
            this.closeForm();
          } else {
            alert('Error: ' + response.message);
          }
        },
        error: (err) => {
          const errorMsg = err.error?.message || err.message || 'Error desconocido';
          alert('Error al actualizar: ' + errorMsg);
        }
      });
    } else {
      // Crear
      this.apiService.createIncidencia(this.formData).subscribe({
        next: (response) => {
          if (response.success) {
            alert('✓ Incidencia creada exitosamente');
            this.loadIncidencias();
            this.closeForm();
          } else {
            alert('Error: ' + response.message);
          }
        },
        error: (err) => {
          const errorMsg = err.error?.message || err.message || 'Error desconocido';
          alert('Error al crear: ' + errorMsg);
        }
      });
    }
  }

  deleteIncidencia(id: number) {
    if (confirm('¿Está seguro de eliminar esta incidencia?')) {
      this.apiService.deleteIncidencia(id).subscribe({
        next: (response) => {
          if (response.success) {
            alert('✓ Incidencia eliminada exitosamente');
            this.loadIncidencias();
          } else {
            alert('Error: ' + response.message);
          }
        },
        error: (err) => {
          const errorMsg = err.error?.message || err.message || 'Error desconocido';
          alert('Error al eliminar: ' + errorMsg);
        }
      });
    }
  }

  applyFilters() {
    this.loadIncidencias();
  }

  clearFilters() {
    this.filters = {
      status: '',
      prioridad: '',
      tipo_id: ''
    };
    this.loadIncidencias();
  }

  getPrioridadClass(prioridad?: string): string {
    switch (prioridad) {
      case 'Alta': return 'badge-danger';
      case 'Media': return 'badge-warning';
      case 'Baja': return 'badge-info';
      default: return 'badge-secondary';
    }
  }

  getStatusClass(status?: string): string {
    switch (status) {
      case 'abierto': return 'badge-warning';
      case 'en proceso': return 'badge-info';
      case 'cerrado': return 'badge-success';
      default: return 'badge-secondary';
    }
  }

  formatDate(dateString: string): string {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('es-ES', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  getTipoDescripcion(): string {
    if (!this.formData.tipo_id) return '';
    const tipo = this.tiposIncidencia.find(t => t.id === this.formData.tipo_id);
    return tipo?.descripcion || '';
  }

  // ========== MANEJO DE ARCHIVOS ==========
  
  onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.selectedFile = input.files[0];
    }
  }

  uploadFile() {
    if (!this.selectedFile) {
      alert('Selecciona un archivo primero');
      return;
    }

    // Validar tamaño (10MB)
    if (this.selectedFile.size > 10 * 1024 * 1024) {
      alert('El archivo excede el tamaño máximo de 10MB');
      return;
    }

    this.uploading = true;
    const incidenciaId = this.editingIncidencia?.id;

    this.apiService.uploadFile(this.selectedFile, incidenciaId).subscribe({
      next: (response) => {
        if (response.success) {
          this.uploadedFiles.push(response.data);
          this.selectedFile = null;
          // Limpiar input file
          const fileInput = document.getElementById('fileInput') as HTMLInputElement;
          if (fileInput) fileInput.value = '';
          
          // Actualizar formData.evidencias (tanto para nuevas como existentes)
          const currentFiles = this.formData.evidencias ? this.formData.evidencias.split(',').filter(f => f.trim()) : [];
          currentFiles.push(response.data.filename);
          this.formData.evidencias = currentFiles.join(',');
          
          // Refrescar lista de incidencias si es edición
          if (incidenciaId) {
            this.loadIncidencias();
          }
        } else {
          alert('Error: ' + response.message);
        }
        this.uploading = false;
      },
      error: (err) => {
        alert('Error al subir archivo: ' + (err.error?.message || err.message));
        this.uploading = false;
      }
    });
  }

  loadFiles(incidenciaId: number) {
    this.apiService.getIncidenciaFiles(incidenciaId).subscribe({
      next: (response) => {
        if (response.success) {
          this.uploadedFiles = response.data.files;
        }
      },
      error: (err) => {
        console.error('Error al cargar archivos:', err);
      }
    });
  }

  deleteFile(file: UploadedFile) {
    if (!confirm(`¿Eliminar el archivo ${file.filename}?`)) return;

    const incidenciaId = this.editingIncidencia?.id;
    
    this.apiService.deleteFile(file.filename, incidenciaId).subscribe({
      next: (response) => {
        if (response.success) {
          this.uploadedFiles = this.uploadedFiles.filter(f => f.filename !== file.filename);
          // Actualizar también el formData.evidencias
          if (this.formData.evidencias) {
            const files = this.formData.evidencias.split(',').filter(f => f.trim() !== file.filename);
            this.formData.evidencias = files.length > 0 ? files.join(',') : null as any;
          }
          // Refrescar lista de incidencias para actualizar iconos en la tabla
          this.loadIncidencias();
        } else {
          alert('Error: ' + response.message);
        }
      },
      error: (err) => {
        alert('Error al eliminar: ' + (err.error?.message || err.message));
      }
    });
  }

  getFileUrl(file: UploadedFile): string {
    // Usar endpoint de descarga si tenemos incidencia_id
    if (this.editingIncidencia?.id) {
      return this.apiService.getDownloadUrl(this.editingIncidencia.id, file.filename);
    }
    return this.apiService.getFileUrl(file.path);
  }

  formatFileSize(bytes: number): string {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
  }

  getFileIcon(filename: string): string {
    const ext = filename.split('.').pop()?.toLowerCase() || '';
    switch (ext) {
      case 'pdf': return '📄';
      case 'doc': case 'docx': return '🗒️';
      case 'xls': case 'xlsx': return '📊';
      case 'jpg': case 'jpeg': case 'png': case 'gif': case 'webp': return '🖼️';
      default: return '📁';
    }
  }

  // Helpers para mostrar evidencias en la tabla
  getEvidenciasArray(evidencias: string): string[] {
    if (!evidencias) return [];
    return evidencias.split(',').map(f => f.trim()).filter(f => f);
  }

  getEvidenciaUrl(incidenciaId: number, filename: string): string {
    return this.apiService.getDownloadUrl(incidenciaId, filename);
  }

  // ========== VISTA DETALLE ==========
  openDetail(incidencia: Incidencia) {
    this.selectedIncidencia = incidencia;
    this.showDetail = true;
    if (incidencia.id) {
      this.loadDetailFiles(incidencia.id);
      this.loadDetailHistorial(incidencia.id);
    }
  }

  closeDetail() {
    this.showDetail = false;
    this.selectedIncidencia = null;
    this.detailFiles = [];
    this.detailHistorial = [];
  }

  loadDetailFiles(incidenciaId: number) {
    this.loadingDetail = true;
    this.apiService.getIncidenciaFiles(incidenciaId).subscribe({
      next: (response) => {
        if (response.success) {
          this.detailFiles = response.data.files;
        }
        this.loadingDetail = false;
      },
      error: (err) => {
        console.error('Error al cargar archivos:', err);
        this.loadingDetail = false;
      }
    });
  }

  loadDetailHistorial(incidenciaId: number) {
    this.loadingHistorial = true;
    this.apiService.getIncidenciaHistorial(incidenciaId).subscribe({
      next: (response) => {
        if (response.success) {
          this.detailHistorial = response.data;
        }
        this.loadingHistorial = false;
      },
      error: (err) => {
        console.error('Error al cargar historial:', err);
        this.loadingHistorial = false;
      }
    });
  }

  formatAccion(accion: string): string {
    const acciones: { [key: string]: string } = {
      'crear': '🆕 Creación',
      'editar': '✏️ Edición',
      'eliminar': '🗑️ Eliminación',
      'cambio_status': '🔄 Cambio de Estado',
      'asignar': '👤 Asignación'
    };
    return acciones[accion] || accion;
  }

  formatCampo(campo: string): string {
    const campos: { [key: string]: string } = {
      'tipo_id': 'Tipo',
      'profesor': 'Profesor',
      'curso': 'Curso',
      'prioridad': 'Prioridad',
      'sla': 'SLA',
      'asignadoA': 'Asignado a',
      'status': 'Estado',
      'evidencias': 'Evidencias',
      'incidencia': 'Incidencia'
    };
    return campos[campo] || campo;
  }

  editFromDetail() {
    if (this.selectedIncidencia) {
      this.closeDetail();
      this.openForm(this.selectedIncidencia);
    }
  }

  getProfesorNombreById(profesorId?: number): string {
    if (!profesorId) return 'No asignado';
    const docente = this.docentes.find(d => d.id === profesorId);
    return docente?.nombre || 'Desconocido';
  }

  getAsignadoNombreById(asignadoId?: number): string {
    if (!asignadoId) return 'Sin asignar';
    const docente = this.docentes.find(d => d.id === asignadoId);
    return docente?.nombre || 'Desconocido';
  }

  // ========== EXPORTAR PDF ==========
  exportarPdf() {
    if (this.incidencias.length === 0) {
      alert('No hay incidencias para exportar');
      return;
    }
    this.pdfService.exportarIncidencias(this.incidencias);
  }
}
