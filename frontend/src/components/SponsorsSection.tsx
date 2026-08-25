export default function SponsorsSection({ data, subtitle }: { data: any[], subtitle: any }) {
  if (!data || data.length === 0) return null;

  return (
    <section id="sponsors" className="section">
      <div className="container text-center">
        <h2 className="section-title" data-aos="fade-up" id="sponsors-title" dangerouslySetInnerHTML={{ __html: subtitle?.titulo?.replace(/\n/g, '<br>') || 'Nuestros <span class="text-gradient">Patrocinadores</span>' }}></h2>
        <p className="section-subtitle" data-aos="fade-up" data-aos-delay="100" id="sponsors-subtitle">{subtitle?.subtitulo || 'Haciendo posible el mejor evento de la ciudad.'}</p>
        
        <div className="sponsors-marquee" data-aos="zoom-in" data-aos-delay="200">
          <div className="sponsors-track" id="sponsors-grid">
            {data.map((p: any) => (
              <div key={p.id} className="sponsor-logo">
                <img src={p.logo || 'https://via.placeholder.com/150x80/222/FFF?text=' + encodeURIComponent(p.nombre)} alt={p.nombre} style={{ maxHeight: '120px' }} loading="lazy" />
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
