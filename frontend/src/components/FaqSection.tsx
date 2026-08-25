"use client";

import { useState } from 'react';

export default function FaqSection({ data, subtitle }: { data: any[], subtitle: any }) {
  const [openId, setOpenId] = useState<number | null>(null);

  if (!data || data.length === 0) return null;

  return (
    <section id="faq" className="section">
      <div className="container">
        <div className="text-center">
          <h2 className="section-title" data-aos="fade-up" id="faq-title" dangerouslySetInnerHTML={{ __html: subtitle?.titulo?.replace(/\n/g, '<br>') || 'Preguntas <span class="text-gradient">Frecuentes</span>' }}></h2>
          <p className="section-subtitle" data-aos="fade-up" data-aos-delay="100" id="faq-subtitle">{subtitle?.subtitulo}</p>
        </div>
        
        <div className="faq-container" data-aos="fade-up" data-aos-delay="200" id="faq-container">
          {data.map((faq: any) => {
            const isOpen = openId === faq.id;
            return (
              <div key={faq.id} className={`faq-item ${isOpen ? 'active' : ''}`} onClick={() => setOpenId(isOpen ? null : faq.id)}>
                <div className="faq-question">
                  <span style={{ color: faq.color || '#1a1a1a' }}>{faq.pregunta}</span>
                  <i className="ph ph-caret-down"></i>
                </div>
                <div className="faq-answer" style={{ maxHeight: isOpen ? '500px' : '0' }}>
                  <p style={{ color: faq.color || '#1a1a1a' }}>{faq.respuesta}</p>
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
