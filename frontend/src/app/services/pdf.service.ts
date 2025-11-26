import { Injectable } from '@angular/core';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';

export interface ReporteConfig {
  titulo: string;
  subtitulo?: string;
  periodo?: string;
  fechaGeneracion?: string;
}

@Injectable({
  providedIn: 'root'
})
export class PdfService {
  // Colores institucionales
  private readonly colorPrimario = [102, 126, 234]; // #667eea
  private readonly colorSecundario = [118, 75, 162]; // #764ba2
  private readonly colorTexto = [51, 51, 51];
  private readonly colorGris = [128, 128, 128];

  constructor() {}

  /**
   * Crear PDF base con encabezado institucional
   */
  private createBasePdf(config: ReporteConfig): jsPDF {
    const doc = new jsPDF('p', 'mm', 'letter');
    const pageWidth = doc.internal.pageSize.getWidth();
    const fechaGen = config.fechaGeneracion || new Date().toLocaleDateString('es-ES', {
      year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });

    // Header con gradiente simulado
    doc.setFillColor(this.colorPrimario[0], this.colorPrimario[1], this.colorPrimario[2]);
    doc.rect(0, 0, pageWidth, 35, 'F');
    
    // Logo/Nombre institucional
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(22);
    doc.setFont('helvetica', 'bold');
    doc.text('Sistema de Gestión Académica', pageWidth / 2, 15, { align: 'center' });
    
    // Título del reporte
    doc.setFontSize(14);
    doc.setFont('helvetica', 'normal');
    doc.text(config.titulo, pageWidth / 2, 25, { align: 'center' });
    
    // Subtítulo si existe
    if (config.subtitulo) {
      doc.setFontSize(10);
      doc.text(config.subtitulo, pageWidth / 2, 31, { align: 'center' });
    }

    // Línea decorativa
    doc.setDrawColor(this.colorSecundario[0], this.colorSecundario[1], this.colorSecundario[2]);
    doc.setLineWidth(1);
    doc.line(15, 40, pageWidth - 15, 40);

    // Información del reporte
    doc.setTextColor(this.colorGris[0], this.colorGris[1], this.colorGris[2]);
    doc.setFontSize(9);
    doc.text(`Generado: ${fechaGen}`, 15, 47);
    if (config.periodo) {
      doc.text(`Período: ${config.periodo}`, pageWidth - 15, 47, { align: 'right' });
    }

    return doc;
  }

  /**
   * Agregar pie de página
   */
  private addFooter(doc: jsPDF, pageNumber: number = 1): void {
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    
    doc.setDrawColor(200, 200, 200);
    doc.line(15, pageHeight - 15, pageWidth - 15, pageHeight - 15);
    
    doc.setTextColor(this.colorGris[0], this.colorGris[1], this.colorGris[2]);
    doc.setFontSize(8);
    doc.text('Sistema de Gestión Académica - Universidad', 15, pageHeight - 10);
    doc.text(`Página ${pageNumber}`, pageWidth - 15, pageHeight - 10, { align: 'right' });
  }

  /**
   * Exportar lista de docentes a PDF
   */
  exportarDocentes(docentes: any[], config?: Partial<ReporteConfig>): void {
    const doc = this.createBasePdf({
      titulo: 'Reporte de Docentes',
      subtitulo: `Total: ${docentes.length} docentes`,
      ...config
    });

    // Preparar datos para la tabla
    const tableData = docentes.map(d => [
      d.nombre || '',
      d.email || '',
      d.grados || '',
      d.idioma || '',
      d.sni ? 'Sí' : 'No',
      d.estatus || ''
    ]);

    // Crear tabla
    autoTable(doc, {
      startY: 55,
      head: [['Nombre', 'Email', 'Grados', 'Idioma', 'SNI', 'Estatus']],
      body: tableData,
      headStyles: {
        fillColor: [this.colorPrimario[0], this.colorPrimario[1], this.colorPrimario[2]],
        textColor: [255, 255, 255],
        fontStyle: 'bold',
        halign: 'center'
      },
      alternateRowStyles: {
        fillColor: [245, 245, 245]
      },
      styles: {
        fontSize: 9,
        cellPadding: 3
      },
      columnStyles: {
        0: { cellWidth: 45 },
        1: { cellWidth: 50 },
        4: { halign: 'center', cellWidth: 15 },
        5: { halign: 'center', cellWidth: 20 }
      },
      didDrawPage: (data) => {
        this.addFooter(doc, data.pageNumber);
      }
    });

    doc.save('reporte_docentes.pdf');
  }

