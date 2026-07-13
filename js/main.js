async function loadNavData() {
    try {
        const res = await fetch('api/site?route=nav');
        if (!res.ok) throw new Error('Error al cargar el nav');
        const data = await res.json();
        renderNav(data.menu_nav, data.configuraciones, data.botones_nav);
    } catch (e) {
        console.warn('Nav API no disponible:', e);
    }
}

const SECTION_VISIBILITY_MAP = {
    identity: 'mostrar_identidad',
    about: 'mostrar_about',
    chefs: 'mostrar_chefs',
    dishes: 'mostrar_platillos',
    itinerary: 'mostrar_itinerario',
    sponsors: 'mostrar_sponsors',
    faq: 'mostrar_faq',
};

function toggleSection(id, visible) {
    const el = document.getElementById(id);
    if (el) el.style.display = visible === '0' ? 'none' : '';
}

function renderBloqueTexto(c) {
    return `<div class="dyn-bloque dyn-bloque-texto">
        ${c.titulo ? `<h3 style="color:${c.color || 'var(--text-main)'}">${c.titulo}</h3>` : ''}
        ${c.texto ? `<p style="color:${c.color || 'var(--text-main)'}">${c.texto}</p>` : ''}
    </div>`;
}

function renderBloqueImagen(c) {
    if (!c.url) return '';
    return `<div class="dyn-bloque dyn-bloque-imagen"><img src="${c.url}" alt=""></div>`;
}

function renderBloqueBoton(c) {
    if (!c.texto) return '';
    return `<div class="dyn-bloque dyn-bloque-boton">
        <a href="${c.enlace || '#'}" class="btn" style="background:${c.color_fondo || 'var(--primary)'}; color:${c.color_texto || '#ffffff'}; border-color:${c.color_borde || 'transparent'}">${c.texto}</a>
    </div>`;
}

function renderBloqueTarjetas(c) {
    const items = c.items || [];
    if (!items.length) return '';
    return `<div class="dyn-bloque dyn-bloque-tarjetas">
        ${items.map(it => `
            <div class="dyn-tarjeta">
                ${it.imagen ? `<img src="${it.imagen}" alt="">` : ''}
                ${it.titulo ? `<h4>${it.titulo}</h4>` : ''}
                ${it.descripcion ? `<p>${it.descripcion}</p>` : ''}
            </div>
        `).join('')}
    </div>`;
}

const BLOQUE_RENDERERS = {
    texto: renderBloqueTexto,
    imagen: renderBloqueImagen,
    boton: renderBloqueBoton,
    tarjetas: renderBloqueTarjetas,
};

function renderBloque(bloque) {
    const fn = BLOQUE_RENDERERS[bloque.tipo];
    return fn ? fn(bloque.contenido || {}) : '';
}

function renderSeccionDinamica(seccion) {
    const bloques = seccion.bloques || [];
    let html = '';
    let i = 0;
    while (i < bloques.length) {
        const actual = bloques[i];
        const siguiente = bloques[i + 1];
        if (actual.posicion === 'izquierda' && siguiente?.posicion === 'derecha') {
            html += `<div class="dyn-row-2col">
                <div class="dyn-col">${renderBloque(actual)}</div>
                <div class="dyn-col">${renderBloque(siguiente)}</div>
            </div>`;
            i += 2;
        } else {
            html += renderBloque(actual);
            i += 1;
        }
    }
    return html;
}

function renderSeccionesDinamicas(secciones) {
    document.querySelectorAll('.dyn-section').forEach(el => el.remove());
    const lastInserted = {};
    (secciones || []).forEach(seccion => {
        const anchorId = seccion.insertar_despues;
        const refEl = lastInserted[anchorId] || document.getElementById(anchorId);
        if (!refEl) return;
        const el = document.createElement('section');
        el.className = 'section dyn-section';
        el.id = 'dyn-' + seccion.id;
        el.innerHTML = `<div class="container">${renderSeccionDinamica(seccion)}</div>`;
        refEl.insertAdjacentElement('afterend', el);
        lastInserted[anchorId] = el;
    });
}

