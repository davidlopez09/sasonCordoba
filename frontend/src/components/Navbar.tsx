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

    window.addEventListener('scroll', handleScroll, { passive: true });
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
    return () => {
      document.body.style.overflow = '';
    };
  }, [mobileMenuOpen]);

  // Close mobile menu on Escape key
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') setMobileMenuOpen(false);
    };
    if (mobileMenuOpen) {
      window.addEventListener('keydown', handleKeyDown);
    }
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [mobileMenuOpen]);

  const closeMenu = () => setMobileMenuOpen(false);

  return (
    <>
      <div className="scroll-progress" id="scrollProgress" style={{ width: `${scrollProgress}%` }}></div>
      <nav id="navbar" className={`navbar ${scrolled ? 'scrolled' : ''}`}>
        <div className="nav-container">
          <a href="#home" className="logo" onClick={closeMenu}>
            {config?.logo_nav ? (
              <img src={config.logo_nav} alt="Sazón Córdoba" className="brand-logo loaded" />
            ) : (
              <span className="logo-text">Sazón <span className="logo-accent">Córdoba</span></span>
            )}
          </a>
          
          {/* Desktop Navigation */}
          <div className="nav-menu-wrapper">
            <ul className="nav-links" id="nav-links">
              {navData?.map((item: any) => (
                <li key={item.id}>
                  <a 
                    href={resolveSiteUrl(item.enlace)}
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
                  className="nav-btn" 
                  style={{ background: btn.color_fondo, color: btn.color_texto, borderColor: btn.color_borde || 'transparent' }}
                >
                  {btn.texto}
                </a>
              ))}
            </div>
          </div>
          
          {/* Mobile hamburger button */}
          <button 
            className="mobile-menu-btn" 
            aria-label={mobileMenuOpen ? "Cerrar menú" : "Abrir menú"}
            aria-expanded={mobileMenuOpen}
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          >
            <i className={`ph ${mobileMenuOpen ? 'ph-x' : 'ph-list'}`}></i>
          </button>
        </div>
      </nav>

      {/* Mobile Drawer & Overlay rendered outside <nav> to prevent backdrop-filter clipping or horizontal overflow */}
      <div 
        className={`mobile-overlay ${mobileMenuOpen ? 'open' : ''}`} 
        onClick={closeMenu}
        aria-hidden={!mobileMenuOpen}
      ></div>

      <div 
        className={`mobile-nav-drawer ${mobileMenuOpen ? 'open' : ''}`}
        aria-hidden={!mobileMenuOpen}
      >
        <div className="mobile-drawer-header">
          <div className="mobile-drawer-brand">
            {config?.logo_nav ? (
              <img src={config.logo_nav} alt="Sazón Córdoba" />
            ) : (
              <span>Sazón <span className="logo-accent">Córdoba</span></span>
            )}
          </div>
          <button 
            className="mobile-drawer-close" 
            onClick={closeMenu}
            aria-label="Cerrar menú"
          >
            <i className="ph ph-x"></i>
          </button>
        </div>

        <ul className="mobile-drawer-links">
          {navData?.map((item: any) => (
            <li key={`mob-${item.id}`}>
              <a 
                href={resolveSiteUrl(item.enlace)}
                onClick={closeMenu}
              >
                {item.etiqueta}
              </a>
            </li>
          ))}
        </ul>

        {buttons && buttons.length > 0 && (
          <div className="mobile-drawer-actions">
            {buttons.map((btn: any) => (
              <a 
                key={`mob-btn-${btn.id}`} 
                href={resolveSiteUrl(btn.enlace)}
                onClick={closeMenu}
                className="nav-btn" 
                style={{ background: btn.color_fondo, color: btn.color_texto, borderColor: btn.color_borde || 'transparent' }}
              >
                {btn.texto}
              </a>
            ))}
          </div>
        )}
      </div>
    </>
  );
}
