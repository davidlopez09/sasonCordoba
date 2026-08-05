-- =============================================
-- Sazón Córdoba - Mejoras solicitadas por la Cámara de Comercio de Montería
-- (secciones nuevas del RFP, todas gestionables desde el panel admin)
-- =============================================

-- 1. TABLA: Sección "Haz Parte" (texto)
CREATE TABLE IF NOT EXISTS public.seccion_participa (
    id BIGSERIAL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    imagen VARCHAR(255),
    activo BOOLEAN DEFAULT true,
    color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 2. TABLA: Botones CTA de "Haz Parte" (Quiero participar / Quiero ser expositor)
CREATE TABLE IF NOT EXISTS public.botones_participa (
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

-- 3. TABLA: Directorio de Expositores (negocios participantes, distinto de "Exponentes"/chefs)
CREATE TABLE IF NOT EXISTS public.directorio_expositores (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    categoria VARCHAR(100) NOT NULL DEFAULT 'Gastronomía',
    descripcion TEXT,
    contacto VARCHAR(255),
    logo VARCHAR(255),
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    color VARCHAR(20) NOT NULL DEFAULT '#1a1a1a',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 4. TABLA: Galería (fotos/videos de ediciones pasadas)
CREATE TABLE IF NOT EXISTS public.galeria_items (
    id BIGSERIAL PRIMARY KEY,
    tipo VARCHAR(20) NOT NULL DEFAULT 'foto',
    url VARCHAR(255) NOT NULL,
    titulo VARCHAR(255),
    edicion VARCHAR(50),
    orden INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 5. TABLA: Registros de visitantes (formulario público de Registro)
CREATE TABLE IF NOT EXISTS public.registros_visitantes (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    correo VARCHAR(255) NOT NULL,
    telefono VARCHAR(50),
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 6. TABLA: Registros de expositores interesados (formulario público de Registro)
CREATE TABLE IF NOT EXISTS public.registros_expositores (
    id BIGSERIAL PRIMARY KEY,
    nombre_empresa VARCHAR(255) NOT NULL,
    categoria VARCHAR(100) NOT NULL DEFAULT 'Gastronomía',
    nombre_contacto VARCHAR(255) NOT NULL,
    correo VARCHAR(255) NOT NULL,
    telefono VARCHAR(50),
    descripcion TEXT,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 7. TABLA: Mensajes del formulario de Contacto
CREATE TABLE IF NOT EXISTS public.mensajes_contacto (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    correo VARCHAR(255) NOT NULL,
    telefono VARCHAR(50),
    mensaje TEXT NOT NULL,
    leido BOOLEAN DEFAULT false,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- 8. TABLA: Términos y Condiciones (contenido único, largo)
CREATE TABLE IF NOT EXISTS public.terminos_condiciones (
    id BIGSERIAL PRIMARY KEY,
    contenido TEXT NOT NULL DEFAULT '',
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NOW()
);

-- =============================================
-- ALTERACIONES A TABLAS EXISTENTES
-- =============================================

-- Agenda: tipo de actividad para el filtro (taller, presentacion, conferencia, concurso, general)
ALTER TABLE public.itinerario_items ADD COLUMN IF NOT EXISTS tipo VARCHAR(30) NOT NULL DEFAULT 'general';

-- Platos Imperdibles: etiqueta destacada (ninguna, plato_dia, recomendado)
ALTER TABLE public.platillos_destacados ADD COLUMN IF NOT EXISTS etiqueta VARCHAR(20) NOT NULL DEFAULT 'ninguna';

-- =============================================
-- DATOS DE EJEMPLO (información ficticia, editable 100% desde el panel admin)
-- =============================================

INSERT INTO public.seccion_participa (titulo, descripcion, activo) VALUES
('Haz Parte de Sazón Córdoba', 'Súmate como expositor, patrocinador o aliado y sé parte del evento gastronómico más importante de Córdoba. Abrimos espacio a restaurantes, marcas de bebidas, artesanos y emprendedores que quieran mostrar lo mejor de nuestra tierra.', true)
ON CONFLICT DO NOTHING;

INSERT INTO public.botones_participa (texto, enlace, color_fondo, color_texto, color_borde, orden, activo) VALUES
('Quiero Participar', '#registro', '#ff6b00', '#ffffff', 'transparent', 0, true),
('Quiero Ser Expositor', '#registro', 'transparent', '#1a1a1a', '#1a1a1a', 1, true)
ON CONFLICT DO NOTHING;

INSERT INTO public.directorio_expositores (nombre, categoria, descripcion, contacto, orden) VALUES
('Fogón del Sinú', 'Gastronomía', 'Cocina tradicional cordobesa: mote de queso, carne en posta y sancocho de guandú.', 'fogondelsinu@example.com', 0),
('Ron Córdoba Artesanal', 'Bebidas', 'Destilería local con rones añejados y cócteles de autor a base de frutas de la región.', 'roncordoba@example.com', 1),
('Manos de Palma', 'Artesanías', 'Tejidos en palma de iraca y caña flecha elaborados por artesanos de San Andrés de Sotavento.', 'manosdepalma@example.com', 2),
('Café Alto Sinú', 'Bebidas', 'Café de origen cultivado en las estribaciones del Nudo de Paramillo, tueste artesanal.', 'cafealtosinu@example.com', 3),
('Dulces de la Abuela', 'Gastronomía', 'Repostería tradicional: alegrías, cocadas y dulce de mango biche.', 'dulcesabuela@example.com', 4)
ON CONFLICT DO NOTHING;

INSERT INTO public.galeria_items (tipo, url, titulo, edicion, orden) VALUES
('foto', 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=1200&q=80', 'Show cooking en vivo', 'Edición 2025', 0),
('foto', 'https://images.unsplash.com/photo-1414235077428-338988692140?w=1200&q=80', 'Degustación de platillos', 'Edición 2025', 1),
('foto', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1200&q=80', 'Ambiente del evento', 'Edición 2024', 2),
('foto', 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=1200&q=80', 'Chefs invitados', 'Edición 2024', 3)
ON CONFLICT DO NOTHING;

INSERT INTO public.terminos_condiciones (contenido) VALUES
('TÉRMINOS Y CONDICIONES DE USO

1. OBJETO
El presente documento regula el acceso y uso del sitio web oficial del evento Sazón Córdoba, organizado por la Cámara de Comercio de Montería.

2. TRATAMIENTO DE DATOS PERSONALES (HABEAS DATA)
De acuerdo con la Ley 1581 de 2012 y demás normas concordantes, los datos personales suministrados a través de los formularios de este sitio (registro de visitantes, registro de expositores y contacto) serán tratados con la finalidad exclusiva de gestionar la participación en el evento, y no serán compartidos con terceros sin autorización previa del titular.

3. REGLAMENTO DEL EVENTO
El ingreso al evento está sujeto a la disponibilidad de cupos y a las condiciones informadas en la sección de Registro. La organización se reserva el derecho de admisión.

4. USO DEL SITIO WEB
Este contenido es de carácter informativo. Queda prohibida la reproducción total o parcial del contenido sin autorización expresa de la Cámara de Comercio de Montería.

(Texto de ejemplo — reemplazar por el texto legal definitivo aprobado por la organización.)')
ON CONFLICT DO NOTHING;

-- Subtítulos de las secciones nuevas
INSERT INTO public.secciones_subtitulos (seccion, titulo, subtitulo) VALUES
('participa', 'Haz Parte del Evento', 'Súmate como expositor, patrocinador o aliado gastronómico.'),
('directorio', 'Directorio de Expositores', 'Descubre a los negocios y marcas que estarán presentes en la feria.'),
('galeria', 'Galería', 'Revive los mejores momentos de nuestras ediciones anteriores.'),
('registro', 'Regístrate', 'Asegura tu cupo como visitante o postúlate como expositor.'),
('contacto', 'Contáctanos', '¿Tienes preguntas? Escríbenos y te responderemos pronto.')
ON CONFLICT (seccion) DO NOTHING;

-- Configuración del evento (cuenta regresiva, horario, lugar y mapa)
INSERT INTO public.configuraciones_sitio (clave, valor) VALUES
('evento_fecha_inicio', '2026-09-11T12:00:00'),
('evento_fecha_fin', '2026-09-12T22:00:00'),
('evento_horario', 'Vie 11 y Sáb 12 de Septiembre · 12:00 PM - 10:00 PM'),
('evento_lugar', 'Centro de Eventos Montería, Cra. 6 # 25-40, Montería, Córdoba'),
('evento_mapa_embed_url', 'https://www.google.com/maps?q=Centro+de+Eventos+Monteria&output=embed'),
('mostrar_participa', '1'),
('mostrar_directorio', '1'),
('mostrar_countdown', '1'),
('mostrar_galeria', '1'),
('mostrar_registro', '1'),
('mostrar_contacto', '1'),
('social_instagram_url', 'https://instagram.com/sazoncordoba'),
('social_facebook_url', 'https://facebook.com/sazoncordoba')
ON CONFLICT (clave) DO NOTHING;

-- Video/animación introductoria opcional para "Conoce Sazón Feria" (About)
INSERT INTO public.configuraciones_sitio (clave, valor) VALUES
('about_video_url', '')
ON CONFLICT (clave) DO NOTHING;

-- Ítems de navegación para las secciones nuevas
INSERT INTO public.menu_navegacion (etiqueta, enlace, orden, activo, color) VALUES
('Haz Parte', '#participa', 6, true, '#ffffff'),
('Expositores', '#directorio', 7, true, '#ffffff'),
('Galería', '#galeria', 8, true, '#ffffff'),
('Registro', '#registro', 9, true, '#ffffff'),
('Contacto', '#contacto', 10, true, '#ffffff')
ON CONFLICT DO NOTHING;

-- Enlace a Términos y Condiciones en el footer (columna 2, junto a "Acerca de")
INSERT INTO public.pie_pagina (tipo, titulo, contenido, url, icono, columna, orden) VALUES
('enlace', NULL, 'Términos y Condiciones', 'terminos.html', NULL, '2', 1)
ON CONFLICT DO NOTHING;