async function loadSiteData() {
    try {
        const res = await fetch('api/site');
        if (!res.ok) throw new Error('Error al cargar datos');
        const data = await res.json();

        const config = data.configuraciones || {};
        Object.entries(SECTION_VISIBILITY_MAP).forEach(([sectionId, clave]) => {
            toggleSection(sectionId, config[clave]);
        });

        if (config.mostrar_identidad !== '0') renderIdentity(data.identidad);
        if (config.mostrar_about !== '0') renderAbout(data.about);
        if (config.mostrar_chefs !== '0') renderChefs(data.exponentes, data.subtitulos);
        if (config.mostrar_platillos !== '0') renderDishes(data.platillos_destacados, data.subtitulos);
        if (config.mostrar_itinerario !== '0') renderItinerary(data.itinerario, data.subtitulos);
        if (config.mostrar_sponsors !== '0') renderSponsors(data.patrocinadores, data.subtitulos);
        if (config.mostrar_faq !== '0') renderFaq(data.faq, data.subtitulos);

        renderHero(data.hero);
        renderFooter(data.footer, data.configuraciones, data.menu_nav);
        renderSeccionesDinamicas(data.secciones_dinamicas);

        AOS.refresh();
    } catch (e) {
        console.warn('API no disponible:', e);
    }
}

function renderNav(menu, config, botones) {
    const ul = document.getElementById('nav-links');
    if (ul) {
        ul.innerHTML = (menu || []).map(item =>
            `<li><a href="${item.enlace}" style="color:${item.color || '#ffffff'}">${item.etiqueta}</a></li>`
        ).join('');
    }

    const actions = document.getElementById('nav-actions');
    if (actions) {
        actions.innerHTML = (botones || []).map(b =>
            `<a href="${b.enlace}" class="nav-btn" style="background:${b.color_fondo}; color:${b.color_texto}; border-color:${b.color_borde || 'transparent'}">${b.texto}</a>`
        ).join('');
    }

    const navLogo = document.querySelector('.brand-logo');
    if (navLogo && config?.logo_nav) {
        navLogo.src = config.logo_nav;
        navLogo.classList.add('loaded');
    }

    if (config?.color_nav_fondo) {
        document.documentElement.style.setProperty('--navbar-bg', config.color_nav_fondo);
    }
}

function renderHero(hero) {
    const slider = document.getElementById('hero-slider');
    const stats = document.getElementById('hero-stats');

    if (hero.slides?.length) {
        slider.innerHTML = hero.slides.map((s, i) =>
            `<div class="hero-slide${i === 0 ? ' active' : ''}" style="background-image: url('${s.imagen}')"></div>`
        ).join('');
    }

    const texto = hero.texto;
    if (texto) {
        const badgeEl = document.getElementById('hero-badge');
        const titleEl = document.getElementById('hero-title');
        const subtitleEl = document.getElementById('hero-subtitle');

        badgeEl.textContent = texto.texto_badge || '';
        titleEl.innerHTML = (texto.titulo || '').replace(/\n/g, '<br>');
        subtitleEl.textContent = texto.subtitulo || '';

        [badgeEl, titleEl, subtitleEl].forEach(el => {
            el.classList.add('loaded');
            if (texto.color) el.style.color = texto.color;
        });
    }

    const actions = document.getElementById('hero-actions');
    if (actions) {
        actions.innerHTML = (hero.botones || []).map(b =>
            `<a href="${b.enlace}" class="btn btn-large" style="background:${b.color_fondo}; color:${b.color_texto}; border-color:${b.color_borde}">${b.texto}</a>`
        ).join('');
    }

    if (hero.estadisticas?.length) {
        stats.innerHTML = hero.estadisticas.map(e =>
            `<div class="stat-item">
                <h3 class="stat-number" style="color:${e.color || 'var(--primary)'}">${e.numero}</h3>
                <p class="stat-label" style="color:${e.color || 'var(--text-muted)'}">${e.etiqueta}</p>
            </div>`
        ).join('');
    }

    startSlider();
}

