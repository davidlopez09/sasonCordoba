"use client";

import { useEffect, useState } from 'react';

export default function CountdownSection({ config }: { config: any }) {
  const [timeLeft, setTimeLeft] = useState({
    days: '00', hours: '00', minutes: '00', seconds: '00', finished: false
  });

  useEffect(() => {
    if (!config?.evento_fecha_inicio) return;

    const eventDate = new Date(config.evento_fecha_inicio).getTime();

    const updateTimer = () => {
      const now = new Date().getTime();
      const distance = eventDate - now;

      if (distance < 0) {
        setTimeLeft(prev => ({ ...prev, finished: true }));
        return;
      }

      const d = Math.floor(distance / (1000 * 60 * 60 * 24));
      const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const s = Math.floor((distance % (1000 * 60)) / 1000);

      setTimeLeft({
        days: d.toString().padStart(2, '0'),
        hours: h.toString().padStart(2, '0'),
        minutes: m.toString().padStart(2, '0'),
        seconds: s.toString().padStart(2, '0'),
        finished: false
      });
    };

    updateTimer();
    const interval = setInterval(updateTimer, 1000);
    return () => clearInterval(interval);
  }, [config]);

  if (!config?.evento_fecha_inicio) return null;

  return (
    <section id="countdown" className="section section-dark">
      <div className="container text-center">
        <h2 className="section-title" data-aos="fade-up">Faltan Para <span className="text-gradient">Sazón Córdoba</span></h2>
        <div className="countdown-timer" id="countdown-timer" data-aos="zoom-in" data-aos-delay="100">
          <div className="countdown-unit"><span className="countdown-number" id="cd-days">{timeLeft.days}</span><span className="countdown-label">Días</span></div>
          <div className="countdown-unit"><span className="countdown-number" id="cd-hours">{timeLeft.hours}</span><span className="countdown-label">Horas</span></div>
          <div className="countdown-unit"><span className="countdown-number" id="cd-minutes">{timeLeft.minutes}</span><span className="countdown-label">Min</span></div>
          <div className="countdown-unit"><span className="countdown-number" id="cd-seconds">{timeLeft.seconds}</span><span className="countdown-label">Seg</span></div>
        </div>
        <div className="countdown-info" data-aos="fade-up" data-aos-delay="200">
          <div className="countdown-info-item"><i className="ph ph-clock"></i><span id="countdown-horario">{config?.evento_horario || ''}</span></div>
          <div className="countdown-info-item"><i className="ph ph-map-pin"></i><span id="countdown-lugar">{config?.evento_lugar || ''}</span></div>
        </div>
      </div>
    </section>
  );
}
