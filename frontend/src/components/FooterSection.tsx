"use client";

import { useEffect, useRef } from 'react';
import { resolveSiteUrl } from '@/lib/api';

const getIconClass = (icon: string) => {
  if (!icon) return '';
  if (icon.startsWith('ph ')) return icon;
  if (icon.startsWith('ph-')) return `ph ${icon}`;
  return `ph ph-${icon}`;
};

export default function FooterSection({ data, config, navData }: { data: any[], config: any, navData: any[] }) {
  const footerRef = useRef<HTMLElement>(null);

  useEffect(() => {
    if (config?.color_footer_fondo && footerRef.current) {
      footerRef.current.style.setProperty('--footer-bg', config.color_footer_fondo);
    }
  }, [config]);

  if (!data || data.length === 0) return null;

  const globalColor = config?.color_footer_texto || '';
  const col1 = data.filter(f => f.columna === '1');
  const col2 = data.filter(f => f.columna === '2');
  const col3 = data.filter(f => f.columna === '3');

  const renderCol1 = () => {
    if (!col1.length) return null;
    const socialItems = col1.filter(i => i.tipo === 'red_social' && (i.icono || (i.url && i.url !== '#')));
    return (
      <div className="footer-brand">
        {config?.logo_nav && (
          <img src={config.logo_nav} alt="Sazón Córdoba" className="footer-logo" loading="lazy" />
        )}
        {col1.filter(i => i.tipo === 'texto').map((item, idx) => {
          const c = item.color || globalColor;
          return (
            <p key={idx} className={idx === 0 ? 'footer-tagline' : 'footer-note'} style={{ color: idx === 0 && !c ? undefined : c }}>
              {item.contenido}
            </p>
          );
        })}
        {socialItems.length > 0 && (
          <div className="footer-socials">
            {socialItems.map((item, idx) => (
              <a key={idx} href={resolveSiteUrl(item.url)} aria-label="Red social"><i className={getIconClass(item.icono)}></i></a>
            ))}
          </div>
        )}
      </div>
    );
  };

  const renderCol2 = () => {
    if (!col2.length) return null;
    const header = col2.find(i => i.titulo);
    const headerColor = header?.color || globalColor;
    return (
      <div className="footer-links">
        <h3 style={{ color: headerColor }}>{header?.titulo || 'Enlaces'}</h3>
        <ul>
          {navData?.map((item: any, idx: number) => {
            const c = item.color || globalColor;
            return <li key={idx}><a href={resolveSiteUrl(item.enlace)} style={{ color: c }}>{item.etiqueta}</a></li>;
          })}
          {col2.filter(i => i.tipo === 'enlace' && !i.titulo).map((item, idx) => {
            const c = item.color || globalColor;
            return <li key={`link-${idx}`}><a href={resolveSiteUrl(item.url)} style={{ color: c }}>{item.contenido}</a></li>;
          })}
        </ul>
      </div>
    );
  };

  const renderCol3 = () => {
    if (!col3.length) return null;
    const header = col3.find(i => i.titulo);
    const headerColor = header?.color || globalColor;
    return (
      <div className="footer-contact">
        <h3 style={{ color: headerColor }}>{header?.titulo || 'Contacto'}</h3>
        <ul>
          {col3.filter(i => i.tipo === 'texto').map((item, idx) => {
            const c = item.color || globalColor;
            return (
              <li key={idx} style={{ color: c }}>
                {item.icono && <i className={getIconClass(item.icono)}></i>} {item.contenido}
              </li>
            );
          })}
        </ul>
      </div>
    );
  };

  return (
    <footer className="footer" id="footer" ref={footerRef}>
      <div className="container footer-container" id="footer-container">
        {renderCol1()}
        {renderCol2()}
        {renderCol3()}
      </div>
      <div className="footer-bottom">
        <p id="footer-copyright" style={{ color: globalColor }} dangerouslySetInnerHTML={{ __html: config?.footer_copyright || '' }}></p>
      </div>
    </footer>
  );
}
