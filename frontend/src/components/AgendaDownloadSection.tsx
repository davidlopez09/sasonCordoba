"use client";

import { resolveSiteUrl } from '@/lib/api';
export default function AgendaDownloadSection() {
  return (
    <section id="agenda" className="section agenda-section">
      <div className="container">
        <div className="agenda-banner" data-aos="fade-up">
          <div className="agenda-content">
            <span className="badge agenda-badge">PROGRAMACIÓN OFICIAL</span>
            <h2 className="agenda-title">
              Descarga la <span className="text-gradient">Agenda Completa</span>
            </h2>
            <p className="agenda-desc">
              Lleva contigo todos los horarios, presentaciones de chefs, talleres gastronómicos y actividades especiales de <strong>Sazón Córdoba 2026</strong> en un cómodo archivo PDF.
            </p>
            
            <div className="agenda-features">
              <div className="agenda-feature-item">
                <i className="ph ph-check-circle"></i>
                <span>Formato PDF ligero</span>
              </div>
              <div className="agenda-feature-item">
                <i className="ph ph-check-circle"></i>
                <span>Horarios y escenarios</span>
              </div>
              <div className="agenda-feature-item">
                <i className="ph ph-check-circle"></i>
                <span>Listo para imprimir o ver en tu celular</span>
              </div>
            </div>

            <div className="agenda-actions">
              <a 
                href={resolveSiteUrl("/agenda_sc_2026.pdf")} 
                download="Agenda_Sazon_Cordoba_2026.pdf" 
                className="btn btn-primary agenda-btn-download"
                id="btnDescargarAgenda"
              >
                <i className="ph ph-download-simple"></i>
                <span>Descargar Agenda (PDF)</span>
              </a>
              <a 
                href={resolveSiteUrl("/agenda_sc_2026.pdf")} 
                target="_blank" 
                rel="noopener noreferrer" 
                className="btn btn-outline agenda-btn-preview"
                id="btnVerAgenda"
              >
                <i className="ph ph-eye"></i>
                <span>Ver en Línea</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