function renderIdentity(identidad) {
    if (!identidad?.seccion) return;
    const s = identidad.seccion;
    const titleEl = document.getElementById('identity-title');
    const descEl = document.getElementById('identity-desc');
    const badgesEl = document.getElementById('identity-badges');

    if (s.titulo) { titleEl.innerHTML = s.titulo.replace(/\n/g, '<br>'); if (s.color) titleEl.style.color = s.color; }
    if (s.descripcion) { descEl.innerHTML = s.descripcion.replace(/\n/g, '<br>'); if (s.color) descEl.style.color = s.color; }

    if (identidad.badges?.length) {
        badgesEl.style.display = 'flex';
        badgesEl.style.flexWrap = 'wrap';
        badgesEl.style.justifyContent = 'center';
        badgesEl.style.gap = '20px';
        badgesEl.innerHTML = identidad.badges.map(b =>
            `<div class="badge" style="font-size: 1rem; padding: 10px 20px; color:${b.color || '#ff6b00'}; background:${b.color_fondo || 'rgba(255, 107, 0, 0.15)'}">${b.texto}</div>`
        ).join('');
    }
}

function renderAbout(about) {
    if (!about.seccion) return;
    const s = about.seccion;
    const img = document.getElementById('about-img');
    if (s.imagen) {
        img.src = s.imagen;
        img.classList.add('loaded');
    }
    const aboutTitleEl = document.getElementById('about-title');
    const aboutDescEl = document.getElementById('about-desc');
    if (s.titulo) { aboutTitleEl.innerHTML = s.titulo.replace(/\n/g, '<br>'); if (s.color) aboutTitleEl.style.color = s.color; }
    if (s.descripcion) { aboutDescEl.textContent = s.descripcion; if (s.color) aboutDescEl.style.color = s.color; }

    const container = document.getElementById('about-features');
    if (about.caracteristicas?.length) {
        container.innerHTML = about.caracteristicas.map(c =>
            `<div class="feature">
                <div class="feature-icon"><i class="${c.icono}"></i></div>
                <div>
                    <h4 style="color:${c.color || '#1a1a1a'}">${c.titulo}</h4>
                    <p style="color:${c.color || '#1a1a1a'}">${c.descripcion}</p>
                </div>
            </div>`
        ).join('');
    }
}

function renderChefs(exponentes, subtitulos) {
    if (!exponentes?.length) return;
    const grid = document.getElementById('chefs-grid');
    grid.innerHTML = exponentes.map((e, i) =>
        `<div class="chef-card" data-aos="zoom-in" data-aos-delay="${100 + i * 100}">
            <div class="chef-img-wrapper">
                <img src="${e.foto || 'https://via.placeholder.com/600/222/FFF?text=Chef'}" alt="${e.nombre}">
                <div class="chef-socials">
                    ${e.instagram_url ? `<a href="${e.instagram_url}"><i class="ph ph-instagram-logo"></i></a>` : ''}
                    ${e.twitter_url ? `<a href="${e.twitter_url}"><i class="ph ph-twitter-logo"></i></a>` : ''}
                </div>
            </div>
            <div class="chef-info">
                <h3 style="color:${e.color || '#1a1a1a'}">${e.nombre}</h3>
                <p style="color:${e.color || '#1a1a1a'}">${e.especialidad}</p>
            </div>
        </div>`
    ).join('');

    setSubtitles('chefs', subtitulos);
}

function renderDishes(platillos, subtitulos) {
    if (!platillos?.length) return;
    const gallery = document.getElementById('dishes-gallery');
    gallery.innerHTML = platillos.map((p, i) =>
        `<div class="dish-item" data-aos="fade-up" data-aos-delay="${100 + i * 100}">
            <img src="${p.imagen || 'https://via.placeholder.com/600/222/FFF?text=Platillo'}" alt="${p.nombre}">
            <div class="dish-overlay">
                <h4 style="color:${p.color || '#ffffff'}">${p.nombre}</h4>
                <p style="color:${p.color || '#ffffff'}">${p.descripcion}</p>
            </div>
        </div>`
    ).join('');

    setSubtitles('dishes', subtitulos);
}

