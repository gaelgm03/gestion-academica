import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService, Docente } from '../services/api.service';

@Component({
  selector: 'app-docentes',
  imports: [CommonModule, FormsModule],
  templateUrl: './docentes.html',
  styleUrl: './docentes.css',
})
export class Docentes implements OnInit {
  docentes: Docente[] = [];
  loading = true;
  error: string | null = null;
  showForm = false;
  editingDocente: Docente | null = null;
  formData: Docente = {
    nombre: '',
    email: '',
    grados: '',
    idioma: '',
    sni: false,
    cvlink: '',
    estatus: 'activo'
  };

  filters = {
    estatus: '',
    sni: '',
    search: ''
  };

  constructor(private apiService: ApiService) {}

  ngOnInit() {
    this.loadDocentes();
  }

  loadDocentes() {
    this.loading = true;
    this.error = null;
    
    const filters: any = {};
    if (this.filters.estatus) filters.estatus = this.filters.estatus;
    if (this.filters.sni !== '') filters.sni = this.filters.sni;
    if (this.filters.search) filters.search = this.filters.search;

    this.apiService.getDocentes(filters).subscribe({
      next: (response) => {
        if (response.success) {
          this.docentes = response.data;
        } else {
          this.error = response.message;
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Error al cargar docentes: ' + (err.message || 'Error desconocido');
        this.loading = false;
      }
    });
  }

  openForm(docente?: Docente) {
    if (docente) {
      this.editingDocente = docente;
      this.formData = { ...docente };
    } else {
      this.editingDocente = null;
      this.formData = {
        nombre: '',
        email: '',
        grados: '',
        idioma: '',
        sni: false,
        cvlink: '',
        estatus: 'activo'
      };
    }
    this.showForm = true;
  }

  closeForm() {
    this.showForm = false;
    this.editingDocente = null;
    this.formData = {
      nombre: '',
      email: '',
      grados: '',
      idioma: '',
      sni: false,
      cvlink: '',
      estatus: 'activo'
    };
  }

  saveDocente() {
    if (!this.formData.nombre || !this.formData.email) {
      alert('Nombre y email son requeridos');
      return;
    }

    if (this.editingDocente && this.editingDocente.id) {
      // Actualizar
      this.apiService.updateDocente(this.editingDocente.id, this.formData).subscribe({
        next: (response) => {
          if (response.success) {
            alert('✓ Docente actualizado exitosamente');
            this.loadDocentes();
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
      this.apiService.createDocente(this.formData).subscribe({
        next: (response) => {
          if (response.success) {
            alert('✓ Docente creado exitosamente');
            this.loadDocentes();
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

  deleteDocente(id: number) {
    if (confirm('¿Está seguro de eliminar este docente?')) {
      this.apiService.deleteDocente(id).subscribe({
        next: (response) => {
          if (response.success) {
            alert('✓ Docente eliminado exitosamente');
            this.loadDocentes();
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
    this.loadDocentes();
  }

  clearFilters() {
    this.filters = {
      estatus: '',
      sni: '',
      search: ''
    };
    this.loadDocentes();
  }
}
