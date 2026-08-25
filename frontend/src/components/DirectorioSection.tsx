"use client";

import { useState } from 'react';

export default function DirectorioSection({ data, subtitle }: { data: any[], subtitle: any }) {
  const [searchTerm, setSearchTerm] = useState('');
  const [activeCat, setActiveCat] = useState('todas');

  if (!data || data.length === 0) return null;

  const categorias = ['todas', ...Array.from(new Set(data.map(i => i.categoria)))];

  const q = searchTerm.trim().toLowerCase();
  const filteredData = data.filter((item: any) => {
    const matchesCategoria = activeCat === 'todas' || item.categoria === activeCat;
    const matchesQuery = !q || item.nombre?.toLowerCase().includes(q) || (item.descripcion || '').toLowerCase().includes(q);
    return matchesCategoria && matchesQuery;
  });

  return (
    <section id="directorio" className="section">
      <div className="container">
        <div className="text-center">
          <h2 className="section-title" data-aos="fade-up" id="directorio-title" dangerouslySetInnerHTML={{ __html: subtitle?.titulo?.replace(/\n/g, '<br>') || 'Directorio de <span class="text-gradient">Expositores</span>' }}></h2>
          <p className="section-subtitle" data-aos="fade-up" data-aos-delay="100" id="directorio-subtitle">{subtitle?.subtitulo}</p>
        </div>

        <div className="directorio-controls" data-aos="fade-up" data-aos-delay="150">
          <div className="directorio-search">
            <i className="ph ph-magnifying-glass"></i>
            <input 
              type="text" 
              id="directorio-search-input" 
              placeholder="Buscar por nombre o producto..." 
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
          <div className="directorio-filters" id="directorio-filters">
            {categorias.map(cat => (
              <button 
                key={cat}
                type="button" 
                className={`directorio-filter-btn ${cat === activeCat ? 'active' : ''}`}
                onClick={() => setActiveCat(cat as string)}
              >
                {cat === 'todas' ? 'Todas' : cat}
              </button>
            ))}
          </div>
        </div>

        <div className="directorio-grid" id="directorio-grid">
          {filteredData.map((item: any) => (
            <div key={item.id} className="directorio-card">
              {item.logo ? (
                <img src={item.logo} alt={item.nombre} className="directorio-logo" />
              ) : (
                <div className="directorio-logo directorio-logo-placeholder">
                  <i className="ph ph-storefront"></i>
                </div>
              )}
              <div className="directorio-info">
                <span className="directorio-categoria">{item.categoria}</span>
                <h3 style={{ color: item.color || '#1a1a1a' }}>{item.nombre}</h3>
                <p style={{ color: item.color || '#1a1a1a' }}>{item.descripcion || ''}</p>
                {item.contacto && (
                  <span className="directorio-contacto">
                    <i className="ph ph-envelope-simple"></i> {item.contacto}
                  </span>
                )}
              </div>
            </div>
          ))}
        </div>
        
        {filteredData.length === 0 && (
          <p className="directorio-empty" id="directorio-empty">No se encontraron expositores con ese criterio.</p>
        )}
      </div>
    </section>
  );
}
