import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService, Dashboard as DashboardData, Periodo } from '../services/api.service';

@Component({
  selector: 'app-dashboard',
  imports: [CommonModule, FormsModule],
  templateUrl: './dashboard.html',
  styleUrl: './dashboard.css',
})
export class Dashboard implements OnInit {
  dashboardData: DashboardData | null = null;
  loading = true;
  error: string | null = null;
  
  // Filtros de período
  periodos: Periodo[] = [];
  selectedPeriodo = 'todo';
  fechaInicio = '';
  fechaFin = '';
  showCustomDates = false;

  constructor(private apiService: ApiService) {}

  ngOnInit() {
    this.loadPeriodos();
    this.loadDashboard();
  }

  loadPeriodos() {
    this.apiService.getPeriodos().subscribe({
      next: (response) => {
        if (response.success) {
          this.periodos = response.data;
        }
      },
      error: (err) => {
        console.error('Error al cargar períodos:', err);
      }
    });
  }

  onPeriodoChange() {
    this.showCustomDates = this.selectedPeriodo === 'personalizado';
    if (!this.showCustomDates) {
      this.loadDashboard();
    }
  }

  applyCustomDates() {
    if (this.fechaInicio && this.fechaFin) {
      this.loadDashboard();
    }
  }

  loadDashboard() {
    this.loading = true;
    this.error = null;
    
    const fechaInicio = this.selectedPeriodo === 'personalizado' ? this.fechaInicio : undefined;
    const fechaFin = this.selectedPeriodo === 'personalizado' ? this.fechaFin : undefined;
    
    this.apiService.getDashboard(this.selectedPeriodo, fechaInicio, fechaFin).subscribe({
      next: (response) => {
        if (response.success) {
          this.dashboardData = response.data;
        } else {
          this.error = response.message;
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Error al cargar el dashboard: ' + (err.message || 'Error desconocido');
        this.loading = false;
        console.error('Error:', err);
      }
    });
  }

  // Exportación CSV
  exportarIncidencias() {
    const fechaInicio = this.selectedPeriodo === 'personalizado' ? this.fechaInicio : undefined;
    const fechaFin = this.selectedPeriodo === 'personalizado' ? this.fechaFin : undefined;
    const url = this.apiService.getExportUrl('incidencias', this.selectedPeriodo, fechaInicio, fechaFin);
    window.open(url, '_blank');
  }

  exportarDocentes() {
    const url = this.apiService.getExportUrl('docentes');
    window.open(url, '_blank');
  }

  exportarEstadisticas() {
    const fechaInicio = this.selectedPeriodo === 'personalizado' ? this.fechaInicio : undefined;
    const fechaFin = this.selectedPeriodo === 'personalizado' ? this.fechaFin : undefined;
    const url = this.apiService.getExportUrl('estadisticas', this.selectedPeriodo, fechaInicio, fechaFin);
    window.open(url, '_blank');
  }
}
