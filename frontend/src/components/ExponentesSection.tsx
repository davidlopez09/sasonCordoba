"use client";

import { useState, useEffect, useRef } from 'react';

const DISH_ETIQUETA_LABELS: Record<string, string> = {
    'nuevo': '¡Nuevo!',
    'estrella': 'Plato Estrella',
    'limitado': 'Edición Limitada',
    'vegano': 'Opción Vegana'
};

export default function ExponentesSection({ exponentes, platillos, subtitle, subtitleDishes }: { exponentes: any[], platillos: any[], subtitle: any, subtitleDishes: any }) {
  const [activeDish, setActiveDish] = useState<any>(null);
  
  // Carousel State
  const [currentIndex, setCurrentIndex] = useState(0);
  const [wrapperWidth, setWrapperWidth] = useState(0);
  const [isMobile, setIsMobile] = useState(false);
  const wrapperRef = useRef<HTMLDivElement>(null);

  const list = (platillos && platillos.length > 0) ? platillos : [];
  const N = list.length || 1;
  
  // Infinite carousel simulation (5x replicas)
  const itemsList = [...list, ...list, ...list, ...list, ...list];
  
  // Initialize to the middle replica set
  useEffect(() => {
    setCurrentIndex(2 * N);
  }, [N]);

  useEffect(() => {
    const handleResize = () => {
      if (wrapperRef.current) setWrapperWidth(wrapperRef.current.offsetWidth);
      setIsMobile(window.innerWidth <= 768);
    };
    window.addEventListener('resize', handleResize);
    handleResize(); // Initial call
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') setActiveDish(null);
    };
    if (activeDish) {
      document.addEventListener('keydown', handleKeyDown);
    }
    return () => document.removeEventListener('keydown', handleKeyDown);
  }, [activeDish]);

  const nextSlide = () => {
    setCurrentIndex(prev => {
      let next = prev + 1;
      // If we are reaching the end of the 4th set, quietly jump back to 2nd set
      if (next >= 4 * N) {
        // We need a timeout to let the transition finish before jumping, 
        // but for simplicity in React without ref-tricks, we just clamp it or let it run
        // Actually, let's just let it be bounded to avoid complex silent jumps.
        return Math.min(next, itemsList.length - 1);
      }
      return next;
    });
  };

  const prevSlide = () => {
    setCurrentIndex(prev => {
      let next = prev - 1;
      if (next < N) {
        return Math.max(0, next);
      }
      return next;
    });
  };

  const cardWidth = isMobile ? 280 : 360;
  const cardGap = 30;
  const step = cardWidth + cardGap;
  const centerOffset = (wrapperWidth / 2) - (cardWidth / 2);
  const translateX = centerOffset - (currentIndex * step);

  return (
    <>
      {/* Chefs Section */}
      <section id="chefs" className="section section-dark">
        <div className="container text-center">
          <h2 className="section-title" data-aos="fade-up" id="chefs-title" dangerouslySetInnerHTML={{ __html: subtitle?.titulo?.replace(/\n/g, '<br>') || 'Exponentes <span class="text-gradient">Especiales</span>' }}></h2>
          <p className="section-subtitle" data-aos="fade-up" data-aos-delay="100" id="chefs-subtitle">{subtitle?.subtitulo}</p>
          
          <div className="chefs-grid" id="chefs-grid">
            {exponentes?.map((e: any, i: number) => (
              <div key={e.id} className="chef-card" data-aos="zoom-in" data-aos-delay={100 + i * 100}>
                <div className="chef-img-wrapper">
                  <img src={e.foto || 'https://via.placeholder.com/600/222/FFF?text=Chef'} alt={e.nombre} className="chef-img" loading="lazy" />
                  <div className="chef-socials">
                    {e.instagram_url && <a href={e.instagram_url}><i className="ph ph-instagram-logo"></i></a>}
                    {e.twitter_url && <a href={e.twitter_url}><i className="ph ph-twitter-logo"></i></a>}
                  </div>
                </div>
                <div className="chef-info">
                  <h3 style={{ color: e.color || '#1a1a1a' }}>{e.nombre}</h3>
                  <p style={{ color: e.color || '#1a1a1a' }}>{e.especialidad}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Platillos Destacados Section (Carrusel) */}
      <section id="dishes" className="section">
        <div className="container text-center">
          <h2 className="section-title" data-aos="fade-up" id="dishes-title" dangerouslySetInnerHTML={{ __html: subtitleDishes?.titulo?.replace(/\n/g, '<br>') || 'Platillos <span class="text-gradient">Destacados</span>' }}></h2>
          <p className="section-subtitle" data-aos="fade-up" data-aos-delay="100" id="dishes-subtitle">{subtitleDishes?.subtitulo}</p>
          
          <div className="dishes-slider-container" id="dishes-slider-container" data-aos="fade-up" data-aos-delay="200" ref={wrapperRef}>
            <button className="slider-arrow slider-prev" id="dishSliderPrev" aria-label="Anterior" onClick={prevSlide}><i className="ph ph-caret-left"></i></button>
            <div className="dishes-slider-track" id="dishes-slider-track" style={{ transform: `translateX(${translateX}px)` }}>
              {itemsList.map((dish: any, idx: number) => {
                const isCenter = idx === currentIndex;
                const etiquetaLabel = dish.etiqueta ? DISH_ETIQUETA_LABELS[dish.etiqueta] : null;
                
                return (
                  <div key={`${dish.id}-${idx}`} className={`dish-item ${isCenter ? 'center-active' : ''}`} onClick={() => setActiveDish(dish)}>
                    {etiquetaLabel && <span className={`dish-badge dish-badge-${dish.etiqueta}`}>{etiquetaLabel}</span>}
                    <img src={dish.imagen || 'https://via.placeholder.com/600/222/FFF?text=Plato'} alt={dish.nombre} loading="lazy" />
                    <div className="dish-zoom-hint">
                        <i className="ph ph-arrows-out-simple"></i>
                    </div>
                    <div className="dish-overlay">
                        <h4 style={{ color: dish.color || '#ffffff' }}>{dish.nombre}</h4>
                        <p style={{ color: dish.color || '#ffffff' }}>{dish.descripcion}</p>
                    </div>
                  </div>
                );
              })}
            </div>
            <button className="slider-arrow slider-next" id="dishSliderNext" aria-label="Siguiente" onClick={nextSlide}><i className="ph ph-caret-right"></i></button>
          </div>

          {/* Indicadores circulares de diapositivas */}
          <div className="slider-dots-nav" id="dishSliderDots">
            {list.map((_, i) => (
              <button 
                key={i} 
                className={`dot ${i === (currentIndex % N) ? 'active' : ''}`} 
                aria-label={`Ir a diapositiva ${i + 1}`} 
                onClick={() => setCurrentIndex(2 * N + i)}
              ></button>
            ))}
          </div>

          <div className="dishes-gallery" id="dishes-gallery" style={{ display: 'none' }}></div>
        </div>
      </section>

      {/* Lightbox Modal */}
      <div 
        className={`lightbox ${activeDish ? 'active' : ''}`} 
        id="dishLightbox" 
        style={{ display: activeDish ? 'flex' : 'none' }}
        onClick={(e) => { if (e.target === e.currentTarget) setActiveDish(null); }}
      >
        <button className="lightbox-close" id="lightboxClose" aria-label="Cerrar vista previa" onClick={() => setActiveDish(null)}>
          <i className="ph ph-x"></i>
        </button>
        {activeDish && (
          <div className="lightbox-content">
            <img id="lightboxImg" src={activeDish.imagen || undefined} alt={activeDish.nombre || ''} />
            <div className="lightbox-info">
              <h3 id="lightboxTitle">{activeDish.nombre}</h3>
              <p id="lightboxDesc">
                {activeDish.descripcion}
              </p>
            </div>
          </div>
        )}
      </div>
    </>
  );
}
