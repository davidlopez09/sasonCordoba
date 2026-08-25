"use client";

import { useState } from 'react';

const ITINERARY_TIPO_LABELS: any = {
  general: 'General',
  masterclass: 'Masterclass',
  degustacion: 'Degustación',
  concurso: 'Concurso'
};

const buildCalendarUrl = (item: any) => {
  const text = encodeURIComponent(`Sazón Córdoba: ${item.titulo}`);
  const details = encodeURIComponent(`Chef: ${item.nombre_chef}\n${item.descripcion}`);
  return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${text}&details=${details}`;
};

export default function ItinerarySection({ data, subtitle }: { data: any[], subtitle: any }) {
  const [activeTipo, setActiveTipo] = useState('todos');

  if (!data || data.length === 0) return null;

  const tiposPresentes = ['todos', ...Array.from(new Set(data.map((i: any) => i.tipo || 'general')))];
  
  const filtered = activeTipo === 'todos' 
    ? data 
    : data.filter((i: any) => (i.tipo || 'general') === activeTipo);

  return (
    <section id="itinerary" className="section section-dark">
      <div className="container">
        <div className="text-center">
          <h2 className="section-title" data-aos="fade-up" id="itinerary-title" dangerouslySetInnerHTML={{ __html: subtitle?.titulo?.replace(/\n/g, '<br>') || 'Itinerario y <span class="text-gradient">Menú</span>' }}></h2>
          <p className="section-subtitle" data-aos="fade-up" data-aos-delay="100" id="itinerary-subtitle">{subtitle?.subtitulo}</p>
        </div>
        
        {tiposPresentes.length > 2 && (
          <div className="itinerary-filters" id="itinerary-filters" data-aos="fade-up" data-aos-delay="100">
            {tiposPresentes.map(tipo => (
              <button 
                key={tipo}
                type="button" 
                className={`itinerary-filter-btn${activeTipo === tipo ? ' active' : ''}`}
                onClick={() => setActiveTipo(tipo as string)}
              >
                {tipo === 'todos' ? 'Todos' : (ITINERARY_TIPO_LABELS[tipo as string] || tipo)}
              </button>
            ))}
          </div>
        )}

        <div className="timeline" id="timeline">
          {filtered.map((item: any, i: number) => (
            <div key={item.id} className="timeline-item" data-aos="fade-up">
              <div className="timeline-time" style={{ color: item.color || '#1a1a1a' }}>
                <span className="time" style={{ color: item.color || '#1a1a1a' }}>{item.hora}</span>
                <span className="day" style={{ color: item.color || '#1a1a1a' }}>{item.dia}</span>
              </div>
              <div className="timeline-content" style={{ background: item.color_fondo || '#ffffff', borderColor: item.color_borde || 'rgba(0,0,0,0.08)' }}>
                <h3 className="timeline-title" style={{ color: item.color || '#1a1a1a' }}>{item.titulo}</h3>
                <p className="timeline-chef" style={{ color: item.color || '#1a1a1a' }}>Por: <strong>{item.nombre_chef}</strong></p>
                <p className="timeline-desc" style={{ color: item.color || '#1a1a1a' }}>{item.descripcion}</p>
                <a className="timeline-calendar-link" href={buildCalendarUrl(item)} target="_blank" rel="noopener noreferrer">
                  <i className="ph ph-calendar-plus"></i> Agregar a mi calendario
                </a>
              </div>
            </div>
          ))}
        </div>
        
      </div>
    </section>
  );
}
