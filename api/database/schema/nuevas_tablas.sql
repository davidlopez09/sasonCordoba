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

-- 3. TABLA: Menú de Navegación
CREATE TABLE IF NOT EXISTS public.menu_navegacion (
    id BIGSERIAL PRIMARY KEY,
    etiqueta VARCHAR(255) NOT NULL,
    enlace VARCHAR(255) NOT NULL,
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 4. TABLA: Preguntas Frecuentes
CREATE TABLE IF NOT EXISTS public.preguntas_frecuentes (
    id BIGSERIAL PRIMARY KEY,
    pregunta TEXT NOT NULL,
    respuesta TEXT NOT NULL,
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

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
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 6. TABLA: Subtítulos de Secciones
CREATE TABLE IF NOT EXISTS public.secciones_subtitulos (
    id BIGSERIAL PRIMARY KEY,
    seccion VARCHAR(100) NOT NULL UNIQUE,
    titulo VARCHAR(255),
    subtitulo TEXT,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

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

INSERT INTO public.menu_navegacion (etiqueta, enlace, orden, activo) VALUES
('Inicio', '#home', 0, true),
('Identidad', '#identity', 1, true),
('El Evento', '#about', 2, true),
('Invitados', '#chefs', 3, true),
('Platillos', '#dishes', 4, true),
('Itinerario', '#itinerary', 5, true)
ON CONFLICT DO NOTHING;

INSERT INTO public.preguntas_frecuentes (pregunta, respuesta, orden, activo) VALUES
('¿Dónde puedo comprar mis entradas?', 'Las entradas estarán disponibles en las taquillas del evento o pueden adquirirse de forma anticipada a través de la página web de la Cámara de Comercio de Montería y puntos de venta autorizados.', 0, true),
('¿El evento es apto para niños?', '¡Por supuesto! Sazón Córdoba es un evento familiar. Contaremos con zonas especiales y platillos pensados para los más pequeños, además de un ambiente seguro y agradable.', 1, true),
('¿Habrá opciones vegetarianas o veganas?', 'Sí. Varios de nuestros restaurantes y chefs invitados tendrán opciones vegetarianas, veganas y libres de gluten para garantizar que todos disfruten de la experiencia gastronómica.', 2, true),
('¿Cuentan con parqueadero disponible?', 'El centro de eventos dispone de un amplio parqueadero vigilado para los asistentes. Recomendamos llegar con anticipación para asegurar tu lugar o utilizar servicios de transporte.', 3, true)
ON CONFLICT DO NOTHING;

INSERT INTO public.pie_pagina (tipo, titulo, contenido, url, icono, columna, orden) VALUES
('texto', 'Sazón Córdoba', 'Una experiencia gastronómica sin igual en el corazón de Montería.', NULL, NULL, '1', 0),
('red_social', NULL, NULL, '#', 'ph-facebook-logo', '1', 1),
('red_social', NULL, NULL, '#', 'ph-instagram-logo', '1', 2),
('red_social', NULL, NULL, '#', 'ph-youtube-logo', '1', 3),
('enlace', 'Acerca de', NULL, '#about', NULL, '2', 0),
('enlace', 'Invitados', NULL, '#chefs', NULL, '2', 1),
('enlace', 'Platillos', NULL, '#dishes', NULL, '2', 2),
('enlace', 'Agenda', NULL, '#itinerary', NULL, '2', 3),
('texto', 'Contacto', 'Centro de Eventos, Montería', NULL, 'ph-map-pin', '3', 0),
('texto', 'Contacto', 'info@sazoncordoba.com', NULL, 'ph-envelope-simple', '3', 1),
('texto', 'Contacto', '+57 300 123 4567', NULL, 'ph-phone', '3', 2)
ON CONFLICT DO NOTHING;

INSERT INTO public.secciones_subtitulos (seccion, titulo, subtitulo) VALUES
('chefs', 'Exponentes Especiales', 'Los maestros culinarios que darán vida a los sabores más intensos de la noche.'),
('dishes', 'Platillos Destacados', 'Una mirada a las delicias que te esperan. Prepárate para enamorar tu paladar.'),
('itinerary', 'Itinerario y Menú', 'Planifica tu visita y no te pierdas ninguna degustación.'),
('faq', 'Preguntas Frecuentes', 'Resuelve tus dudas sobre el evento Sazón Córdoba.')
ON CONFLICT (seccion) DO NOTHING;

-- Agregar configuración adicional
INSERT INTO public.configuraciones_sitio (clave, valor) VALUES
('reservar_url', '#itinerary'),
('reservar_texto', 'Reservar Ahora'),
('footer_copyright', '&copy; 2026 Cámara de Comercio de Montería. Todos los derechos reservados.'),
('logo_nav', 'img/logos/logosason.jpg'),
('color_nav_texto', '#5a6066')
ON CONFLICT (clave) DO NOTHING;
