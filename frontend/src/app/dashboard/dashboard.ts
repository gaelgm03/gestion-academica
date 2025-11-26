import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { BaseChartDirective } from 'ng2-charts';
import { Chart, ChartConfiguration, ChartData, ChartType, registerables } from 'chart.js';
import { ApiService, Dashboard as DashboardData, Periodo } from '../services/api.service';

// Registrar todos los componentes de Chart.js
Chart.register(...registerables);

@Component({
  selector: 'app-dashboard',
  imports: [CommonModule, FormsModule, BaseChartDirective],
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

  // Configuración de gráficas
  // Gráfica de incidencias por estado (Doughnut)
  estadoChartType: ChartType = 'doughnut';
  estadoChartData: ChartData<'doughnut'> = {
    labels: [],
    datasets: [{
      data: [],
      backgroundColor: ['#ff9800', '#2196f3', '#4caf50'],
      hoverBackgroundColor: ['#f57c00', '#1976d2', '#388e3c']
    }]
  };
  estadoChartOptions: ChartConfiguration['options'] = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom' }
    }
  };

  // Gráfica de incidencias por prioridad (Bar)
  prioridadChartType: ChartType = 'bar';
  prioridadChartData: ChartData<'bar'> = {
    labels: [],
    datasets: [{
      label: 'Cantidad',
      data: [],
      backgroundColor: ['#f44336', '#ff9800', '#4caf50'],
      borderRadius: 6
    }]
  };
  prioridadChartOptions: ChartConfiguration['options'] = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false }
    },
    scales: {
      y: { beginAtZero: true, ticks: { stepSize: 1 } }
    }
  };

  // Gráfica de docentes por estatus (Pie)
  docentesChartType: ChartType = 'pie';
  docentesChartData: ChartData<'pie'> = {
    labels: [],
    datasets: [{
      data: [],
      backgroundColor: ['#4caf50', '#9e9e9e'],
      hoverBackgroundColor: ['#388e3c', '#757575']
    }]
  };
  docentesChartOptions: ChartConfiguration['options'] = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom' }
    }
  };

  // Gráfica de tendencia diaria (Line)
  tendenciaChartType: ChartType = 'line';
  tendenciaChartData: ChartData<'line'> = {
    labels: [],
    datasets: [{
      label: 'Incidencias',
      data: [],
      borderColor: '#1976d2',
      backgroundColor: 'rgba(25, 118, 210, 0.1)',
      fill: true,
      tension: 0.3,
      pointRadius: 4,
      pointHoverRadius: 6
    }]
  };
  tendenciaChartOptions: ChartConfiguration['options'] = {
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
          this.updateCharts();
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

  // Actualizar datos de las gráficas
  updateCharts() {
    if (!this.dashboardData) return;

    // Actualizar gráfica de incidencias por estado
    if (this.dashboardData.incidencias_por_estado) {
      this.estadoChartData = {
        labels: this.dashboardData.incidencias_por_estado.map(i => this.capitalize(i.status)),
        datasets: [{
          data: this.dashboardData.incidencias_por_estado.map(i => i.cantidad),
          backgroundColor: ['#ff9800', '#2196f3', '#4caf50'],
          hoverBackgroundColor: ['#f57c00', '#1976d2', '#388e3c']
        }]
      };
    }

    // Actualizar gráfica de incidencias por prioridad
    if (this.dashboardData.incidencias_por_prioridad) {
      this.prioridadChartData = {
        labels: this.dashboardData.incidencias_por_prioridad.map(i => i.prioridad),
        datasets: [{
          label: 'Cantidad',
          data: this.dashboardData.incidencias_por_prioridad.map(i => i.cantidad),
          backgroundColor: ['#f44336', '#ff9800', '#4caf50'],
          borderRadius: 6
        }]
      };
    }

    // Actualizar gráfica de docentes por estatus
    if (this.dashboardData.docentes_por_estatus) {
      this.docentesChartData = {
        labels: this.dashboardData.docentes_por_estatus.map(d => this.capitalize(d.estatus)),
        datasets: [{
          data: this.dashboardData.docentes_por_estatus.map(d => d.cantidad),
          backgroundColor: ['#4caf50', '#9e9e9e'],
          hoverBackgroundColor: ['#388e3c', '#757575']
        }]
      };
    }

    // Actualizar gráfica de tendencia diaria
    if (this.dashboardData.tendencia_diaria && this.dashboardData.tendencia_diaria.length > 0) {
      this.tendenciaChartData = {
        labels: this.dashboardData.tendencia_diaria.map(t => this.formatDate(t.fecha)),
        datasets: [{
          label: 'Incidencias',
          data: this.dashboardData.tendencia_diaria.map(t => t.cantidad),
          borderColor: '#1976d2',
          backgroundColor: 'rgba(25, 118, 210, 0.1)',
          fill: true,
          tension: 0.3,
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      };
    }
  }

  capitalize(text: string): string {
    return text ? text.charAt(0).toUpperCase() + text.slice(1) : '';
  }

  formatDate(dateStr: string): string {
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
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