  /**
   * Exportar lista de incidencias a PDF
   */
  exportarIncidencias(incidencias: any[], config?: Partial<ReporteConfig>): void {
    const doc = this.createBasePdf({
      titulo: 'Reporte de Incidencias',
      subtitulo: `Total: ${incidencias.length} incidencias`,
      ...config
    });

    const tableData = incidencias.map(i => [
      i.id?.toString() || '',
      i.tipo_nombre || i.tipo || '',
      i.profesor_nombre || '',
      i.curso || '',
      i.prioridad || '',
      i.status || '',
      i.fecha_creacion ? new Date(i.fecha_creacion).toLocaleDateString('es-ES') : ''
    ]);

    autoTable(doc, {
      startY: 55,
      head: [['ID', 'Tipo', 'Profesor', 'Curso', 'Prioridad', 'Estado', 'Fecha']],
      body: tableData,
      headStyles: {
        fillColor: [this.colorPrimario[0], this.colorPrimario[1], this.colorPrimario[2]],
        textColor: [255, 255, 255],
        fontStyle: 'bold',
        halign: 'center'
      },
      alternateRowStyles: {
        fillColor: [245, 245, 245]
      },
      styles: {
        fontSize: 8,
        cellPadding: 2
      },
      columnStyles: {
        0: { cellWidth: 12, halign: 'center' },
        4: { halign: 'center', cellWidth: 20 },
        5: { halign: 'center', cellWidth: 22 },
        6: { halign: 'center', cellWidth: 22 }
      },
      didDrawPage: (data) => {
        this.addFooter(doc, data.pageNumber);
      }
    });

    doc.save('reporte_incidencias.pdf');
  }

  /**
   * Exportar lista de cursos a PDF
   */
  exportarCursos(cursos: any[], config?: Partial<ReporteConfig>): void {
    const doc = this.createBasePdf({
      titulo: 'Catálogo de Cursos',
      subtitulo: `Total: ${cursos.length} cursos`,
      ...config
    });

    const tableData = cursos.map(c => [
      c.codigo || '',
      c.nombre || '',
      c.creditos?.toString() || '0',
      c.semestre?.toString() || '-',
      c.modalidad || '',
      c.academia_nombre || 'N/A',
      c.estatus || ''
    ]);

    autoTable(doc, {
      startY: 55,
      head: [['Código', 'Nombre', 'Créditos', 'Sem.', 'Modalidad', 'Academia', 'Estatus']],
      body: tableData,
      headStyles: {
        fillColor: [this.colorPrimario[0], this.colorPrimario[1], this.colorPrimario[2]],
        textColor: [255, 255, 255],
        fontStyle: 'bold',
        halign: 'center'
      },
      alternateRowStyles: {
        fillColor: [245, 245, 245]
      },
      styles: {
        fontSize: 9,
        cellPadding: 3
      },
      columnStyles: {
        0: { cellWidth: 20 },
        2: { halign: 'center', cellWidth: 18 },
        3: { halign: 'center', cellWidth: 15 },
        4: { halign: 'center', cellWidth: 22 },
        6: { halign: 'center', cellWidth: 18 }
      },
      didDrawPage: (data) => {
        this.addFooter(doc, data.pageNumber);
      }
    });

    doc.save('catalogo_cursos.pdf');
  }