function renderItinerary(itinerario, subtitulos) {
    if (!itinerario?.length) return;
    const timeline = document.getElementById('timeline');
    timeline.innerHTML = itinerario.map(item =>
        `<div class="timeline-item" data-aos="fade-up">
            <div class="timeline-time" style="color:${item.color || '#1a1a1a'}">
                <span class="time" style="color:${item.color || '#1a1a1a'}">${item.hora}</span>
                <span class="day" style="color:${item.color || '#1a1a1a'}">${item.dia}</span>
            </div>
            <div class="timeline-content" style="background:${item.color_fondo || '#ffffff'}; border-color:${item.color_borde || 'rgba(0,0,0,0.08)'}">
                <h3 class="timeline-title" style="color:${item.color || '#1a1a1a'}">${item.titulo}</h3>
                <p class="timeline-chef" style="color:${item.color || '#1a1a1a'}">Por: <strong>${item.nombre_chef}</strong></p>
                <p class="timeline-desc" style="color:${item.color || '#1a1a1a'}">${item.descripcion}</p>
            </div>
        </div>`
    ).join('');

    setSubtitles('itinerary', subtitulos);
}

function renderSponsors(patrocinadores, subtitulos) {
    setSubtitles('sponsors', subtitulos);

    if (!patrocinadores?.length) return;
    const grid = document.getElementById('sponsors-grid');
    grid.innerHTML = patrocinadores.map(p =>
        `<div class="sponsor-logo">
            <img src="${p.logo || 'https://via.placeholder.com/150x80/222/FFF?text=' + encodeURIComponent(p.nombre)}" alt="${p.nombre}" style="max-height: 120px;">
        </div>`
    ).join('');
}

function renderFaq(faq, subtitulos) {
    if (!faq?.length) return;
    const container = document.getElementById('faq-container');
    container.innerHTML = faq.map(item =>
        `<div class="faq-item">
            <div class="faq-question">
                <span style="color:${item.color || '#1a1a1a'}">${item.pregunta}</span>
                <i class="ph ph-caret-down"></i>
            </div>
            <div class="faq-answer">
                <p style="color:${item.color || '#1a1a1a'}">${item.respuesta}</p>
            </div>
        </div>`
    ).join('');

    setSubtitles('faq', subtitulos);

    document.querySelectorAll('.faq-question').forEach(q => {
        q.addEventListener('click', () => {
            const item = q.parentElement;
            const isActive = item.classList.contains('active');
            document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));
            if (!isActive) item.classList.add('active');
        });
    });
}

function renderFooter(footer, config, menuNav) {
    const container = document.getElementById('footer-container');
    if (!container) return;

    const footerEl = document.querySelector('.footer');
    if (footerEl && config?.color_footer_fondo) {
        footerEl.style.setProperty('--footer-bg', config.color_footer_fondo);
    }
    const globalColor = config?.color_footer_texto || '';

    const col1 = footer?.filter(f => f.columna === '1') || [];
    const col2 = footer?.filter(f => f.columna === '2') || [];
    const col3 = footer?.filter(f => f.columna === '3') || [];

    container.innerHTML = renderFooterCol(col1, 'brand', globalColor) + renderFooterCol(col2, 'links', globalColor, menuNav) + renderFooterCol(col3, 'contact', globalColor);

    const copyright = document.getElementById('footer-copyright');
    if (copyright && config?.footer_copyright) {
        copyright.innerHTML = config.footer_copyright;
        if (globalColor) copyright.style.color = globalColor;
    }
}

