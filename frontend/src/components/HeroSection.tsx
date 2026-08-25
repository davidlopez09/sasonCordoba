"use client";

import { useEffect, useState } from 'react';

export default function HeroSection({ hero }: { hero: any }) {
  const [currentSlide, setCurrentSlide] = useState(0);

  useEffect(() => {
    if (!hero?.slides || hero.slides.length <= 1) return;
    
    const interval = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % hero.slides.length);
    }, 5000);
    
    return () => clearInterval(interval);
  }, [hero]);

  if (!hero) return null;

  return (
    <header className="hero" id="home">
      <div className="hero-slider" id="hero-slider">
        {hero.slides?.map((s: any, i: number) => (
          <div 
            key={s.id} 
            className={`hero-slide ${i === currentSlide ? 'active' : ''}`}
            style={{ backgroundImage: `url('${s.imagen}')` }}
          ></div>
        ))}
      </div>
      
      <div className="hero-overlay"></div>
      <div className="hero-glow hero-glow-1"></div>
      <div className="hero-glow hero-glow-2"></div>
      
      <div className="hero-content" data-aos="fade-up" data-aos-duration="1200">
        {hero.texto && (
          <>
            <div className="badge hero-text loaded" style={{ color: hero.texto.color || '' }} id="hero-badge">{hero.texto.texto_badge}</div>
            <h1 className="hero-title hero-text loaded" style={{ color: hero.texto.color || '' }} id="hero-title" dangerouslySetInnerHTML={{ __html: hero.texto.titulo }}></h1>
            <p className="hero-subtitle hero-text loaded" style={{ color: hero.texto.color || '' }} id="hero-subtitle">{hero.texto.subtitulo}</p>
          </>
        )}
        
        <div className="hero-actions" id="hero-actions">
          {hero.botones?.map((b: any) => (
            <a key={b.id} href={b.enlace} className="btn btn-large" style={{ background: b.color_fondo, color: b.color_texto, border: `1px solid ${b.color_borde || 'transparent'}`, borderRadius: '30px' }}>
              {b.icono && <i className={b.icono}></i>} {b.texto}
            </a>
          ))}
        </div>
        
        <div className="hero-stats" id="hero-stats">
          {hero.estadisticas?.map((s: any) => (
            <div key={s.id} className="stat-item">
              <h3 style={{ color: s.color || '#fff' }}>{s.numero}</h3>
              <p style={{ color: s.color || '#fff' }}>{s.etiqueta}</p>
            </div>
          ))}
        </div>
      </div>

      <a href="#identity" className="scroll-down-hint" aria-label="Desplazarse hacia abajo">
          <i className="ph ph-caret-double-down"></i>
          <span>Explorar</span>
      </a>
    </header>
  );
}
