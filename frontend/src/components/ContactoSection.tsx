"use client";

export default function ContactoSection({ subtitle, config }: { subtitle: any, config: any }) {
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const form = e.target as HTMLFormElement;
    const msgEl = document.getElementById('msgContacto');
    if (!msgEl) return;
    
    const btn = form.querySelector('button[type="submit"]') as HTMLButtonElement;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Enviando...';
    msgEl.textContent = 'Procesando...';
    msgEl.className = 'form-message';

    try {
      const apiUrl = process.env.NEXT_PUBLIC_API_URL || '/sazon-cordoba/api';
      const res = await fetch(`${apiUrl}/index.php?route=contacto`, {
        method: 'POST',
        body: new FormData(form),
      });
      const data = await res.json();
      if (!res.ok || data.error) {
        msgEl.textContent = data.error || 'Ocurrió un error. Intenta de nuevo.';
        msgEl.className = 'form-message error';
        return;
      }
      msgEl.textContent = '¡Mensaje enviado! Te responderemos pronto.';
      msgEl.className = 'form-message success';
      form.reset();
    } catch (err) {
      msgEl.textContent = 'Error de conexión. Intenta de nuevo.';
      msgEl.className = 'form-message error';
    } finally {
      btn.disabled = false;
      btn.textContent = originalText || 'Enviar Mensaje';
    }
  };

  return (
    <section id="contacto" className="section section-dark">
      <div className="container contacto-container">
        
        <div className="contacto-form-wrapper" data-aos="fade-right">
          <h2 className="section-title" id="contacto-title" dangerouslySetInnerHTML={{ __html: subtitle?.titulo?.replace(/\n/g, '<br>') || 'Contáctanos' }}></h2>
          <p className="section-desc" id="contacto-subtitle">{subtitle?.subtitulo}</p>
          
          <form id="formContacto" onSubmit={handleSubmit}>
            <input type="text" name="nombre" placeholder="Nombre completo" required />
            <input type="email" name="correo" placeholder="Correo electrónico" required />
            <input type="tel" name="telefono" placeholder="Teléfono (opcional)" />
            <textarea name="mensaje" placeholder="Tu mensaje" required></textarea>
            <button type="submit" className="btn btn-primary btn-large">Enviar Mensaje</button>
            <p className="form-message" id="msgContacto"></p>
          </form>
        </div>
        
        <div className="contacto-map-wrapper" data-aos="fade-left">
          <iframe 
            id="contacto-map" 
            src={config?.evento_mapa_embed_url || ''} 
            loading="lazy" 
            referrerPolicy="no-referrer-when-downgrade" 
            title="Ubicación del evento"
          ></iframe>
        </div>

      </div>
    </section>
  );
}
