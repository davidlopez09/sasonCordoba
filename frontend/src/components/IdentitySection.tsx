export default function IdentitySection({ data, subtitle }: { data: any, subtitle: any }) {
  if (!data?.seccion) return null;
  const s = data.seccion;
  
  return (
    <section id="identity" className="section section-dark">
      <div className="container text-center">
        <h2 
          className="section-title" 
          data-aos="fade-up" 
          id="identity-title" 
          style={{ color: s.color || '' }} 
          dangerouslySetInnerHTML={{ __html: s.titulo?.replace(/\n/g, '<br>') || '' }}
        ></h2>
        <div 
          className="section-desc" 
          data-aos="fade-up" 
          data-aos-delay="100" 
          id="identity-desc"
          style={{ maxWidth: '800px', margin: '0 auto 40px auto', fontSize: '1.2rem', color: s.color || '' }} 
          dangerouslySetInnerHTML={{ __html: s.descripcion?.replace(/\n/g, '<br>') || '' }}
        ></div>
        <div 
          className="identity-features" 
          data-aos="zoom-in" 
          data-aos-delay="200" 
          id="identity-badges"
          style={{ display: 'flex', flexWrap: 'wrap', justifyContent: 'center', gap: '20px' }}
        >
          {data.badges?.map((b: any) => (
            <div 
              key={b.id} 
              className="badge" 
              style={{ fontSize: '1rem', padding: '10px 20px', color: b.color || '#ff6b00', background: b.color_fondo || 'rgba(255, 107, 0, 0.15)' }}
            >
              {b.texto}
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