function renderFooterCol(items, type, globalColor, menuNav) {
    if (!items?.length) return '';
    let className, content;

    if (type === 'brand') {
        className = 'footer-brand';
        content = '';
        items.forEach(item => {
            if (item.tipo === 'texto') {
                const c = item.color || globalColor;
                content += `<p${c ? ` style="color:${c}"` : ''}>${item.contenido || ''}</p>`;
            }
        });
        const socials = items.filter(i => i.tipo === 'red_social');
        if (socials.length) {
            content += `<div class="footer-socials">`;
            socials.forEach(s => {
                content += `<a href="${s.url || '#'}"><i class="${s.icono}"></i></a>`;
            });
            content += `</div>`;
        }
    } else if (type === 'links') {
        className = 'footer-links';
        const header = items.find(i => i.titulo);
        const headerColor = header?.color || globalColor;
        content = `<h3${headerColor ? ` style="color:${headerColor}"` : ''}>${header?.titulo || 'Enlaces'}</h3><ul>`;
        (menuNav || []).forEach(item => {
            const c = item.color || globalColor;
            content += `<li><a href="${item.enlace}"${c ? ` style="color:${c}"` : ''}>${item.etiqueta}</a></li>`;
        });
        content += `</ul>`;
    } else {
        className = 'footer-contact';
        const header = items.find(i => i.titulo);
        const headerColor = header?.color || globalColor;
        content = `<h3${headerColor ? ` style="color:${headerColor}"` : ''}>${header?.titulo || 'Contacto'}</h3><ul>`;
        items.filter(i => i.tipo === 'texto').forEach(item => {
            const c = item.color || globalColor;
            content += `<li${c ? ` style="color:${c}"` : ''}>${item.icono ? `<i class="${item.icono}"></i>` : ''}${item.contenido || ''}</li>`;
        });
        content += `</ul>`;
    }

    return `<div class="${className}">${content}</div>`;
}

function setSubtitles(seccion, subtitulos) {
    if (!subtitulos?.[seccion]) return;
    const s = subtitulos[seccion];
    const titleEl = document.getElementById(`${seccion}-title`);
    const subEl = document.getElementById(`${seccion}-subtitle`);
    if (titleEl && s.titulo) {
        const parts = s.titulo.split(/\s+(.+)/);
        const gradientStyle = s.color ? ` style="-webkit-text-fill-color:${s.color}; color:${s.color}"` : '';
        if (parts.length > 1) {
            titleEl.innerHTML = `${parts[0]} <span class="text-gradient"${gradientStyle}>${parts[1]}</span>`;
        } else {
            titleEl.innerHTML = s.titulo;
        }
        if (s.color) titleEl.style.color = s.color;
    }
    if (subEl && s.subtitulo) {
        subEl.textContent = s.subtitulo;
        if (s.color) subEl.style.color = s.color;
    }
}

let sliderInterval;

function startSlider() {
    if (sliderInterval) clearInterval(sliderInterval);
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length < 2) return;
    let current = 0;
    sliderInterval = setInterval(() => {
        slides[current].classList.remove('active');
        current = (current + 1) % slides.length;
        slides[current].classList.add('active');
    }, 5000);
}

document.addEventListener('DOMContentLoaded', () => {
    AOS.init({ duration: 800, easing: 'ease-in-out-cubic', once: true, offset: 50 });

    Promise.all([loadNavData(), loadSiteData()])
        .catch(e => console.warn('Error cargando el sitio:', e))
        .finally(() => {
            document.getElementById('pageLoader')?.classList.add('loaded');
        });

    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 50);
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                const navbarHeight = document.querySelector('.navbar').offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.scrollY - navbarHeight;
                window.scrollTo({ top: targetPosition, behavior: 'smooth' });
            }
        });
    });

    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    mobileMenuBtn.addEventListener('click', () => {
        if (navLinks.style.display === 'flex') {
            navLinks.style.display = '';
        } else {
            navLinks.style.display = 'flex';
            navLinks.style.flexDirection = 'column';
            navLinks.style.position = 'absolute';
            navLinks.style.top = '100%';
            navLinks.style.left = '0';
            navLinks.style.width = '100%';
            navLinks.style.background = 'var(--glass-bg)';
            navLinks.style.backdropFilter = 'blur(12px)';
            navLinks.style.padding = '20px';
            navLinks.style.borderBottom = '1px solid var(--glass-border)';
        }
    });
});
