-- =============================================
-- Sazón Córdoba - Schema completo para Supabase
-- =============================================

-- 1. TABLA: Usuarios (para el login del panel admin)
CREATE TABLE IF NOT EXISTS public.usuarios (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    correo VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP(0) WITHOUT TIME ZONE,
    contrasena VARCHAR(255) NOT NULL,
    token_recordar VARCHAR(100),
    rol VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Insertar usuario admin (contraseña: sazon2026)
INSERT INTO public.usuarios (nombre, correo, contrasena, rol) VALUES
('Administrador', 'admin@sazoncordoba.com', '$2y$10$e2nQfm7orYPgm2W3n6U9Jusm4eDzKx6sKt0x090Kzl/.1/ktnWK7W', 'admin')
ON CONFLICT (correo) DO NOTHING;

-- 2. TABLA: Banner Principal
CREATE TABLE IF NOT EXISTS public.banner_principal (
    id BIGSERIAL PRIMARY KEY,
    imagen VARCHAR(255) NOT NULL,
    activo BOOLEAN DEFAULT true,
    orden INTEGER DEFAULT 0,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Migración para bases ya existentes: el texto del hero pasa a ser único (tabla hero_texto),
-- ya no varía por slide.
ALTER TABLE public.banner_principal DROP COLUMN IF EXISTS texto_badge;
ALTER TABLE public.banner_principal DROP COLUMN IF EXISTS titulo;
ALTER TABLE public.banner_principal DROP COLUMN IF EXISTS subtitulo;

-- 2a. TABLA: Texto único del Hero (badge/título/subtítulo, no cambia por slide)
CREATE TABLE IF NOT EXISTS public.hero_texto (
    id BIGSERIAL PRIMARY KEY,
    texto_badge VARCHAR(255),
    titulo VARCHAR(255),
    subtitulo TEXT,
    color VARCHAR(20) NOT NULL DEFAULT '#ffffff',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde hero_texto se creó sin la columna `color`
ALTER TABLE public.hero_texto ADD COLUMN IF NOT EXISTS color VARCHAR(20) NOT NULL DEFAULT '#ffffff';

-- 2c. TABLA: Botones del Hero (agregar/editar/eliminar, colores)
CREATE TABLE IF NOT EXISTS public.botones_hero (
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

-- 3. TABLA: Estadísticas Principales
CREATE TABLE IF NOT EXISTS public.estadisticas_principales (
    id BIGSERIAL PRIMARY KEY,
    numero VARCHAR(255) NOT NULL,
    etiqueta VARCHAR(255) NOT NULL,
    icono VARCHAR(255),
    orden INTEGER DEFAULT 0,
    color VARCHAR(20) NOT NULL DEFAULT '#ffffff',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde estadisticas_principales se creó sin la columna `color`
ALTER TABLE public.estadisticas_principales ADD COLUMN IF NOT EXISTS color VARCHAR(20) NOT NULL DEFAULT '#ffffff';

-- 4. TABLA: Secciones About
CREATE TABLE IF NOT EXISTS public.secciones_about (
    id BIGSERIAL PRIMARY KEY,
    imagen VARCHAR(255),
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde secciones_about se creó sin la columna `color`
ALTER TABLE public.secciones_about ADD COLUMN IF NOT EXISTS color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a';

-- 5. TABLA: Características About
CREATE TABLE IF NOT EXISTS public.caracteristicas_about (
    id BIGSERIAL PRIMARY KEY,
    icono VARCHAR(255) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    orden INTEGER DEFAULT 0,
    color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde caracteristicas_about se creó sin la columna `color`
ALTER TABLE public.caracteristicas_about ADD COLUMN IF NOT EXISTS color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a';

-- 6. TABLA: Exponentes
CREATE TABLE IF NOT EXISTS public.exponentes (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    especialidad VARCHAR(255) NOT NULL,
    foto VARCHAR(255),
    instagram_url VARCHAR(255),
    twitter_url VARCHAR(255),
    orden INTEGER DEFAULT 0,
    color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde exponentes se creó sin la columna `color`
ALTER TABLE public.exponentes ADD COLUMN IF NOT EXISTS color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a';

-- 7. TABLA: Platillos Destacados
CREATE TABLE IF NOT EXISTS public.platillos_destacados (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    imagen VARCHAR(255),
    orden INTEGER DEFAULT 0,
    color VARCHAR(20) NOT NULL DEFAULT '#ffffff',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde platillos_destacados se creó sin la columna `color`
ALTER TABLE public.platillos_destacados ADD COLUMN IF NOT EXISTS color VARCHAR(20) NOT NULL DEFAULT '#ffffff';

-- 8. TABLA: Itinerario Items
CREATE TABLE IF NOT EXISTS public.itinerario_items (
    id BIGSERIAL PRIMARY KEY,
    hora VARCHAR(255) NOT NULL,
    dia VARCHAR(255) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    nombre_chef VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    orden INTEGER DEFAULT 0,
    color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a',
    color_fondo VARCHAR(40) NOT NULL DEFAULT '#ffffff',
    color_borde VARCHAR(40) NOT NULL DEFAULT 'rgba(0,0,0,0.08)',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- Backfill para bases ya existentes donde itinerario_items se creó sin las columnas de color
ALTER TABLE public.itinerario_items ADD COLUMN IF NOT EXISTS color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a';
ALTER TABLE public.itinerario_items ADD COLUMN IF NOT EXISTS color_fondo VARCHAR(40) NOT NULL DEFAULT '#ffffff';
ALTER TABLE public.itinerario_items ADD COLUMN IF NOT EXISTS color_borde VARCHAR(40) NOT NULL DEFAULT 'rgba(0,0,0,0.08)';

-- 9. TABLA: Patrocinadores
CREATE TABLE IF NOT EXISTS public.patrocinadores (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    logo VARCHAR(255),
    url VARCHAR(255),
    orden INTEGER DEFAULT 0,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 10. TABLA: Configuraciones Sitio
CREATE TABLE IF NOT EXISTS public.configuraciones_sitio (
    id BIGSERIAL PRIMARY KEY,
    clave VARCHAR(255) NOT NULL UNIQUE,
    valor TEXT,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- =============================================
-- DATOS DE EJEMPLO
-- =============================================

INSERT INTO public.banner_principal (imagen, activo, orden) VALUES
('https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=1920&q=80', true, 0),
('https://images.unsplash.com/photo-1414235077428-338988692140?w=1920&q=80', true, 1),
('https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1920&q=80', true, 2)
ON CONFLICT DO NOTHING;

INSERT INTO public.hero_texto (texto_badge, titulo, subtitulo) VALUES
('Edición 2026', 'El Sabor que Enciende a Montería', 'Descubre el evento culinario más prestigioso de la región.')
ON CONFLICT DO NOTHING;

INSERT INTO public.botones_hero (texto, enlace, color_fondo, color_texto, color_borde, orden, activo) VALUES
('Ver Menú', '#itinerary', '#ff6b00', '#ffffff', 'transparent', 1, true),
('Descubre Más', '#about', 'transparent', '#ffffff', '#ffffff', 2, true)
ON CONFLICT DO NOTHING;

INSERT INTO public.estadisticas_principales (numero, etiqueta, orden) VALUES
('15+', 'Chefs Invitados', 0),
('40+', 'Platillos Únicos', 1),
('3', 'Días de Sabor', 2)
ON CONFLICT DO NOTHING;

INSERT INTO public.secciones_about (imagen, titulo, descripcion) VALUES
('https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80', 'Una Experiencia Inolvidable', 'Sazón Córdoba es más que una feria gastronómica; es un tributo a nuestras raíces y una ventana a la cocina vanguardista.')
ON CONFLICT DO NOTHING;

INSERT INTO public.caracteristicas_about (icono, titulo, descripcion, orden) VALUES
('ph ph-cooking-pot', 'Alta Cocina', 'Degustaciones de platillos de autor preparados en vivo.', 0),
('ph ph-martini', 'Maridaje Perfecto', 'Bebidas y licores seleccionados por sommeliers expertos.', 1)
ON CONFLICT DO NOTHING;

INSERT INTO public.exponentes (nombre, especialidad, foto, instagram_url, twitter_url, orden) VALUES
('Roberto Salgado', 'Especialista en Asados', 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=600&q=80', '#', '#', 0),
('Camila Vargas', 'Fusión Contemporánea', 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=600&q=80', '#', '#', 1),
('Diego Montes', 'Cocina de Autor', 'https://images.unsplash.com/photo-1600565193348-f74bd3c7ccdf?w=600&q=80', '#', '#', 2)
ON CONFLICT DO NOTHING;

INSERT INTO public.platillos_destacados (nombre, descripcion, imagen, orden) VALUES
('Costillas al Ahumador', 'Salsa BBQ artesanal y especias secretas.', 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&q=80', 0),
('Risotto de Mariscos', 'Mariscos frescos y azafrán importado.', 'https://images.unsplash.com/photo-1560684352-8497838a2229?w=600&q=80', 1),
('Pasta Trufada', 'Pasta artesanal con crema de trufa negra.', 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?w=600&q=80', 2),
('Pizza Gourmet', 'Masa madre, prosciutto y rúcula fresca.', 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&q=80', 3)
ON CONFLICT DO NOTHING;

INSERT INTO public.itinerario_items (hora, dia, titulo, nombre_chef, descripcion, orden) VALUES
('12:00 PM', 'Sábado', 'Apertura y Aperitivos', 'Chef Diego Montes', 'Inauguración del evento con una selección de tapas frías, quesos madurados y maridaje con vinos blancos.', 0),
('03:30 PM', 'Sábado', 'Show Cooking: Carnes y Fuego', 'Chef Roberto Salgado', 'Demostración en vivo de técnicas de asado y ahumado.', 1),
('07:00 PM', 'Sábado', 'Cena de Gala y Fusión', 'Chef Camila Vargas', 'Un recorrido de 3 tiempos destacando ingredientes locales.', 2)
ON CONFLICT DO NOTHING;

INSERT INTO public.patrocinadores (nombre, logo, orden) VALUES
('Cámara de Comercio de Montería', 'img/logos/logocaramadecomercio.png', 0),
('Sazón Córdoba', 'img/logos/logosason.jpg', 1)
ON CONFLICT DO NOTHING;

-- 11. TABLA: Sección Identidad
CREATE TABLE IF NOT EXISTS public.seccion_identidad (
    id BIGSERIAL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    activo BOOLEAN DEFAULT true,
    color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 12. TABLA: Badges Identidad
CREATE TABLE IF NOT EXISTS public.badges_identidad (
    id BIGSERIAL PRIMARY KEY,
    texto VARCHAR(255) NOT NULL,
    orden INTEGER DEFAULT 0,
    color VARCHAR(20) NOT NULL DEFAULT '#ff6b00',
    color_fondo VARCHAR(40) NOT NULL DEFAULT 'rgba(255, 107, 0, 0.15)',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 13a. TABLA: Logos del Nav (biblioteca, uno activo a la vez)
CREATE TABLE IF NOT EXISTS public.logos_nav (
    id BIGSERIAL PRIMARY KEY,
    logo VARCHAR(500) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 13b. TABLA: Botones del Nav (agregar/editar/eliminar, colores)
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

-- 13. TABLA: Menú de Navegación
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

-- 14. TABLA: Preguntas Frecuentes
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

-- 15. TABLA: Pie de Página
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

-- 16. TABLA: Subtítulos de Secciones
CREATE TABLE IF NOT EXISTS public.secciones_subtitulos (
    id BIGSERIAL PRIMARY KEY,
    seccion VARCHAR(100) NOT NULL UNIQUE,
    titulo VARCHAR(255),
    subtitulo TEXT,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- DATOS DE EJEMPLO - Nuevas tablas
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
('faq', 'Preguntas Frecuentes', 'Resuelve tus dudas sobre el evento Sazón Córdoba.')
ON CONFLICT (seccion) DO NOTHING;

INSERT INTO public.configuraciones_sitio (clave, valor) VALUES
('footer_copyright', '&copy; 2026 Cámara de Comercio de Montería. Todos los derechos reservados.'),
('color_nav_fondo', '#000000'),
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

INSERT INTO public.logos_nav (logo, activo) VALUES
('img/logos/logosason.jpg', true)
ON CONFLICT DO NOTHING;

INSERT INTO public.botones_nav (texto, enlace, color_fondo, color_texto, color_borde, orden, activo) VALUES
('Reservar Ahora', '#itinerary', '#ff6b00', '#ffffff', 'transparent', 0, true),
('Ingresar', 'api/admin/login.php', 'transparent', '#ffffff', '#ff6b00', 1, true)
ON CONFLICT DO NOTHING;

-- 17. TABLA: Secciones Dinámicas (constructor de secciones por bloques)
CREATE TABLE IF NOT EXISTS public.secciones_dinamicas (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    insertar_despues VARCHAR(50) NOT NULL,
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 18. TABLA: Bloques Dinámicos (contenido de cada sección dinámica)
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
