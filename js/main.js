async function loadSiteData() {
    try {
        const res = await fetch('api/site');
        if (!res.ok) throw new Error('Error al cargar datos');
        const data = await res.json();

        renderNav(data.menu_nav, data.configuraciones);
        renderHero(data.hero);
        renderIdentity(data.identidad);
        renderAbout(data.about);
        renderChefs(data.exponentes, data.subtitulos);
        renderDishes(data.platillos_destacados, data.subtitulos);
        renderItinerary(data.itinerario, data.subtitulos);
        renderSponsors(data.patrocinadores);
        renderFaq(data.faq, data.subtitulos);
        renderFooter(data.footer, data.configuraciones);

        AOS.refresh();
    } catch (e) {
        console.warn('API no disponible:', e);
    }
}

function renderNav(menu, config) {
    const ul = document.getElementById('nav-links');
    if (ul) {
        ul.innerHTML = (menu || []).map(item =>
            `<li><a href="${item.enlace}">${item.etiqueta}</a></li>`
        ).join('');
    }

    const btn = document.getElementById('btn-reservar');
    if (btn && config) {
        btn.href = config.reservar_url || '#itinerary';
        btn.textContent = config.reservar_texto || 'Reservar Ahora';
    }

    const navLogo = document.querySelector('.brand-logo');
    if (navLogo && config?.logo_nav) {
        navLogo.src = config.logo_nav;
    }

    if (config?.color_nav_texto) {
        document.documentElement.style.setProperty('--nav-link-color', config.color_nav_texto);
    }
}

function renderHero(hero) {
    const slider = document.getElementById('hero-slider');
    const stats = document.getElementById('hero-stats');

    if (hero.slides?.length) {
        slider.innerHTML = hero.slides.map((s, i) =>
            `<div class="hero-slide${i === 0 ? ' active' : ''}" style="background-image: url('${s.imagen}')"></div>`
        ).join('');

        const first = hero.slides[0];
        document.getElementById('hero-badge').textContent = first.texto_badge || 'Edición 2026';
        document.getElementById('hero-title').innerHTML = (first.titulo || 'El Sabor que <br><span class="text-gradient">Enciende</span> a Montería').replace(/\n/g, '<br>');
        document.getElementById('hero-subtitle').textContent = first.subtitulo || 'Únete al evento culinario más prestigioso de la región.';
    }

    if (hero.estadisticas?.length) {
        stats.innerHTML = hero.estadisticas.map(e =>
            `<div class="stat-item">
                <h3 class="stat-number">${e.numero}</h3>
                <p class="stat-label">${e.etiqueta}</p>
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

    if (s.titulo) titleEl.innerHTML = s.titulo.replace(/\n/g, '<br>');
    if (s.descripcion) descEl.innerHTML = s.descripcion.replace(/\n/g, '<br>');

    if (identidad.badges?.length) {
        badgesEl.style.display = 'flex';
        badgesEl.style.flexWrap = 'wrap';
        badgesEl.style.justifyContent = 'center';
        badgesEl.style.gap = '20px';
        badgesEl.innerHTML = identidad.badges.map(b =>
            `<div class="badge" style="font-size: 1rem; padding: 10px 20px;">${b.texto}</div>`
        ).join('');
    }
}

function renderAbout(about) {
    if (!about.seccion) return;
    const s = about.seccion;
    const img = document.getElementById('about-img');
    if (s.imagen) img.src = s.imagen;
    if (s.titulo) document.getElementById('about-title').innerHTML = s.titulo.replace(/\n/g, '<br>');
    if (s.descripcion) document.getElementById('about-desc').textContent = s.descripcion;

    const container = document.getElementById('about-features');
    if (about.caracteristicas?.length) {
        container.innerHTML = about.caracteristicas.map(c =>
            `<div class="feature">
                <div class="feature-icon"><i class="${c.icono}"></i></div>
                <div>
                    <h4>${c.titulo}</h4>
                    <p>${c.descripcion}</p>
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
                <h3>${e.nombre}</h3>
                <p>${e.especialidad}</p>
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
                <h4>${p.nombre}</h4>
                <p>${p.descripcion}</p>
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
            <div class="timeline-time">
                <span class="time">${item.hora}</span>
                <span class="day">${item.dia}</span>
            </div>
            <div class="timeline-content">
                <h3 class="timeline-title">${item.titulo}</h3>
                <p class="timeline-chef">Por: <strong>${item.nombre_chef}</strong></p>
                <p class="timeline-desc">${item.descripcion}</p>
            </div>
        </div>`
    ).join('');

    setSubtitles('itinerary', subtitulos);
}

function renderSponsors(patrocinadores) {
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
                <span>${item.pregunta}</span>
                <i class="ph ph-caret-down"></i>
            </div>
            <div class="faq-answer">
                <p>${item.respuesta}</p>
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

function renderFooter(footer, config) {
    const container = document.getElementById('footer-container');
    if (!container) return;

    const col1 = footer?.filter(f => f.columna === '1') || [];
    const col2 = footer?.filter(f => f.columna === '2') || [];
    const col3 = footer?.filter(f => f.columna === '3') || [];

    container.innerHTML = renderFooterCol(col1, 'brand') + renderFooterCol(col2, 'links') + renderFooterCol(col3, 'contact');

    const copyright = document.getElementById('footer-copyright');
    if (copyright && config?.footer_copyright) {
        copyright.innerHTML = config.footer_copyright;
    }
}

function renderFooterCol(items, type) {
    if (!items?.length) return '';
    let className, heading, content;

    if (type === 'brand') {
        className = 'footer-brand';
        content = `<a href="#home" class="logo"><img src="img/logos/logosason.jpg" alt="Sazón Córdoba" style="max-height: 50px; border-radius: 5px;"></a>`;
        items.forEach(item => {
            if (item.tipo === 'texto') {
                content += `<p>${item.contenido || ''}</p>`;
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
        content = `<h3>${header?.titulo || 'Enlaces'}</h3><ul>`;
        items.filter(i => i.tipo === 'enlace').forEach(item => {
            content += `<li><a href="${item.url || '#'}">${item.contenido || item.titulo || 'Enlace'}</a></li>`;
        });
        content += `</ul>`;
    } else {
        className = 'footer-contact';
        const header = items.find(i => i.titulo);
        content = `<h3>${header?.titulo || 'Contacto'}</h3><ul>`;
        items.filter(i => i.tipo === 'texto').forEach(item => {
            content += `<li>${item.icono ? `<i class="${item.icono}"></i>` : ''}${item.contenido || ''}</li>`;
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
        if (parts.length > 1) {
            titleEl.innerHTML = `${parts[0]} <span class="text-gradient">${parts[1]}</span>`;
        } else {
            titleEl.innerHTML = s.titulo;
        }
    }
    if (subEl && s.subtitulo) subEl.textContent = s.subtitulo;
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

    loadSiteData();

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
