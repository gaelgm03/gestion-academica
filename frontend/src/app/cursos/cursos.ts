import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService, Curso, DocenteCurso, Docente, PeriodoAcademico } from '../services/api.service';
import { AuthService } from '../services/auth.service';
import { PdfService } from '../services/pdf.service';

@Component({
  selector: 'app-cursos',
  imports: [CommonModule, FormsModule],
  templateUrl: './cursos.html',
  styleUrl: './cursos.css',
})
export class Cursos implements OnInit {
  cursos: Curso[] = [];
  docentes: Docente[] = [];
  periodos: PeriodoAcademico[] = [];
  academias: { id: number; nombre: string }[] = [];
  loading = true;
  error: string | null = null;
  showForm = false;
  editingCurso: Curso | null = null;
  
  // Vista detalle
  showDetail = false;
  selectedCurso: Curso | null = null;
  cursoDocentes: DocenteCurso[] = [];
  loadingDetail = false;
  
  // Modal asignación
  showAsignacion = false;
  asignacionData: DocenteCurso = {
    docente_id: 0,
    curso_id: 0,
    periodo: '',
    grupo: 'A',
    horario: '',
    aula: '',
    cupo_maximo: 30,
    inscritos: 0
  };

  formData: Curso = {
    codigo: '',
    nombre: '',
    descripcion: '',
    creditos: 0,
    horas_semana: 0,
    semestre: undefined,
    modalidad: 'presencial',
    academia_id: undefined,
    estatus: 'activo'
  };

  // Validación
  formErrors: { [key: string]: string } = {};
  formTouched: { [key: string]: boolean } = {};
  formSubmitted = false;

  filters = {
    estatus: '',
    academia_id: '',
    semestre: '',
    modalidad: '',
    search: ''
  };

  constructor(
    private apiService: ApiService,
    private authService: AuthService,
    private pdfService: PdfService
  ) {}

  // Métodos de permisos
  canCreate(): boolean {
    return this.authService.hasPermission('academia', 'gestionar');
  }

  canEdit(): boolean {
    return this.authService.hasPermission('academia', 'gestionar');
  }

  canDelete(): boolean {
    // Solo admin puede eliminar cursos
    return this.authService.hasRole(['admin']);
  }

  canExport(): boolean {
    return this.authService.hasPermission('reporte', 'exportar');
  }

  canAssign(): boolean {
    // Puede asignar docentes si puede gestionar academia
    return this.authService.hasPermission('academia', 'gestionar');
  }

  ngOnInit() {
    this.loadCursos();
    this.loadDocentes();
    this.loadPeriodos();
    this.loadAcademias();
  }

