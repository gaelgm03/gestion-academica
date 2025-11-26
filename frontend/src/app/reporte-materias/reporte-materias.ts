import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { BaseChartDirective } from 'ng2-charts';
import { Chart, ChartConfiguration, ChartData, ChartType, registerables } from 'chart.js';
import { ApiService, ReportePorMateria, Periodo, CursoConIncidencias, CursoConDocentes, TopMateriaEvaluacion } from '../services/api.service';

// Registrar componentes de Chart.js
Chart.register(...registerables);

@Component({
  selector: 'app-reporte-materias',
  imports: [CommonModule, FormsModule, BaseChartDirective],
  templateUrl: './reporte-materias.html',
  styleUrl: './reporte-materias.css',
})
export class ReporteMaterias implements OnInit {
  reporteData: ReportePorMateria | null = null;
  loading = true;
  error: string | null = null;
  
  // Filtros
  periodos: Periodo[] = [];
  selectedPeriodo = 'todo';
  fechaInicio = '';
  fechaFin = '';
  showCustomDates = false;

  // Tabs
  activeTab: 'incidencias' | 'docentes' | 'distribucion' | 'evaluaciones' = 'incidencias';

  // Gráficas
  // Distribución por modalidad (Doughnut)
  modalidadChartType: ChartType = 'doughnut';
  modalidadChartData: ChartData<'doughnut'> = {
    labels: [],
    datasets: [{
      data: [],
      backgroundColor: ['#4caf50', '#2196f3', '#ff9800'],
      hoverBackgroundColor: ['#388e3c', '#1976d2', '#f57c00']
    }]
  };
  modalidadChartOptions: ChartConfiguration['options'] = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom' }
    }
  };

  // Distribución por academia (Bar)
  academiaChartType: ChartType = 'bar';
  academiaChartData: ChartData<'bar'> = {
    labels: [],
    datasets: [{
      label: 'Cursos',
      data: [],
      backgroundColor: '#1976d2',
      borderRadius: 6
    }]
  };
  academiaChartOptions: ChartConfiguration['options'] = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    plugins: {
      legend: { display: false }
    },
    scales: {
      x: { beginAtZero: true, ticks: { stepSize: 1 } }
    }
  };

  // Distribución por semestre (Bar)
  semestreChartType: ChartType = 'bar';
  semestreChartData: ChartData<'bar'> = {
    labels: [],
    datasets: [{
      label: 'Cursos',
      data: [],
      backgroundColor: '#4caf50',
      borderRadius: 6
    }]
  };
  semestreChartOptions: ChartConfiguration['options'] = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false }
    },
    scales: {
      y: { beginAtZero: true, ticks: { stepSize: 1 } }
    }
  };

  constructor(private apiService: ApiService) {}

  ngOnInit() {
    this.loadPeriodos();
    this.loadReporte();
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
      this.loadReporte();
    }
  }

  applyCustomDates() {
    if (this.fechaInicio && this.fechaFin) {
      this.loadReporte();
    }
  }

  loadReporte() {
    this.loading = true;
    this.error = null;
    
    const fechaInicio = this.selectedPeriodo === 'personalizado' ? this.fechaInicio : undefined;
    const fechaFin = this.selectedPeriodo === 'personalizado' ? this.fechaFin : undefined;
    
    this.apiService.getReportePorMateria(this.selectedPeriodo, fechaInicio, fechaFin).subscribe({
      next: (response) => {
        if (response.success) {
          this.reporteData = response.data;
          this.updateCharts();
        } else {
          this.error = response.message;
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Error al cargar el reporte: ' + (err.message || 'Error desconocido');
        this.loading = false;
        console.error('Error:', err);
      }
    });
  }

  updateCharts() {
    if (!this.reporteData) return;

    // Gráfica de modalidad
    if (this.reporteData.distribucion_modalidad) {
      this.modalidadChartData = {
        labels: this.reporteData.distribucion_modalidad.map(m => this.capitalize(m.modalidad)),
        datasets: [{
          data: this.reporteData.distribucion_modalidad.map(m => m.cantidad),
          backgroundColor: ['#4caf50', '#2196f3', '#ff9800'],
          hoverBackgroundColor: ['#388e3c', '#1976d2', '#f57c00']
        }]
      };
    }

    // Gráfica de academia
    if (this.reporteData.distribucion_academia) {
      this.academiaChartData = {
        labels: this.reporteData.distribucion_academia.map(a => a.academia),
        datasets: [{
          label: 'Cursos',
          data: this.reporteData.distribucion_academia.map(a => a.cantidad),
          backgroundColor: '#1976d2',
          borderRadius: 6
        }]
      };
    }

    // Gráfica de semestre
    if (this.reporteData.distribucion_semestre) {
      this.semestreChartData = {
        labels: this.reporteData.distribucion_semestre.map(s => s.semestre ? `Semestre ${s.semestre}` : 'Sin asignar'),
        datasets: [{
          label: 'Cursos',
          data: this.reporteData.distribucion_semestre.map(s => s.cantidad),
          backgroundColor: '#4caf50',
          borderRadius: 6
        }]
      };
    }
  }

  capitalize(text: string): string {
    return text ? text.charAt(0).toUpperCase() + text.slice(1) : '';
  }

  setActiveTab(tab: 'incidencias' | 'docentes' | 'distribucion' | 'evaluaciones') {
    this.activeTab = tab;
  }

  getIncidenciasClass(curso: CursoConIncidencias): string {
    if (curso.total_incidencias === 0) return '';
    if (curso.prioridad_alta > 0) return 'has-critical';
    if (curso.incidencias_abiertas > 0) return 'has-open';
    return '';
  }

  getEvaluacionClass(promedio: number): string {
    if (promedio >= 9) return 'eval-excellent';
    if (promedio >= 8) return 'eval-good';
    if (promedio >= 7) return 'eval-regular';
    return 'eval-low';
  }

  exportar(formato: 'csv' | 'xlsx') {
    const fechaInicio = this.selectedPeriodo === 'personalizado' ? this.fechaInicio : undefined;
    const fechaFin = this.selectedPeriodo === 'personalizado' ? this.fechaFin : undefined;
    const url = this.apiService.getExportUrl('materias', this.selectedPeriodo, fechaInicio, fechaFin, formato);
    window.open(url, '_blank');
  }

  exportarCSV() {
    this.exportar('csv');
  }

  exportarXLSX() {
    this.exportar('xlsx');
  }
}
