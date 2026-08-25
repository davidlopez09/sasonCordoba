export default function ParticipaSection({ data }: { data: any, subtitle?: any }) {
  const s = data?.seccion;
  if (!s) return null;

  return (
    <section id="participa" className="section section-dark">
      <div className={`container participa-container ${!s.imagen ? 'no-image' : ''}`}>
        
        <div className="participa-content" data-aos="fade-right">
          <h2 className="section-title" id="participa-title" style={{ color: s.color || '' }} dangerouslySetInnerHTML={{ __html: s.titulo?.replace(/\n/g, '<br>') || '' }}></h2>
          <p className="section-desc" id="participa-desc" style={{ color: s.color || '' }}>{s.descripcion}</p>
          
          <div className="participa-actions" id="participa-actions">
            {data.botones?.map((b: any) => (
              <a key={b.id} href={b.enlace} className="btn btn-large" style={{ background: b.color_fondo, color: b.color_texto, borderColor: b.color_borde }}>
                {b.texto}
              </a>
            ))}
          </div>
        </div>
        
        <div className="participa-image-wrapper" id="participa-image-wrapper" style={{ display: s.imagen ? 'block' : 'none' }} data-aos="fade-left">
          {s.imagen && (
            <img src={s.imagen} alt="Haz parte de Sazón Córdoba" className="participa-img loaded" id="participa-img" />
          )}
        </div>
        
      </div>
    </section>
  );
}
