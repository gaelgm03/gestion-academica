import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService, Docente, AreaEspecialidad } from '../services/api.service';

@Component({
  selector: 'app-docentes',
  imports: [CommonModule, FormsModule],
  templateUrl: './docentes.html',
  styleUrl: './docentes.css',
})
export class Docentes implements OnInit {
  docentes: Docente[] = [];
  areasEspecialidad: AreaEspecialidad[] = [];
  loading = true;
  error: string | null = null;
  showForm = false;
  editingDocente: Docente | null = null;
  
  // Vista detalle
  showDetail = false;
  selectedDocente: Docente | null = null;
  docenteAreas: AreaEspecialidad[] = [];
  loadingDetail = false;
  formData: Docente = {
    nombre: '',
    email: '',
    grados: '',
    idioma: '',
    sni: false,
    cvlink: '',
    estatus: 'activo',
    area_ids: []
  };
  selectedAreaIds: number[] = [];
  
  // Validación de formulario
  formErrors: { [key: string]: string } = {};
  formTouched: { [key: string]: boolean } = {};
  formSubmitted = false;

  filters = {
    estatus: '',
    sni: '',
    area_id: '',
    search: ''
  };

  constructor(private apiService: ApiService) {}

  ngOnInit() {
    this.loadDocentes();
    this.loadAreasEspecialidad();
  }

  loadAreasEspecialidad() {
    this.apiService.getAreasEspecialidad().subscribe({
      next: (response) => {
        if (response.success) {
          this.areasEspecialidad = response.data;
        }
      },
      error: (err) => {
        console.error('Error al cargar áreas:', err);
      }
    });
  }

  loadDocentes() {
    this.loading = true;
    this.error = null;
    
    const filters: any = {};
    if (this.filters.estatus) filters.estatus = this.filters.estatus;
    if (this.filters.sni !== '') filters.sni = this.filters.sni;
    if (this.filters.area_id) filters.area_id = this.filters.area_id;
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
      // Parsear area_ids si viene como string
      if (docente.area_ids) {
        this.selectedAreaIds = Array.isArray(docente.area_ids) 
          ? docente.area_ids 
          : String(docente.area_ids).split(',').map(id => parseInt(id.trim(), 10)).filter(id => !isNaN(id));
      } else {
        this.selectedAreaIds = [];
      }
    } else {
      this.editingDocente = null;
      this.formData = {
        nombre: '',
        email: '',
        grados: '',
        idioma: '',
        sni: false,
        cvlink: '',
        estatus: 'activo',
        area_ids: []
      };
      this.selectedAreaIds = [];
    }
    this.showForm = true;
  }

  closeForm() {
    this.showForm = false;
    this.editingDocente = null;
    this.selectedAreaIds = [];
    this.formData = {
      nombre: '',
      email: '',
      grados: '',
      idioma: '',
      sni: false,
      cvlink: '',
      estatus: 'activo',
      area_ids: []
    };
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
      case 'nombre':
        if (!this.formData.nombre || this.formData.nombre.trim().length < 3) {
          this.formErrors[field] = 'El nombre debe tener al menos 3 caracteres';
          return false;
        }
        break;
      case 'email':
        const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!this.formData.email) {
          this.formErrors[field] = 'El email es requerido';
          return false;
        }
        if (!emailRegex.test(this.formData.email)) {
          this.formErrors[field] = 'Ingresa un email válido';
          return false;
        }
        break;
      case 'cvlink':
        if (this.formData.cvlink && this.formData.cvlink.trim()) {
          const urlRegex = /^https?:\/\/.+/;
          if (!urlRegex.test(this.formData.cvlink)) {
            this.formErrors[field] = 'El link debe comenzar con http:// o https://';
            return false;
          }
        }
        break;
    }
    return true;
  }

  validateForm(): boolean {
    this.formSubmitted = true;
    let isValid = true;

    // Validar campos requeridos
    if (!this.validateField('nombre')) isValid = false;
    if (!this.validateField('email')) isValid = false;
    if (!this.validateField('cvlink')) isValid = false;

    return isValid;
  }

  hasError(field: string): boolean {
    return !!(this.formErrors[field] && (this.formTouched[field] || this.formSubmitted));
  }

  getError(field: string): string {
    return this.formErrors[field] || '';
  }

  toggleArea(areaId: number) {
    const index = this.selectedAreaIds.indexOf(areaId);
    if (index > -1) {
      this.selectedAreaIds.splice(index, 1);
    } else {
      this.selectedAreaIds.push(areaId);
    }
  }

  isAreaSelected(areaId: number): boolean {
    return this.selectedAreaIds.includes(areaId);
  }

  getAreaNombre(areaId: number): string {
    const area = this.areasEspecialidad.find(a => a.id === areaId);
    return area ? area.nombre : '';
  }

  saveDocente() {
    // Validar formulario antes de guardar
    if (!this.validateForm()) {
      return;
    }

    // Agregar áreas seleccionadas al formData
    const dataToSave = {
      ...this.formData,
      area_ids: this.selectedAreaIds
    };

    if (this.editingDocente && this.editingDocente.id) {
      // Actualizar
      this.apiService.updateDocente(this.editingDocente.id, dataToSave).subscribe({
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
      this.apiService.createDocente(dataToSave).subscribe({
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
      area_id: '',
      search: ''
    };
    this.loadDocentes();
  }

  // ========== VISTA DETALLE ==========
  openDetail(docente: Docente) {
    this.selectedDocente = docente;
    this.showDetail = true;
    this.loadDocenteAreas(docente.id!);
  }

  closeDetail() {
    this.showDetail = false;
    this.selectedDocente = null;
    this.docenteAreas = [];
  }

  loadDocenteAreas(docenteId: number) {
    this.loadingDetail = true;
    this.apiService.getAreasDelDocente(docenteId).subscribe({
      next: (response) => {
        if (response.success) {
          this.docenteAreas = response.data;
        }
        this.loadingDetail = false;
      },
      error: (err) => {
        console.error('Error al cargar áreas del docente:', err);
        this.loadingDetail = false;
      }
    });
  }

  editFromDetail() {
    if (this.selectedDocente) {
      this.closeDetail();
      this.openForm(this.selectedDocente);
    }
  }
}