  loadCursos() {
    this.loading = true;
    this.error = null;
    
    const filters: any = {};
    if (this.filters.estatus) filters.estatus = this.filters.estatus;
    if (this.filters.academia_id) filters.academia_id = this.filters.academia_id;
    if (this.filters.semestre) filters.semestre = this.filters.semestre;
    if (this.filters.modalidad) filters.modalidad = this.filters.modalidad;
    if (this.filters.search) filters.search = this.filters.search;

    this.apiService.getCursos(filters).subscribe({
      next: (response) => {
        if (response.success) {
          this.cursos = response.data;
        } else {
          this.error = response.message;
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Error al cargar cursos: ' + (err.error?.message || err.message || 'Error desconocido');
        this.loading = false;
      }
    });
  }

  loadDocentes() {
    this.apiService.getDocentes({ estatus: 'activo' }).subscribe({
      next: (response) => {
        if (response.success) {
          this.docentes = response.data;
        }
      }
    });
  }

  loadPeriodos() {
    this.apiService.getPeriodosAcademicos().subscribe({
      next: (response) => {
        if (response.success) {
          this.periodos = response.data;
        }
      }
    });
  }

  loadAcademias() {
    // Usar endpoint de academias (tabla academia, no area_especialidad)
    this.apiService.getAcademias().subscribe({
      next: (response) => {
        if (response.success) {
          this.academias = response.data;
        }
      },
      error: (err) => {
        console.error('Error al cargar academias:', err);
      }
    });
  }

  openForm(curso?: Curso) {
    if (curso) {
      this.editingCurso = curso;
      this.formData = { ...curso };
    } else {
      this.resetForm();
    }
    this.showForm = true;
  }

  closeForm() {
    this.showForm = false;
    this.editingCurso = null;
    this.resetForm();
  }

  resetForm() {
    this.formData = {
      codigo: '',
      nombre: '',
      descripcion: '',
      creditos: 0,
      horas_semana: 0,
      semestre: undefined,
      modalidad: 'presencial',
      academia_id: undefined,
      estatus: 'activo'
    };
    this.editingCurso = null;
    this.formErrors = {};
    this.formTouched = {};
    this.formSubmitted = false;
  }

  // ========== VALIDACIÓN ==========
  validateField(field: string): boolean {
    this.formTouched[field] = true;
    delete this.formErrors[field];

    switch (field) {
      case 'codigo':
        if (!this.formData.codigo || this.formData.codigo.trim().length < 3) {
          this.formErrors[field] = 'El código debe tener al menos 3 caracteres';
          return false;
        }
        break;
      case 'nombre':
        if (!this.formData.nombre || this.formData.nombre.trim().length < 5) {
          this.formErrors[field] = 'El nombre debe tener al menos 5 caracteres';
          return false;
        }
        break;
      case 'creditos':
        if (this.formData.creditos !== undefined && (this.formData.creditos < 0 || this.formData.creditos > 20)) {
          this.formErrors[field] = 'Los créditos deben estar entre 0 y 20';
          return false;
        }
        break;
    }
    return true;
  }

  validateForm(): boolean {
    this.formSubmitted = true;
    let isValid = true;

    if (!this.validateField('codigo')) isValid = false;
    if (!this.validateField('nombre')) isValid = false;
    if (!this.validateField('creditos')) isValid = false;

    return isValid;
  }

  hasError(field: string): boolean {
    return !!(this.formErrors[field] && (this.formTouched[field] || this.formSubmitted));
  }

  getError(field: string): string {
    return this.formErrors[field] || '';
  }

  saveCurso() {
    if (!this.validateForm()) {
      return;
    }

    if (this.editingCurso && this.editingCurso.id) {
      this.apiService.updateCurso(this.editingCurso.id, this.formData).subscribe({
        next: (response) => {
          if (response.success) {
            alert('✓ Curso actualizado exitosamente');
            this.loadCursos();
            this.closeForm();
          } else {
            alert('Error: ' + response.message);
          }
        },
        error: (err) => {
          alert('Error al actualizar: ' + (err.error?.message || err.message));
        }
      });
    } else {
      this.apiService.createCurso(this.formData).subscribe({
        next: (response) => {
          if (response.success) {
            alert('✓ Curso creado exitosamente');
            this.loadCursos();
            this.closeForm();
          } else {
            alert('Error: ' + response.message);
          }
        },
        error: (err) => {
          alert('Error al crear: ' + (err.error?.message || err.message));
        }
      });
    }
  }

  deleteCurso(id: number) {
    if (confirm('¿Está seguro de eliminar este curso?')) {
      this.apiService.deleteCurso(id).subscribe({
        next: (response) => {
          if (response.success) {
            alert('✓ Curso eliminado exitosamente');
            this.loadCursos();
          } else {
            alert('Error: ' + response.message);
          }
        },
        error: (err) => {
          alert('Error al eliminar: ' + (err.error?.message || err.message));
        }
      });
    }
  }

  applyFilters() {
    this.loadCursos();
  }

  clearFilters() {
    this.filters = {
      estatus: '',
      academia_id: '',
      semestre: '',
      modalidad: '',
      search: ''
    };
    this.loadCursos();
  }

  // ========== VISTA DETALLE ==========
  openDetail(curso: Curso) {
    this.selectedCurso = curso;
    this.showDetail = true;
    if (curso.id) {
      this.loadCursoDocentes(curso.id);
    }
  }

  closeDetail() {
    this.showDetail = false;
    this.selectedCurso = null;
    this.cursoDocentes = [];
  }

  loadCursoDocentes(cursoId: number) {
    this.loadingDetail = true;
    this.apiService.getDocentesDelCurso(cursoId).subscribe({
      next: (response) => {
        if (response.success) {
          this.cursoDocentes = response.data;
        }
        this.loadingDetail = false;
      },
      error: (err) => {
        console.error('Error al cargar docentes:', err);
        this.loadingDetail = false;
      }
    });
  }

  editFromDetail() {
    if (this.selectedCurso) {
      this.closeDetail();
      this.openForm(this.selectedCurso);
    }
  }

  // ========== ASIGNACIÓN DE DOCENTES ==========
  openAsignacion(curso: Curso) {
    // Recargar lista de docentes para tener los más recientes
    this.loadDocentes();
    
    this.asignacionData = {
      docente_id: 0,
      curso_id: curso.id!,
      periodo: this.periodos.find(p => p.estatus === 'activo')?.codigo || '',
      grupo: 'A',
      horario: '',
      aula: '',
      cupo_maximo: 30,
      inscritos: 0
    };
    this.showAsignacion = true;
  }

  closeAsignacion() {
    this.showAsignacion = false;
  }

  saveAsignacion() {
    if (!this.asignacionData.docente_id || !this.asignacionData.periodo) {
      alert('Selecciona un docente y período');
      return;
    }

    this.apiService.asignarDocenteACurso(this.asignacionData).subscribe({
      next: (response) => {
        if (response.success) {
          alert('✓ Docente asignado exitosamente');
          this.closeAsignacion();
          if (this.selectedCurso?.id) {
            this.loadCursoDocentes(this.selectedCurso.id);
          }
          this.loadCursos();
        } else {
          alert('Error: ' + response.message);
        }
      },
      error: (err) => {
        alert('Error al asignar: ' + (err.error?.message || err.message));
      }
    });
  }

  deleteAsignacion(asignacionId: number) {
    if (confirm('¿Eliminar esta asignación?')) {
      this.apiService.eliminarAsignacion(asignacionId).subscribe({
        next: (response) => {
          if (response.success) {
            alert('✓ Asignación eliminada');
            if (this.selectedCurso?.id) {
              this.loadCursoDocentes(this.selectedCurso.id);
            }
            this.loadCursos();
          } else {
            alert('Error: ' + response.message);
          }
        },
        error: (err) => {
          alert('Error: ' + (err.error?.message || err.message));
        }
      });
    }
  }

  // ========== HELPERS ==========
  getModalidadClass(modalidad?: string): string {
    switch (modalidad) {
      case 'presencial': return 'badge-presencial';
      case 'virtual': return 'badge-virtual';
      case 'hibrido': return 'badge-hibrido';
      default: return 'badge-secondary';
    }
  }

  getEstatusClass(estatus?: string): string {
    return estatus === 'activo' ? 'badge-success' : 'badge-warning';
  }

  getDocenteNombre(docenteId: number): string {
    const docente = this.docentes.find(d => d.id === docenteId);
    return docente?.nombre || 'Desconocido';
  }

  getAcademiaNombre(academiaId?: number): string {
    if (!academiaId) return 'Sin academia';
    const academia = this.academias.find(a => a.id === academiaId);
    return academia?.nombre || 'Desconocida';
  }

  // ========== EXPORTAR PDF ==========
  exportarPdf() {
    if (this.cursos.length === 0) {
      alert('No hay cursos para exportar');
      return;
    }
    this.pdfService.exportarCursos(this.cursos);
  }
}
