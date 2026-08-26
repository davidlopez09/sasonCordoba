"use client";

import { useState } from 'react';

export default function RegistroSection({ subtitle }: { subtitle: any }) {
  const [activeTab, setActiveTab] = useState<'visitante' | 'expositor'>('visitante');

  const handleSubmit = async (e: React.FormEvent, type: string) => {
    e.preventDefault();
    const form = e.target as HTMLFormElement;
    const msgEl = document.getElementById(`msg${type === 'visitante' ? 'Visitante' : 'Expositor'}`);
    if (!msgEl) return;
    
    const btn = form.querySelector('button[type="submit"]') as HTMLButtonElement;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Enviando...';
    msgEl.textContent = 'Procesando...';
    msgEl.className = 'form-message';

    const route = type === 'visitante' ? 'registro_visitante' : 'registro_expositor';
    const successMsg = type === 'visitante' 
          ? '¡Registro exitoso! Revisa tu correo para la confirmación.' 
          : '¡Postulación enviada! Te contactaremos pronto.';

    try {
      const res = await fetch(`http://localhost/sasoncordoba/api/index.php?route=${route}`, {
        method: 'POST',
        body: new FormData(form),
      });
      const data = await res.json();
      if (!res.ok || data.error) {
        msgEl.textContent = data.error || 'Ocurrió un error. Intenta de nuevo.';
        msgEl.className = 'form-message error';
        return;
      }
      msgEl.textContent = successMsg;
      msgEl.className = 'form-message success';
      form.reset();
    } catch (err) {
      msgEl.textContent = 'Error de conexión. Intenta de nuevo.';
      msgEl.className = 'form-message error';
    } finally {
      btn.disabled = false;
      btn.textContent = originalText || 'Enviar';
    }
  };

  return (
    <section id="registro" className="section section-dark">
      <div className="container">
        <div className="text-center">
          <h2 className="section-title" data-aos="fade-up" id="registro-title" dangerouslySetInnerHTML={{ __html: subtitle?.titulo?.replace(/\n/g, '<br>') || 'Regístrate' }}></h2>
          <p className="section-subtitle" data-aos="fade-up" data-aos-delay="100" id="registro-subtitle">{subtitle?.subtitulo}</p>
        </div>

        <div className="registro-tabs" data-aos="fade-up" data-aos-delay="150">
          <button 
            type="button" 
            className={`registro-tab ${activeTab === 'visitante' ? 'active' : ''}`} 
            onClick={() => setActiveTab('visitante')}
          >
            Soy Visitante
          </button>
          <button 
            type="button" 
            className={`registro-tab ${activeTab === 'expositor' ? 'active' : ''}`} 
            onClick={() => setActiveTab('expositor')}
          >
            Quiero Ser Expositor
          </button>
        </div>

        <form 
          className={`registro-form ${activeTab === 'visitante' ? 'active' : ''}`} 
          id="formVisitante" 
          onSubmit={(e) => handleSubmit(e, 'visitante')}
        >
          <div className="form-row">
            <input type="text" name="nombre" placeholder="Nombre completo" required />
            <input type="email" name="correo" placeholder="Correo electrónico" required />
          </div>
          <input type="tel" name="telefono" placeholder="Teléfono (opcional)" />
          <button type="submit" className="btn btn-primary btn-large">Registrarme como Visitante</button>
          <p className="form-message" id="msgVisitante"></p>
        </form>

        <form 
          className={`registro-form ${activeTab === 'expositor' ? 'active' : ''}`} 
          id="formExpositor" 
          onSubmit={(e) => handleSubmit(e, 'expositor')}
        >
          <div className="form-row">
            <input type="text" name="nombre_empresa" placeholder="Nombre del negocio o marca" required />
            <select name="categoria" required defaultValue="">
              <option value="" disabled>Categoría</option>
              <option value="Gastronomía">Gastronomía</option>
              <option value="Bebidas">Bebidas</option>
              <option value="Artesanías">Artesanías</option>
              <option value="Otros">Otros</option>
            </select>
          </div>
          <div className="form-row">
            <input type="text" name="nombre_contacto" placeholder="Nombre de contacto" required />
            <input type="email" name="correo" placeholder="Correo electrónico" required />
          </div>
          <input type="tel" name="telefono" placeholder="Teléfono (opcional)" />
          <textarea name="descripcion" placeholder="Cuéntanos sobre tu negocio o producto (opcional)"></textarea>
          <button type="submit" className="btn btn-primary btn-large">Postularme como Expositor</button>
          <p className="form-message" id="msgExpositor"></p>
        </form>
      </div>
    </section>
  );
}
