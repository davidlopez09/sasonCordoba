"use client";

import { useEffect, useState } from 'react';
import { resolveSiteUrl } from '@/lib/api';

export default function Navbar({ navData, config, buttons }: { navData: any, config: any, buttons: any }) {
  const [scrolled, setScrolled] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [scrollProgress, setScrollProgress] = useState(0);

  useEffect(() => {
    // Inyectar color de fondo de la API como variable CSS
    if (config?.color_nav_fondo) {
      document.documentElement.style.setProperty('--navbar-bg', config.color_nav_fondo);
    }

    const handleScroll = () => {
      const scrollTop = window.scrollY;
      setScrolled(scrollTop > 50);

      const docHeight = document.documentElement.scrollHeight - window.innerHeight;
      const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
      setScrollProgress(progress);
    };

    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Init
    return () => window.removeEventListener('scroll', handleScroll);
  }, [config]);

  // Lock body scroll when mobile menu is open
  useEffect(() => {
    if (mobileMenuOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  }, [mobileMenuOpen]);

  return (
    <>
      <div className="scroll-progress" id="scrollProgress" style={{ width: `${scrollProgress}%` }}></div>
      <nav id="navbar" className={`navbar ${scrolled ? 'scrolled' : ''}`}>
        <div className="nav-container">
          <a href="#home" className="logo" onClick={() => setMobileMenuOpen(false)}>
            {config?.logo_nav ? (
              <img src={config.logo_nav} alt="Sazón Córdoba" className="brand-logo loaded" />
            ) : (
              <span className="logo-text">Sazón <span className="logo-accent">Córdoba</span></span>
            )}
          </a>
          
          <div className={`nav-menu-wrapper ${mobileMenuOpen ? 'open' : ''}`}>
            <ul className="nav-links" id="nav-links">
              {navData?.map((item: any) => (
                <li key={item.id}>
                  <a 
                    href={resolveSiteUrl(item.enlace)}
                    onClick={() => setMobileMenuOpen(false)}
                    style={{ color: item.color || '#ffffff' }}
                  >
                    {item.etiqueta}
                  </a>
                </li>
              ))}
            </ul>
            
            <div className="nav-actions" id="nav-actions">
              {buttons?.map((btn: any) => (
                <a 
                  key={btn.id} 
                  href={resolveSiteUrl(btn.enlace)}
                  onClick={() => setMobileMenuOpen(false)}
                  className="nav-btn" 
                  style={{ background: btn.color_fondo, color: btn.color_texto, borderColor: btn.color_borde || 'transparent' }}
                >
                  {btn.texto}
                </a>
              ))}
            </div>
          </div>
          
          <button 
            className="mobile-menu-btn" 
            aria-label={mobileMenuOpen ? "Cerrar menú" : "Abrir menú"}
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          >
            <i className={`ph ${mobileMenuOpen ? 'ph-x' : 'ph-list'}`}></i>
          </button>
        </div>

        {mobileMenuOpen && (
          <div className="mobile-overlay" onClick={() => setMobileMenuOpen(false)}></div>
        )}
      </nav>
    </>
  );
}
