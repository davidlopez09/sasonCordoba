"use client";

import { useEffect, useState } from 'react';

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

  const mobileStyles = mobileMenuOpen ? {
    display: 'flex',
    flexDirection: 'column' as const,
    position: 'absolute' as const,
    top: '100%',
    left: '0',
    width: '100%',
    background: 'var(--navbar-bg, #0a0a0a)',
    padding: '20px',
    boxShadow: '0 10px 30px rgba(0,0,0,0.5)'
  } : {};

  return (
    <>
      <div className="scroll-progress" id="scrollProgress" style={{ width: `${scrollProgress}%` }}></div>
      <nav id="navbar" className={`navbar ${scrolled ? 'scrolled' : ''}`}>
        <div className="nav-container">
          <a href="#home" className="logo">
            {config?.logo_nav ? (
              <img src={config.logo_nav} alt="Sazón Córdoba" className="brand-logo loaded" />
            ) : (
              <span className="logo-text">Sazón <span className="text-gradient">Córdoba</span></span>
            )}
          </a>
          
          <ul className="nav-links" id="nav-links" style={mobileMenuOpen ? mobileStyles : undefined}>
            {navData?.map((item: any) => (
              <li key={item.id}>
                <a href={item.enlace} style={{ color: item.color || '#ffffff' }}>{item.etiqueta}</a>
              </li>
            ))}
          </ul>
          
          <div className="nav-actions" id="nav-actions" style={mobileMenuOpen ? mobileStyles : undefined}>
            {buttons?.map((btn: any) => (
              <a key={btn.id} href={btn.enlace} className="nav-btn" style={{ background: btn.color_fondo, color: btn.color_texto, borderColor: btn.color_borde || 'transparent' }}>
                {btn.texto}
              </a>
            ))}
          </div>
          
          <button 
            className="mobile-menu-btn" 
            aria-label="Abrir menú"
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          >
            <i className="ph ph-list"></i>
          </button>
        </div>
      </nav>
    </>
  );
}
