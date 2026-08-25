"use client";

import { useState } from 'react';

export default function GaleriaSection({ data, subtitle, config }: { data: any[], subtitle: any, config: any }) {
  const [activeImage, setActiveImage] = useState<any>(null);

  if (!data || data.length === 0) return null;

  return (
    <>
      <section id="galeria" className="section">
        <div className="container">
          <div className="text-center">
            <h2 className="section-title" data-aos="fade-up" id="galeria-title" dangerouslySetInnerHTML={{ __html: subtitle?.titulo?.replace(/\n/g, '<br>') || 'Galería <span class="text-gradient">Fotográfica</span>' }}></h2>
            <p className="section-subtitle" data-aos="fade-up" data-aos-delay="100" id="galeria-subtitle">{subtitle?.subtitulo}</p>
          </div>
          
          <div className="galeria-grid" id="galeria-grid" data-aos="fade-up" data-aos-delay="150">
            {data.map((item: any, i: number) => (
              <div 
                key={item.id} 
                className={`galeria-item galeria-item-${item.tipo}`} 
                onClick={() => {
                  if (item.tipo === 'video') {
                    window.open(item.url, '_blank', 'noopener,noreferrer');
                  } else {
                    setActiveImage(item);
                  }
                }}
              >
                <img src={item.url} alt={item.titulo || 'Galería Sazón Córdoba'} loading="lazy" />
                {item.tipo === 'video' && <div className="galeria-play"><i className="ph ph-play-circle"></i></div>}
                {item.titulo && (
                  <div className="galeria-overlay">
                    <span>{item.titulo}</span>
                    {item.edicion && <small>{item.edicion}</small>}
                  </div>
                )}
              </div>
            ))}
          </div>

          <div className="galeria-socials" id="galeria-socials">
            <p>Síguenos para más contenido:</p>
            <div className="social-links">
              {config?.social_instagram_url && <a href={config.social_instagram_url} target="_blank" rel="noopener noreferrer"><i className="ph ph-instagram-logo"></i></a>}
              {config?.social_facebook_url && <a href={config.social_facebook_url} target="_blank" rel="noopener noreferrer"><i className="ph ph-facebook-logo"></i></a>}
              {config?.social_twitter_url && <a href={config.social_twitter_url} target="_blank" rel="noopener noreferrer"><i className="ph ph-twitter-logo"></i></a>}
            </div>
          </div>
        </div>
      </section>

      {/* Lightbox compartido o propio */}
      <div 
        className={`lightbox ${activeImage ? 'active' : ''}`} 
        style={{ display: activeImage ? 'flex' : 'none' }}
        onClick={(e) => { if (e.target === e.currentTarget) setActiveImage(null); }}
      >
        <button className="lightbox-close" onClick={() => setActiveImage(null)}>
          <i className="ph ph-x"></i>
        </button>
        <div className="lightbox-content">
          {activeImage && (
            <div className="dish-detail" style={{ maxWidth: '800px', padding: '20px', textAlign: 'center', background: 'transparent' }}>
                <img src={activeImage.url} alt={activeImage.titulo} style={{ maxHeight: '80vh', objectFit: 'contain' }} />
                {activeImage.titulo && <h2 style={{ color: '#fff', marginTop: '1rem' }}>{activeImage.titulo}</h2>}
                {activeImage.edicion && <p style={{ color: '#aaa' }}>{activeImage.edicion}</p>}
            </div>
          )}
        </div>
      </div>
    </>
  );
}
