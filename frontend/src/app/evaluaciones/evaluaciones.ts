import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService, EvaluacionDocente, CriterioEvaluacion, PeriodoEvaluacion, Docente, Curso, EvaluacionDetalle, EvaluacionStats } from '../services/api.service';
import { PdfService } from '../services/pdf.service';

@Component({
  selector: 'app-evaluaciones',
  imports: [CommonModule, FormsModule],
  templateUrl: './evaluaciones.html',
  styleUrl: './evaluaciones.css',
})
export class Evaluaciones implements OnInit {
  evaluaciones: EvaluacionDocente[] = [];
  docentes: Docente[] = [];
  cursos: Curso[] = [];
  criterios: CriterioEvaluacion[] = [];
  periodos: PeriodoEvaluacion[] = [];
  stats: EvaluacionStats | null = null;
  loading = true;
  error: string | null = null;
  showForm = false;
  editingEvaluacion: EvaluacionDocente | null = null;
  
  // Vista detalle
  showDetail = false;
  selectedEvaluacion: EvaluacionDocente | null = null;

  formData: EvaluacionDocente = {
    docente_id: 0,
    tipo_evaluador: 'alumno',
    estatus: 'borrador',
    calificacion_global: undefined,
    comentarios: '',
    fortalezas: '',
    areas_mejora: '',
    recomendaciones: ''
  };

  // Calificaciones por criterio
  calificacionesCriterios: { [key: number]: number } = {};

  filters = {
    docente_id: '',
    tipo_evaluador: '',
    estatus: '',
    periodo_id: ''
  };

  constructor(private apiService: ApiService, private pdfService: PdfService) {}

  ngOnInit() {
    this.loadEvaluaciones();
    this.loadDocentes();
    this.loadCursos();
    this.loadCriterios();
    this.loadPeriodos();
    this.loadStats();
  }