  /**
   * Exportar evaluaciones de un docente a PDF
   */
  exportarEvaluacionDocente(docente: any, resumen: any, evaluaciones: any[], config?: Partial<ReporteConfig>): void {
    const doc = this.createBasePdf({
      titulo: 'Reporte de Evaluación Docente',
      subtitulo: docente.nombre,
      ...config
    });

    const pageWidth = doc.internal.pageSize.getWidth();
    let yPos = 55;

    // Información del docente
    doc.setFontSize(11);
    doc.setTextColor(this.colorTexto[0], this.colorTexto[1], this.colorTexto[2]);
    doc.setFont('helvetica', 'bold');
    doc.text('Información del Docente', 15, yPos);
    yPos += 8;

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.text(`Email: ${docente.email || 'N/A'}`, 15, yPos);
    doc.text(`Grados: ${docente.grados || 'N/A'}`, 110, yPos);
    yPos += 6;
    doc.text(`Estatus: ${docente.estatus || 'N/A'}`, 15, yPos);
    doc.text(`SNI: ${docente.sni ? 'Sí' : 'No'}`, 110, yPos);
    yPos += 12;

    // Resumen de evaluaciones
    if (resumen && resumen.total_evaluaciones > 0) {
      // Cuadro de calificación global
      doc.setFillColor(this.colorPrimario[0], this.colorPrimario[1], this.colorPrimario[2]);
      doc.roundedRect(15, yPos, 50, 30, 3, 3, 'F');
      
      doc.setTextColor(255, 255, 255);
      doc.setFontSize(24);
      doc.setFont('helvetica', 'bold');
      doc.text(resumen.promedio_global?.toFixed(1) || 'N/A', 40, yPos + 15, { align: 'center' });
      doc.setFontSize(8);
      doc.text('Promedio Global', 40, yPos + 24, { align: 'center' });

      // Estadísticas
      doc.setTextColor(this.colorTexto[0], this.colorTexto[1], this.colorTexto[2]);
      doc.setFontSize(10);
      doc.setFont('helvetica', 'normal');
      doc.text(`Total de evaluaciones: ${resumen.total_evaluaciones}`, 75, yPos + 8);
      doc.text(`Evaluaciones de alumnos: ${resumen.eval_alumnos || 0}`, 75, yPos + 15);
      doc.text(`Evaluaciones de coordinadores: ${resumen.eval_coordinadores || 0}`, 75, yPos + 22);
      doc.text(`Evaluaciones de pares: ${resumen.eval_pares || 0}`, 75, yPos + 29);
      
      yPos += 40;

      // Promedios por criterio
      if (resumen.promedios_criterios && resumen.promedios_criterios.length > 0) {
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(11);
        doc.text('Calificaciones por Criterio', 15, yPos);
        yPos += 8;

        const criteriosData = resumen.promedios_criterios.map((c: any) => [
          c.criterio || '',
          c.categoria || '',
          c.promedio?.toFixed(1) || '0.0',
          c.total_respuestas?.toString() || '0'
        ]);

        autoTable(doc, {
          startY: yPos,
          head: [['Criterio', 'Categoría', 'Promedio', 'Respuestas']],
          body: criteriosData,
          headStyles: {
            fillColor: [this.colorSecundario[0], this.colorSecundario[1], this.colorSecundario[2]],
            textColor: [255, 255, 255],
            fontStyle: 'bold'
          },
          styles: {
            fontSize: 9
          },
          columnStyles: {
            2: { halign: 'center' },
            3: { halign: 'center' }
          }
        });
      }
    } else {
      doc.setFontSize(11);
      doc.setTextColor(this.colorGris[0], this.colorGris[1], this.colorGris[2]);
      doc.text('No hay evaluaciones registradas para este docente.', 15, yPos);
    }

    this.addFooter(doc);
    doc.save(`evaluacion_${docente.nombre?.replace(/\s+/g, '_') || 'docente'}.pdf`);
  }

  /**
   * Exportar dashboard/estadísticas a PDF
   */
  exportarDashboard(dashboard: any, config?: Partial<ReporteConfig>): void {
    const doc = this.createBasePdf({
      titulo: 'Resumen Ejecutivo',
      subtitulo: 'Dashboard del Sistema',
      ...config
    });

    const pageWidth = doc.internal.pageSize.getWidth();
    let yPos = 55;

    // Estadísticas principales
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.setTextColor(this.colorTexto[0], this.colorTexto[1], this.colorTexto[2]);
    doc.text('Estadísticas Generales', 15, yPos);
    yPos += 10;

    // Cajas de estadísticas
    const stats = dashboard.dashboard || {};
    const boxes = [
      { label: 'Total Docentes', value: stats.total_docentes || 0 },
      { label: 'Docentes Activos', value: stats.docentes_activos || 0 },
      { label: 'Docentes SNI', value: stats.docentes_sni || 0 },
      { label: 'Total Incidencias', value: stats.total_incidencias || 0 },
      { label: 'Inc. Abiertas', value: stats.incidencias_abiertas || 0 }
    ];

    const boxWidth = 35;
    const boxHeight = 25;
    let xPos = 15;

    boxes.forEach((box, index) => {
      doc.setFillColor(240, 240, 240);
      doc.roundedRect(xPos, yPos, boxWidth, boxHeight, 2, 2, 'F');
      
      doc.setFontSize(16);
      doc.setFont('helvetica', 'bold');
      doc.setTextColor(this.colorPrimario[0], this.colorPrimario[1], this.colorPrimario[2]);
      doc.text(box.value.toString(), xPos + boxWidth/2, yPos + 12, { align: 'center' });
      
      doc.setFontSize(7);
      doc.setTextColor(this.colorGris[0], this.colorGris[1], this.colorGris[2]);
      doc.text(box.label, xPos + boxWidth/2, yPos + 20, { align: 'center' });
      
      xPos += boxWidth + 5;
    });

    yPos += 35;

    // Incidencias por estado
    if (dashboard.incidencias_por_estado && dashboard.incidencias_por_estado.length > 0) {
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(11);
      doc.setTextColor(this.colorTexto[0], this.colorTexto[1], this.colorTexto[2]);
      doc.text('Incidencias por Estado', 15, yPos);
      yPos += 8;

      const estadoData = dashboard.incidencias_por_estado.map((e: any) => [
        e.status || '',
        e.cantidad?.toString() || '0'
      ]);

      autoTable(doc, {
        startY: yPos,
        head: [['Estado', 'Cantidad']],
        body: estadoData,
        headStyles: {
          fillColor: [this.colorPrimario[0], this.colorPrimario[1], this.colorPrimario[2]],
          textColor: [255, 255, 255]
        },
        styles: { fontSize: 10 },
        columnStyles: {
          0: { cellWidth: 60 },
          1: { halign: 'center', cellWidth: 30 }
        },
        tableWidth: 100
      });

      yPos = (doc as any).lastAutoTable.finalY + 15;
    }

    // Incidencias por prioridad
    if (dashboard.incidencias_por_prioridad && dashboard.incidencias_por_prioridad.length > 0) {
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(11);
      doc.setTextColor(this.colorTexto[0], this.colorTexto[1], this.colorTexto[2]);
      doc.text('Incidencias por Prioridad', 120, (doc as any).lastAutoTable?.startY || 100);

      const prioridadData = dashboard.incidencias_por_prioridad.map((p: any) => [
        p.prioridad || '',
        p.cantidad?.toString() || '0'
      ]);

      autoTable(doc, {
        startY: (doc as any).lastAutoTable?.startY + 8 || 108,
        head: [['Prioridad', 'Cantidad']],
        body: prioridadData,
        headStyles: {
          fillColor: [this.colorSecundario[0], this.colorSecundario[1], this.colorSecundario[2]],
          textColor: [255, 255, 255]
        },
        styles: { fontSize: 10 },
        columnStyles: {
          0: { cellWidth: 40 },
          1: { halign: 'center', cellWidth: 25 }
        },
        tableWidth: 75,
        margin: { left: 120 }
      });
    }

    this.addFooter(doc);
    doc.save('dashboard_resumen.pdf');
  }

