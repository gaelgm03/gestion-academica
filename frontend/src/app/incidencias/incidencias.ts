
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService, Incidencia, Docente, TipoIncidencia } from '../services/api.service';

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
  loading = true;
  error: string | null = null;
  showForm = false;
  editingIncidencia: Incidencia | null = null;
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

  filters = {
    status: '',
    prioridad: '',
    tipo_id: ''
  };

  constructor(private apiService: ApiService) {}

  ngOnInit() {
    this.loadIncidencias();
    this.loadDocentes();
    this.loadTiposIncidencia();
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

  openForm(incidencia?: Incidencia) {
    if (incidencia) {
      this.editingIncidencia = incidencia;
      this.formData = { ...incidencia };
    } else {
      this.resetForm();
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
  }

  saveIncidencia() {
    if (!this.formData.tipo_id) {
      alert('El tipo de incidencia es requerido');
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
}