  loadEvaluaciones() {
    this.loading = true;
    this.error = null;
    
    const filters: any = {};
    if (this.filters.docente_id) filters.docente_id = this.filters.docente_id;
    if (this.filters.tipo_evaluador) filters.tipo_evaluador = this.filters.tipo_evaluador;
    if (this.filters.estatus) filters.estatus = this.filters.estatus;
    if (this.filters.periodo_id) filters.periodo_id = this.filters.periodo_id;

    this.apiService.getEvaluaciones(filters).subscribe({
      next: (response) => {
        if (response.success) {
          this.evaluaciones = response.data;
        } else {
          this.error = response.message;
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Error al cargar evaluaciones: ' + (err.error?.message || err.message);
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

  loadCursos() {
    this.apiService.getCursosParaSelector().subscribe({
      next: (response) => {
        if (response.success) {
          this.cursos = response.data;
        }
      }
    });
  }

  loadCriterios() {
    this.apiService.getCriteriosEvaluacion().subscribe({
      next: (response) => {
        if (response.success) {
          this.criterios = response.data;
          // Inicializar calificaciones
          this.criterios.forEach(c => {
            this.calificacionesCriterios[c.id] = 0;
          });
        }
      }
    });
  }

  loadPeriodos() {
    this.apiService.getPeriodosEvaluacion().subscribe({
      next: (response) => {
        if (response.success) {
          this.periodos = response.data;
        }
      }
    });
  }

  loadStats() {
    this.apiService.getEvaluacionesStats().subscribe({
      next: (response) => {
        if (response.success) {
          this.stats = response.data;
        }
      }
    });
  }

  openForm(evaluacion?: EvaluacionDocente) {
    if (evaluacion) {
      this.editingEvaluacion = evaluacion;
      this.formData = { ...evaluacion };
      // Cargar detalles
      if (evaluacion.detalles) {
        evaluacion.detalles.forEach(d => {
          this.calificacionesCriterios[d.criterio_id] = d.calificacion;
        });
      }
    } else {
      this.resetForm();
    }
    this.showForm = true;
  }

  closeForm() {
    this.showForm = false;
    this.editingEvaluacion = null;
    this.resetForm();
  }

  resetForm() {
    this.formData = {
      docente_id: 0,
      tipo_evaluador: 'alumno',
      estatus: 'borrador',
      calificacion_global: undefined,
      comentarios: '',
      fortalezas: '',
      areas_mejora: '',
      recomendaciones: ''
    };
    this.criterios.forEach(c => {
      this.calificacionesCriterios[c.id] = 0;
    });
    this.editingEvaluacion = null;
  }

  saveEvaluacion() {
    if (!this.formData.docente_id) {
      alert('Selecciona un docente');
      return;
    }

    // Preparar detalles
    const detalles: EvaluacionDetalle[] = [];
    let sumaPonderada = 0;
    let sumaPesos = 0;

    this.criterios.forEach(c => {
      const calif = this.calificacionesCriterios[c.id];
      if (calif > 0) {
        detalles.push({
          criterio_id: c.id,
          calificacion: calif
        });
        sumaPonderada += calif * c.peso;
        sumaPesos += c.peso;
      }
    });

    // Calcular promedio ponderado
    if (sumaPesos > 0) {
      this.formData.calificacion_global = Math.round((sumaPonderada / sumaPesos) * 100) / 100;
    }

    const dataToSave = {
      ...this.formData,
      detalles
    };

    if (this.editingEvaluacion && this.editingEvaluacion.id) {
      this.apiService.updateEvaluacion(this.editingEvaluacion.id, dataToSave).subscribe({
        next: (response) => {
          if (response.success) {
            alert('✓ Evaluación actualizada exitosamente');
            this.loadEvaluaciones();
            this.loadStats();
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
      this.apiService.createEvaluacion(dataToSave as EvaluacionDocente).subscribe({
        next: (response) => {
          if (response.success) {
            alert('✓ Evaluación creada exitosamente');
            this.loadEvaluaciones();
            this.loadStats();
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

  deleteEvaluacion(id: number) {
    if (confirm('¿Está seguro de eliminar esta evaluación?')) {
      this.apiService.deleteEvaluacion(id).subscribe({
        next: (response) => {
          if (response.success) {
            alert('✓ Evaluación eliminada');
            this.loadEvaluaciones();
            this.loadStats();
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

  openDetail(evaluacion: EvaluacionDocente) {
    // Cargar evaluación completa
    this.apiService.getEvaluacion(evaluacion.id!).subscribe({
      next: (response) => {
        if (response.success) {
          this.selectedEvaluacion = response.data;
          this.showDetail = true;
        }
      }
    });
  }

  closeDetail() {
    this.showDetail = false;
    this.selectedEvaluacion = null;
  }

  applyFilters() {
    this.loadEvaluaciones();
  }

  clearFilters() {
    this.filters = {
      docente_id: '',
      tipo_evaluador: '',
      estatus: '',
      periodo_id: ''
    };
    this.loadEvaluaciones();
  }

  // Helpers
  getCalificacionClass(calif: number | undefined): string {
    if (!calif) return '';
    if (calif >= 9) return 'excelente';
    if (calif >= 8) return 'muy-bueno';
    if (calif >= 7) return 'bueno';
    if (calif >= 6) return 'regular';
    return 'bajo';
  }

  getTipoEvaluadorLabel(tipo: string): string {
    const labels: { [key: string]: string } = {
      'alumno': '👨‍🎓 Alumno',
      'par': '👥 Par',
      'coordinador': '👔 Coordinador',
      'autoevaluacion': '🪞 Autoevaluación'
    };
    return labels[tipo] || tipo;
  }

  getEstatusClass(estatus: string): string {
    switch (estatus) {
      case 'completada': return 'badge-success';
      case 'borrador': return 'badge-warning';
      case 'revisada': return 'badge-info';
      default: return 'badge-secondary';
    }
  }

  getCategoriaLabel(categoria: string): string {
    const labels: { [key: string]: string } = {
      'conocimiento': '📚 Conocimiento',
      'metodologia': '🎯 Metodología',
      'comunicacion': '💬 Comunicación',
      'puntualidad': '⏰ Puntualidad',
      'material': '📋 Material',
      'evaluacion': '📝 Evaluación',
      'otro': '📌 Otro'
    };
    return labels[categoria] || categoria;
  }

  getCriteriosByCat(categoria: string): CriterioEvaluacion[] {
    return this.criterios.filter(c => c.categoria === categoria);
  }

  getCategorias(): string[] {
    return [...new Set(this.criterios.map(c => c.categoria))];
  }

  formatDate(dateString: string): string {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    });
  }

  // ========== EXPORTAR PDF ==========
  exportarPdf() {
    if (!this.stats) {
      alert('No hay estadísticas para exportar');
      return;
    }
    this.pdfService.exportarEstadisticasEvaluaciones(this.stats);
  }
}
