-- =============================================
-- Nuevas tablas para control total desde el admin
-- =============================================

-- 1. TABLA: Sección Identidad
CREATE TABLE IF NOT EXISTS public.seccion_identidad (
    id BIGSERIAL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 2. TABLA: Badges Identidad
CREATE TABLE IF NOT EXISTS public.badges_identidad (
    id BIGSERIAL PRIMARY KEY,
    texto VARCHAR(255) NOT NULL,
    orden INTEGER DEFAULT 0,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill de la columna `color` para seccion_identidad y badges_identidad
ALTER TABLE public.seccion_identidad ADD COLUMN IF NOT EXISTS color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a';
ALTER TABLE public.badges_identidad ADD COLUMN IF NOT EXISTS color VARCHAR(20) NOT NULL DEFAULT '#ff6b00';
ALTER TABLE public.badges_identidad ADD COLUMN IF NOT EXISTS color_fondo VARCHAR(40) NOT NULL DEFAULT 'rgba(255, 107, 0, 0.15)';

-- 2a. TABLA: Logos del Nav (biblioteca, uno activo a la vez)
CREATE TABLE IF NOT EXISTS public.logos_nav (
    id BIGSERIAL PRIMARY KEY,
    logo VARCHAR(500) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 2b. TABLA: Botones del Nav (agregar/editar/eliminar, colores)
CREATE TABLE IF NOT EXISTS public.botones_nav (
    id BIGSERIAL PRIMARY KEY,
    texto VARCHAR(100) NOT NULL,
    enlace VARCHAR(255) NOT NULL,
    color_fondo VARCHAR(20) NOT NULL DEFAULT '#ff6b00',
    color_texto VARCHAR(20) NOT NULL DEFAULT '#ffffff',
    color_borde VARCHAR(20) NOT NULL DEFAULT 'transparent',
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde la tabla se creó sin la columna `color_borde`
ALTER TABLE public.botones_nav
    ADD COLUMN IF NOT EXISTS color_borde VARCHAR(20) NOT NULL DEFAULT 'transparent';

-- 3. TABLA: Menú de Navegación
CREATE TABLE IF NOT EXISTS public.menu_navegacion (
    id BIGSERIAL PRIMARY KEY,
    etiqueta VARCHAR(255) NOT NULL,
    enlace VARCHAR(255) NOT NULL,
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    color VARCHAR(7) NOT NULL DEFAULT '#ffffff',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde la tabla se creó sin la columna `color`
ALTER TABLE public.menu_navegacion
    ADD COLUMN IF NOT EXISTS color VARCHAR(7) NOT NULL DEFAULT '#ffffff';

-- 4. TABLA: Preguntas Frecuentes
CREATE TABLE IF NOT EXISTS public.preguntas_frecuentes (
    id BIGSERIAL PRIMARY KEY,
    pregunta TEXT NOT NULL,
    respuesta TEXT NOT NULL,
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde preguntas_frecuentes se creó sin la columna `color`
ALTER TABLE public.preguntas_frecuentes ADD COLUMN IF NOT EXISTS color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a';

-- 5. TABLA: Pie de Página
CREATE TABLE IF NOT EXISTS public.pie_pagina (
    id BIGSERIAL PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL DEFAULT 'texto',
    titulo VARCHAR(255),
    contenido TEXT,
    url VARCHAR(255),
    icono VARCHAR(255),
    columna VARCHAR(50) NOT NULL DEFAULT '1',
    orden INTEGER DEFAULT 0,
    color VARCHAR(20) NOT NULL DEFAULT '',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde pie_pagina se creó sin la columna `color`
ALTER TABLE public.pie_pagina ADD COLUMN IF NOT EXISTS color VARCHAR(20) NOT NULL DEFAULT '';

-- 6. TABLA: Subtítulos de Secciones
CREATE TABLE IF NOT EXISTS public.secciones_subtitulos (
    id BIGSERIAL PRIMARY KEY,
    seccion VARCHAR(100) NOT NULL UNIQUE,
    titulo VARCHAR(255),
    subtitulo TEXT,
    color VARCHAR(20) NOT NULL DEFAULT '',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde secciones_subtitulos se creó sin la columna `color`
ALTER TABLE public.secciones_subtitulos ADD COLUMN IF NOT EXISTS color VARCHAR(20) NOT NULL DEFAULT '';

-- =============================================
-- DATOS DE EJEMPLO
-- =============================================

INSERT INTO public.seccion_identidad (titulo, descripcion, activo) VALUES
('El sabor de nuestra tierra ahora tiene identidad propia', 'Celebramos que Sazón Córdoba es oficialmente una marca registrada, un sello que reconoce, protege y proyecta lo mejor de nuestra gastronomía. No es solo un nombre… es una apuesta por el talento local, por nuestros restaurantes, cocineros y emprendedores que día a día le ponen alma a cada plato.', true)
ON CONFLICT DO NOTHING;

INSERT INTO public.badges_identidad (texto, orden) VALUES
('Negocios gastronómicos', 0),
('Formación y profesionalización', 1),
('Eventos y turismo', 2),
('Crecimiento empresarial', 3)
ON CONFLICT DO NOTHING;

INSERT INTO public.menu_navegacion (etiqueta, enlace, orden, activo, color) VALUES
('Inicio', '#home', 0, true, '#ffffff'),
('Identidad', '#identity', 1, true, '#ffffff'),
('El Evento', '#about', 2, true, '#ffffff'),
('Invitados', '#chefs', 3, true, '#ffffff'),
('Platillos', '#dishes', 4, true, '#ffffff'),
('Itinerario', '#itinerary', 5, true, '#ffffff')
ON CONFLICT DO NOTHING;

INSERT INTO public.preguntas_frecuentes (pregunta, respuesta, orden, activo) VALUES
('¿Dónde puedo comprar mis entradas?', 'Las entradas estarán disponibles en las taquillas del evento o pueden adquirirse de forma anticipada a través de la página web de la Cámara de Comercio de Montería y puntos de venta autorizados.', 0, true),
('¿El evento es apto para niños?', '¡Por supuesto! Sazón Córdoba es un evento familiar. Contaremos con zonas especiales y platillos pensados para los más pequeños, además de un ambiente seguro y agradable.', 1, true),
('¿Habrá opciones vegetarianas o veganas?', 'Sí. Varios de nuestros restaurantes y chefs invitados tendrán opciones vegetarianas, veganas y libres de gluten para garantizar que todos disfruten de la experiencia gastronómica.', 2, true),
('¿Cuentan con parqueadero disponible?', 'El centro de eventos dispone de un amplio parqueadero vigilado para los asistentes. Recomendamos llegar con anticipación para asegurar tu lugar o utilizar servicios de transporte.', 3, true)
ON CONFLICT DO NOTHING;

-- Nota: la columna 2 ("Acerca de") ya no guarda los enlaces individuales acá — esos
-- se toman en vivo de menu_navegacion (el mismo menú del nav). Solo se guarda el
-- encabezado de la columna.
INSERT INTO public.pie_pagina (tipo, titulo, contenido, url, icono, columna, orden) VALUES
('texto', 'Sazón Córdoba', 'Una experiencia gastronómica sin igual en el corazón de Montería.', NULL, NULL, '1', 0),
('red_social', NULL, NULL, '#', 'ph-facebook-logo', '1', 1),
('red_social', NULL, NULL, '#', 'ph-instagram-logo', '1', 2),
('red_social', NULL, NULL, '#', 'ph-youtube-logo', '1', 3),
('enlace', 'Acerca de', NULL, '#about', NULL, '2', 0),
('texto', 'Contacto', 'Centro de Eventos, Montería', NULL, 'ph-map-pin', '3', 0),
('texto', 'Contacto', 'info@sazoncordoba.com', NULL, 'ph-envelope-simple', '3', 1),
('texto', 'Contacto', '+57 300 123 4567', NULL, 'ph-phone', '3', 2)
ON CONFLICT DO NOTHING;

INSERT INTO public.secciones_subtitulos (seccion, titulo, subtitulo) VALUES
('chefs', 'Exponentes Especiales', 'Los maestros culinarios que darán vida a los sabores más intensos de la noche.'),
('dishes', 'Platillos Destacados', 'Una mirada a las delicias que te esperan. Prepárate para enamorar tu paladar.'),
('itinerary', 'Itinerario y Menú', 'Planifica tu visita y no te pierdas ninguna degustación.'),
('faq', 'Preguntas Frecuentes', 'Resuelve tus dudas sobre el evento Sazón Córdoba.'),
('sponsors', 'Nuestros Patrocinadores', 'Haciendo posible el mejor evento de la ciudad.')
ON CONFLICT (seccion) DO NOTHING;

-- Agregar configuración adicional
INSERT INTO public.configuraciones_sitio (clave, valor) VALUES
('footer_copyright', '&copy; 2026 Cámara de Comercio de Montería. Todos los derechos reservados.'),
('color_footer_fondo', '#020202'),
('color_footer_texto', ''),
('mostrar_identidad', '1'),
('mostrar_about', '1'),
('mostrar_chefs', '1'),
('mostrar_platillos', '1'),
('mostrar_itinerario', '1'),
('mostrar_sponsors', '1'),
('mostrar_faq', '1')
ON CONFLICT (clave) DO NOTHING;

INSERT INTO public.botones_nav (texto, enlace, color_fondo, color_texto, color_borde, orden, activo) VALUES
('Reservar Ahora', '#itinerary', '#ff6b00', '#ffffff', 'transparent', 0, true),
('Ingresar', 'api/admin/login.php', 'transparent', '#ffffff', '#ff6b00', 1, true)
ON CONFLICT DO NOTHING;

-- TABLA: Secciones Dinámicas (constructor de secciones por bloques)
CREATE TABLE IF NOT EXISTS public.secciones_dinamicas (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    insertar_despues VARCHAR(50) NOT NULL,
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- TABLA: Bloques Dinámicos (contenido de cada sección dinámica)
CREATE TABLE IF NOT EXISTS public.bloques_dinamicos (
    id BIGSERIAL PRIMARY KEY,
    seccion_id BIGINT NOT NULL REFERENCES secciones_dinamicas(id) ON DELETE CASCADE,
    tipo VARCHAR(30) NOT NULL,
    posicion VARCHAR(20) NOT NULL DEFAULT 'completo',
    contenido TEXT NOT NULL,
    orden INTEGER DEFAULT 0,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);