  /**
   * Exportar estadísticas de evaluaciones a PDF
   */
  exportarEstadisticasEvaluaciones(stats: any, config?: Partial<ReporteConfig>): void {
    const doc = this.createBasePdf({
      titulo: 'Estadísticas de Evaluaciones',
      subtitulo: `Total: ${stats.total_evaluaciones || 0} evaluaciones`,
      ...config
    });

    let yPos = 55;

    // Promedio general
    doc.setFillColor(this.colorPrimario[0], this.colorPrimario[1], this.colorPrimario[2]);
    doc.roundedRect(15, yPos, 60, 35, 3, 3, 'F');
    
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(28);
    doc.setFont('helvetica', 'bold');
    doc.text(stats.promedio_general?.toFixed(1) || 'N/A', 45, yPos + 18, { align: 'center' });
    doc.setFontSize(10);
    doc.text('Promedio General', 45, yPos + 28, { align: 'center' });

    // Total evaluaciones
    doc.setTextColor(this.colorTexto[0], this.colorTexto[1], this.colorTexto[2]);
    doc.setFontSize(12);
    doc.text(`Total de evaluaciones: ${stats.total_evaluaciones || 0}`, 85, yPos + 15);
    
    yPos += 45;

    // Top docentes
    if (stats.top_docentes && stats.top_docentes.length > 0) {
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(11);
      doc.text('Top Docentes Mejor Evaluados', 15, yPos);
      yPos += 8;

      const topData = stats.top_docentes.map((t: any) => [
        t.docente || '',
        t.promedio?.toFixed(1) || '0.0',
        t.total_evaluaciones?.toString() || '0'
      ]);

      autoTable(doc, {
        startY: yPos,
        head: [['Docente', 'Promedio', 'Evaluaciones']],
        body: topData,
        headStyles: {
          fillColor: [this.colorPrimario[0], this.colorPrimario[1], this.colorPrimario[2]],
          textColor: [255, 255, 255]
        },
        styles: { fontSize: 10 },
        columnStyles: {
          1: { halign: 'center' },
          2: { halign: 'center' }
        }
      });

      yPos = (doc as any).lastAutoTable.finalY + 15;
    }

    // Distribución por calificación
    if (stats.distribucion_calificaciones && stats.distribucion_calificaciones.length > 0) {
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(11);
      doc.setTextColor(this.colorTexto[0], this.colorTexto[1], this.colorTexto[2]);
      doc.text('Distribución de Calificaciones', 15, yPos);
      yPos += 8;

      const distData = stats.distribucion_calificaciones.map((d: any) => [
        d.rango || '',
        d.cantidad?.toString() || '0'
      ]);

      autoTable(doc, {
        startY: yPos,
        head: [['Rango', 'Cantidad']],
        body: distData,
        headStyles: {
          fillColor: [this.colorSecundario[0], this.colorSecundario[1], this.colorSecundario[2]],
          textColor: [255, 255, 255]
        },
        styles: { fontSize: 10 },
        columnStyles: {
          1: { halign: 'center' }
        }
      });
    }

    this.addFooter(doc);
    doc.save('estadisticas_evaluaciones.pdf');
  }
}
