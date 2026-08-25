export default function AboutSection({ data, subtitle, config }: { data: any, subtitle: any, config?: any }) {
  if (!data?.seccion) return null;

  return (
    <section id="about" className="section">
      <div className="container about-container">
        
        <div className="about-image-wrapper" data-aos="fade-right">
          {data.seccion.imagen && (
            <img src={data.seccion.imagen} alt="Acerca de Sazón Córdoba" id="about-img" className="about-img about-img-main loaded" />
          )}
          <div className="about-img-badge">
              <i className="ph ph-star-fill"></i>
              <span>Experiencia Premium</span>
          </div>
        </div>

        <div className="about-content" data-aos="fade-left">
          <h2 className="section-title" id="about-title" style={{ color: data.seccion.color || '' }} dangerouslySetInnerHTML={{ __html: data.seccion.titulo?.replace(/\n/g, '<br>') || '' }}></h2>
          <p className="section-desc" id="about-desc" style={{ color: data.seccion.color || '' }}>{data.seccion.descripcion}</p>
          
          <div className="features" id="about-features">
            {data.caracteristicas?.map((c: any) => (
              <div key={c.id} className="feature">
                <div className="feature-icon"><i className={c.icono}></i></div>
                <div>
                  <h4 style={{ color: c.color || '#1a1a1a' }}>{c.titulo}</h4>
                  <p style={{ color: c.color || '#1a1a1a' }}>{c.descripcion}</p>
                </div>
              </div>
            ))}
          </div>

          <div className="about-video-wrapper" id="about-video-wrapper" style={{ display: config?.about_video_url ? 'block' : 'none' }}>
              {config?.about_video_url && <iframe id="about-video-frame" src={config.about_video_url} title="Video introductorio Sazón Córdoba" allowFullScreen loading="lazy"></iframe>}
          </div>
        </div>

      </div>
    </section>
  );
}
