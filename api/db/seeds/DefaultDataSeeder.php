<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class DefaultDataSeeder extends AbstractSeed
{
    public function run(): void
    {
        // Limpiar tablas para evitar duplicados si se corre varias veces
        $this->table('terminos_condiciones')->truncate();
        $this->table('galeria_items')->truncate();
        $this->table('directorio_expositores')->truncate();
        $this->table('botones_participa')->truncate();
        $this->table('seccion_participa')->truncate();
        $this->table('botones_nav')->truncate();
        $this->table('logos_nav')->truncate();
        $this->table('configuraciones_sitio')->truncate();
        $this->table('secciones_subtitulos')->truncate();
        $this->table('pie_pagina')->truncate();
        $this->table('preguntas_frecuentes')->truncate();
        $this->table('menu_navegacion')->truncate();
        $this->table('badges_identidad')->truncate();
        $this->table('seccion_identidad')->truncate();
        $this->table('patrocinadores')->truncate();
        $this->table('itinerario_items')->truncate();
        $this->table('platillos_destacados')->truncate();
        $this->table('exponentes')->truncate();
        $this->table('caracteristicas_about')->truncate();
        $this->table('secciones_about')->truncate();
        $this->table('estadisticas_principales')->truncate();
        $this->table('botones_hero')->truncate();
        $this->table('hero_texto')->truncate();
        $this->table('banner_principal')->truncate();
        $this->table('usuarios')->truncate();

        // 1. Usuarios
        $this->table('usuarios')->insert([
            [
                'nombre' => 'Administrador',
                'correo' => 'joseangel191134@gmail.com',
                'contrasena' => '$2a$10$j34DBvDQ93bicmKQwOyAv.VhVCo2h9V6bY9B5SybOlHwdq8fHBO32',
                'rol' => 'admin'
            ]
        ])->saveData();

        // 2. Banner Principal
        $this->table('banner_principal')->insert([
            ['imagen' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=1920&q=80', 'activo' => true, 'orden' => 0],
            ['imagen' => 'https://images.unsplash.com/photo-1414235077428-338988692140?w=1920&q=80', 'activo' => true, 'orden' => 1],
            ['imagen' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1920&q=80', 'activo' => true, 'orden' => 2]
        ])->saveData();

        // 3. Hero Texto
        $this->table('hero_texto')->insert([
            ['texto_badge' => 'Edición 2026', 'titulo' => 'El Sabor que Enciende a Montería', 'subtitulo' => 'Descubre el evento culinario más prestigioso de la región.']
        ])->saveData();

        // 4. Botones Hero
        $this->table('botones_hero')->insert([
            ['texto' => 'Ver Menú', 'enlace' => '#itinerary', 'color_fondo' => '#ff6b00', 'color_texto' => '#ffffff', 'color_borde' => 'transparent', 'orden' => 1, 'activo' => true],
            ['texto' => 'Descubre Más', 'enlace' => '#about', 'color_fondo' => 'transparent', 'color_texto' => '#ffffff', 'color_borde' => '#ffffff', 'orden' => 2, 'activo' => true]
        ])->saveData();

        // 5. Estadisticas Principales
        $this->table('estadisticas_principales')->insert([
            ['numero' => '15+', 'etiqueta' => 'Chefs Invitados', 'orden' => 0],
            ['numero' => '40+', 'etiqueta' => 'Platillos Únicos', 'orden' => 1],
            ['numero' => '3', 'etiqueta' => 'Días de Sabor', 'orden' => 2]
        ])->saveData();

        // 6. Secciones About
        $this->table('secciones_about')->insert([
            ['imagen' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80', 'titulo' => 'Una Experiencia Inolvidable', 'descripcion' => 'Sazón Córdoba es más que una feria gastronómica; es un tributo a nuestras raíces y una ventana a la cocina vanguardista.']
        ])->saveData();

        // 7. Caracteristicas About
        $this->table('caracteristicas_about')->insert([
            ['icono' => 'ph ph-cooking-pot', 'titulo' => 'Alta Cocina', 'descripcion' => 'Degustaciones de platillos de autor preparados en vivo.', 'orden' => 0],
            ['icono' => 'ph ph-martini', 'titulo' => 'Maridaje Perfecto', 'descripcion' => 'Bebidas y licores seleccionados por sommeliers expertos.', 'orden' => 1]
        ])->saveData();

        // 8. Exponentes
        $this->table('exponentes')->insert([
            ['nombre' => 'Roberto Salgado', 'especialidad' => 'Especialista en Asados', 'foto' => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=600&q=80', 'instagram_url' => '#', 'twitter_url' => '#', 'orden' => 0],
            ['nombre' => 'Camila Vargas', 'especialidad' => 'Fusión Contemporánea', 'foto' => 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=600&q=80', 'instagram_url' => '#', 'twitter_url' => '#', 'orden' => 1],
            ['nombre' => 'Diego Montes', 'especialidad' => 'Cocina de Autor', 'foto' => 'https://images.unsplash.com/photo-1600565193348-f74bd3c7ccdf?w=600&q=80', 'instagram_url' => '#', 'twitter_url' => '#', 'orden' => 2]
        ])->saveData();

        // 9. Platillos Destacados
        $this->table('platillos_destacados')->insert([
            ['nombre' => 'Costillas al Ahumador', 'descripcion' => 'Salsa BBQ artesanal y especias secretas.', 'imagen' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&q=80', 'orden' => 0],
            ['nombre' => 'Risotto de Mariscos', 'descripcion' => 'Mariscos frescos y azafrán importado.', 'imagen' => 'https://images.unsplash.com/photo-1560684352-8497838a2229?w=600&q=80', 'orden' => 1],
            ['nombre' => 'Pasta Trufada', 'descripcion' => 'Pasta artesanal con crema de trufa negra.', 'imagen' => 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?w=600&q=80', 'orden' => 2],
            ['nombre' => 'Pizza Gourmet', 'descripcion' => 'Masa madre, prosciutto y rúcula fresca.', 'imagen' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&q=80', 'orden' => 3]
        ])->saveData();

        // 10. Itinerario Items
        $this->table('itinerario_items')->insert([
            ['hora' => '12:00 PM', 'dia' => 'Sábado', 'titulo' => 'Apertura y Aperitivos', 'nombre_chef' => 'Chef Diego Montes', 'descripcion' => 'Inauguración del evento con una selección de tapas frías, quesos madurados y maridaje con vinos blancos.', 'orden' => 0],
            ['hora' => '03:30 PM', 'dia' => 'Sábado', 'titulo' => 'Show Cooking: Carnes y Fuego', 'nombre_chef' => 'Chef Roberto Salgado', 'descripcion' => 'Demostración en vivo de técnicas de asado y ahumado.', 'orden' => 1],
            ['hora' => '07:00 PM', 'dia' => 'Sábado', 'titulo' => 'Cena de Gala y Fusión', 'nombre_chef' => 'Chef Camila Vargas', 'descripcion' => 'Un recorrido de 3 tiempos destacando ingredientes locales.', 'orden' => 2]
        ])->saveData();

        // 11. Patrocinadores
        $this->table('patrocinadores')->insert([
            ['nombre' => 'Cámara de Comercio de Montería', 'logo' => 'img/logos/logocaramadecomercio.png', 'orden' => 0],
            ['nombre' => 'Sazón Córdoba', 'logo' => 'img/logos/logosason.jpg', 'orden' => 1]
        ])->saveData();

        // 12. Seccion Identidad
        $this->table('seccion_identidad')->insert([
            ['titulo' => 'El sabor de nuestra tierra ahora tiene identidad propia', 'descripcion' => 'Celebramos que Sazón Córdoba es oficialmente una marca registrada, un sello que reconoce, protege y proyecta lo mejor de nuestra gastronomía. No es solo un nombre… es una apuesta por el talento local, por nuestros restaurantes, cocineros y emprendedores que día a día le ponen alma a cada plato.', 'activo' => true]
        ])->saveData();

        // 13. Badges Identidad
        $this->table('badges_identidad')->insert([
            ['texto' => 'Negocios gastronómicos', 'orden' => 0],
            ['texto' => 'Formación y profesionalización', 'orden' => 1],
            ['texto' => 'Eventos y turismo', 'orden' => 2],
            ['texto' => 'Crecimiento empresarial', 'orden' => 3]
        ])->saveData();

        // 14. Menu Navegacion
        $this->table('menu_navegacion')->insert([
            ['etiqueta' => 'Inicio', 'enlace' => '#home', 'orden' => 0, 'activo' => true, 'color' => '#ffffff'],
            ['etiqueta' => 'Identidad', 'enlace' => '#identity', 'orden' => 1, 'activo' => true, 'color' => '#ffffff'],
            ['etiqueta' => 'El Evento', 'enlace' => '#about', 'orden' => 2, 'activo' => true, 'color' => '#ffffff'],
            ['etiqueta' => 'Invitados', 'enlace' => '#chefs', 'orden' => 3, 'activo' => true, 'color' => '#ffffff'],
            ['etiqueta' => 'Platillos', 'enlace' => '#dishes', 'orden' => 4, 'activo' => true, 'color' => '#ffffff'],
            ['etiqueta' => 'Itinerario', 'enlace' => '#itinerary', 'orden' => 5, 'activo' => true, 'color' => '#ffffff'],
            ['etiqueta' => 'Haz Parte', 'enlace' => '#participa', 'orden' => 6, 'activo' => true, 'color' => '#ffffff'],
            ['etiqueta' => 'Expositores', 'enlace' => '#directorio', 'orden' => 7, 'activo' => true, 'color' => '#ffffff'],
            ['etiqueta' => 'Galería', 'enlace' => '#galeria', 'orden' => 8, 'activo' => true, 'color' => '#ffffff'],
            ['etiqueta' => 'Registro', 'enlace' => '#registro', 'orden' => 9, 'activo' => true, 'color' => '#ffffff'],
            ['etiqueta' => 'Contacto', 'enlace' => '#contacto', 'orden' => 10, 'activo' => true, 'color' => '#ffffff']
        ])->saveData();

        // 15. Preguntas Frecuentes
        $this->table('preguntas_frecuentes')->insert([
            ['pregunta' => '¿Dónde puedo comprar mis entradas?', 'respuesta' => 'Las entradas estarán disponibles en las taquillas del evento o pueden adquirirse de forma anticipada a través de la página web de la Cámara de Comercio de Montería y puntos de venta autorizados.', 'orden' => 0, 'activo' => true],
            ['pregunta' => '¿El evento es apto para niños?', 'respuesta' => '¡Por supuesto! Sazón Córdoba es un evento familiar. Contaremos con zonas especiales y platillos pensados para los más pequeños, además de un ambiente seguro y agradable.', 'orden' => 1, 'activo' => true],
            ['pregunta' => '¿Habrá opciones vegetarianas o veganas?', 'respuesta' => 'Sí. Varios de nuestros restaurantes y chefs invitados tendrán opciones vegetarianas, veganas y libres de gluten para garantizar que todos disfruten de la experiencia gastronómica.', 'orden' => 2, 'activo' => true],
            ['pregunta' => '¿Cuentan con parqueadero disponible?', 'respuesta' => 'El centro de eventos dispone de un amplio parqueadero vigilado para los asistentes. Recomendamos llegar con anticipación para asegurar tu lugar o utilizar servicios de transporte.', 'orden' => 3, 'activo' => true]
        ])->saveData();

        // 16. Pie de pagina
        $this->table('pie_pagina')->insert([
            ['tipo' => 'texto', 'titulo' => 'Sazón Córdoba', 'contenido' => 'Una experiencia gastronómica sin igual en el corazón de Montería.', 'url' => null, 'icono' => null, 'columna' => '1', 'orden' => 0],
            ['tipo' => 'red_social', 'titulo' => null, 'contenido' => null, 'url' => '#', 'icono' => 'ph-facebook-logo', 'columna' => '1', 'orden' => 1],
            ['tipo' => 'red_social', 'titulo' => null, 'contenido' => null, 'url' => '#', 'icono' => 'ph-instagram-logo', 'columna' => '1', 'orden' => 2],
            ['tipo' => 'red_social', 'titulo' => null, 'contenido' => null, 'url' => '#', 'icono' => 'ph-youtube-logo', 'columna' => '1', 'orden' => 3],
            ['tipo' => 'enlace', 'titulo' => 'Acerca de', 'contenido' => null, 'url' => '#about', 'icono' => null, 'columna' => '2', 'orden' => 0],
            ['tipo' => 'enlace', 'titulo' => null, 'contenido' => 'Términos y Condiciones', 'url' => 'terminos.html', 'icono' => null, 'columna' => '2', 'orden' => 1],
            ['tipo' => 'texto', 'titulo' => 'Contacto', 'contenido' => 'Centro de Eventos, Montería', 'url' => null, 'icono' => 'ph-map-pin', 'columna' => '3', 'orden' => 0],
            ['tipo' => 'texto', 'titulo' => 'Contacto', 'contenido' => 'info@sazoncordoba.com', 'url' => null, 'icono' => 'ph-envelope-simple', 'columna' => '3', 'orden' => 1],
            ['tipo' => 'texto', 'titulo' => 'Contacto', 'contenido' => '+57 300 123 4567', 'url' => null, 'icono' => 'ph-phone', 'columna' => '3', 'orden' => 2]
        ])->saveData();

        // 17. Secciones subtitulos
        $this->table('secciones_subtitulos')->insert([
            ['seccion' => 'chefs', 'titulo' => 'Exponentes Especiales', 'subtitulo' => 'Los maestros culinarios que darán vida a los sabores más intensos de la noche.'],
            ['seccion' => 'dishes', 'titulo' => 'Platillos Destacados', 'subtitulo' => 'Una mirada a las delicias que te esperan. Prepárate para enamorar tu paladar.'],
            ['seccion' => 'itinerary', 'titulo' => 'Itinerario y Menú', 'subtitulo' => 'Planifica tu visita y no te pierdas ninguna degustación.'],
            ['seccion' => 'faq', 'titulo' => 'Preguntas Frecuentes', 'subtitulo' => 'Resuelve tus dudas sobre el evento Sazón Córdoba.'],
            ['seccion' => 'sponsors', 'titulo' => 'Nuestros Patrocinadores', 'subtitulo' => 'Haciendo posible el mejor evento de la ciudad.'],
            ['seccion' => 'participa', 'titulo' => 'Haz Parte del Evento', 'subtitulo' => 'Súmate como expositor, patrocinador o aliado gastronómico.'],
            ['seccion' => 'directorio', 'titulo' => 'Directorio de Expositores', 'subtitulo' => 'Descubre a los negocios y marcas que estarán presentes en la feria.'],
            ['seccion' => 'galeria', 'titulo' => 'Galería', 'subtitulo' => 'Revive los mejores momentos de nuestras ediciones anteriores.'],
            ['seccion' => 'registro', 'titulo' => 'Regístrate', 'subtitulo' => 'Asegura tu cupo como visitante o postúlate como expositor.'],
            ['seccion' => 'contacto', 'titulo' => 'Contáctanos', 'subtitulo' => '¿Tienes preguntas? Escríbenos y te responderemos pronto.']
        ])->saveData();

        // 18. Configuraciones sitio
        $this->table('configuraciones_sitio')->insert([
            ['clave' => 'footer_copyright', 'valor' => '&copy; 2026 Cámara de Comercio de Montería. Todos los derechos reservados.'],
            ['clave' => 'color_nav_fondo', 'valor' => '#000000'],
            ['clave' => 'color_footer_fondo', 'valor' => '#020202'],
            ['clave' => 'color_footer_texto', 'valor' => ''],
            ['clave' => 'mostrar_identidad', 'valor' => '1'],
            ['clave' => 'mostrar_about', 'valor' => '1'],
            ['clave' => 'mostrar_chefs', 'valor' => '1'],
            ['clave' => 'mostrar_platillos', 'valor' => '1'],
            ['clave' => 'mostrar_itinerario', 'valor' => '1'],
            ['clave' => 'mostrar_sponsors', 'valor' => '1'],
            ['clave' => 'mostrar_faq', 'valor' => '1'],
            ['clave' => 'evento_fecha_inicio', 'valor' => '2026-09-11T12:00:00'],
            ['clave' => 'evento_fecha_fin', 'valor' => '2026-09-12T22:00:00'],
            ['clave' => 'evento_horario', 'valor' => 'Vie 11 y Sáb 12 de Septiembre · 12:00 PM - 10:00 PM'],
            ['clave' => 'evento_lugar', 'valor' => 'Centro de Eventos Montería, Cra. 6 # 25-40, Montería, Córdoba'],
            ['clave' => 'evento_mapa_embed_url', 'valor' => 'https://www.google.com/maps?q=Centro+de+Eventos+Monteria&output=embed'],
            ['clave' => 'mostrar_participa', 'valor' => '1'],
            ['clave' => 'mostrar_directorio', 'valor' => '1'],
            ['clave' => 'mostrar_countdown', 'valor' => '1'],
            ['clave' => 'mostrar_galeria', 'valor' => '1'],
            ['clave' => 'mostrar_registro', 'valor' => '1'],
            ['clave' => 'mostrar_contacto', 'valor' => '1'],
            ['clave' => 'social_instagram_url', 'valor' => 'https://instagram.com/sazoncordoba'],
            ['clave' => 'social_facebook_url', 'valor' => 'https://facebook.com/sazoncordoba'],
            ['clave' => 'about_video_url', 'valor' => '']
        ])->saveData();

        // 19. Logos Nav
        $this->table('logos_nav')->insert([
            ['logo' => 'img/logos/logosason.jpg', 'activo' => true]
        ])->saveData();

        // 20. Botones Nav
        $this->table('botones_nav')->insert([
            ['texto' => 'Reservar Ahora', 'enlace' => '#itinerary', 'color_fondo' => '#ff6b00', 'color_texto' => '#ffffff', 'color_borde' => 'transparent', 'orden' => 0, 'activo' => true],
            ['texto' => 'Ingresar', 'enlace' => 'api/admin/login.php', 'color_fondo' => 'transparent', 'color_texto' => '#ffffff', 'color_borde' => '#ff6b00', 'orden' => 1, 'activo' => true]
        ])->saveData();

        // 21. Seccion Participa
        $this->table('seccion_participa')->insert([
            ['titulo' => 'Haz Parte de Sazón Córdoba', 'descripcion' => 'Súmate como expositor, patrocinador o aliado y sé parte del evento gastronómico más importante de Córdoba. Abrimos espacio a restaurantes, marcas de bebidas, artesanos y emprendedores que quieran mostrar lo mejor de nuestra tierra.', 'activo' => true]
        ])->saveData();

        // 22. Botones Participa
        $this->table('botones_participa')->insert([
            ['texto' => 'Quiero Participar', 'enlace' => '#registro', 'color_fondo' => '#ff6b00', 'color_texto' => '#ffffff', 'color_borde' => 'transparent', 'orden' => 0, 'activo' => true],
            ['texto' => 'Quiero Ser Expositor', 'enlace' => '#registro', 'color_fondo' => 'transparent', 'color_texto' => '#1a1a1a', 'color_borde' => '#1a1a1a', 'orden' => 1, 'activo' => true]
        ])->saveData();

        // 23. Directorio Expositores
        $this->table('directorio_expositores')->insert([
            ['nombre' => 'Fogón del Sinú', 'categoria' => 'Gastronomía', 'descripcion' => 'Cocina tradicional cordobesa: mote de queso, carne en posta y sancocho de guandú.', 'contacto' => 'fogondelsinu@example.com', 'orden' => 0],
            ['nombre' => 'Ron Córdoba Artesanal', 'categoria' => 'Bebidas', 'descripcion' => 'Destilería local con rones añejados y cócteles de autor a base de frutas de la región.', 'contacto' => 'roncordoba@example.com', 'orden' => 1],
            ['nombre' => 'Manos de Palma', 'categoria' => 'Artesanías', 'descripcion' => 'Tejidos en palma de iraca y caña flecha elaborados por artesanos de San Andrés de Sotavento.', 'contacto' => 'manosdepalma@example.com', 'orden' => 2],
            ['nombre' => 'Café Alto Sinú', 'categoria' => 'Bebidas', 'descripcion' => 'Café de origen cultivado en las estribaciones del Nudo de Paramillo, tueste artesanal.', 'contacto' => 'cafealtosinu@example.com', 'orden' => 3],
            ['nombre' => 'Dulces de la Abuela', 'categoria' => 'Gastronomía', 'descripcion' => 'Repostería tradicional: alegrías, cocadas y dulce de mango biche.', 'contacto' => 'dulcesabuela@example.com', 'orden' => 4]
        ])->saveData();

        // 24. Galeria Items
        $this->table('galeria_items')->insert([
            ['tipo' => 'foto', 'url' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=1200&q=80', 'titulo' => 'Show cooking en vivo', 'edicion' => 'Edición 2025', 'orden' => 0],
            ['tipo' => 'foto', 'url' => 'https://images.unsplash.com/photo-1414235077428-338988692140?w=1200&q=80', 'titulo' => 'Degustación de platillos', 'edicion' => 'Edición 2025', 'orden' => 1],
            ['tipo' => 'foto', 'url' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1200&q=80', 'titulo' => 'Ambiente del evento', 'edicion' => 'Edición 2024', 'orden' => 2],
            ['tipo' => 'foto', 'url' => 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=1200&q=80', 'titulo' => 'Chefs invitados', 'edicion' => 'Edición 2024', 'orden' => 3]
        ])->saveData();

        // 25. Terminos Condiciones
        $this->table('terminos_condiciones')->insert([
            ['contenido' => "TÉRMINOS Y CONDICIONES DE USO\n\n1. OBJETO\nEl presente documento regula el acceso y uso del sitio web oficial del evento Sazón Córdoba, organizado por la Cámara de Comercio de Montería.\n\n2. TRATAMIENTO DE DATOS PERSONALES (HABEAS DATA)\nDe acuerdo con la Ley 1581 de 2012 y demás normas concordantes, los datos personales suministrados a través de los formularios de este sitio (registro de visitantes, registro de expositores y contacto) serán tratados con la finalidad exclusiva de gestionar la participación en el evento, y no serán compartidos con terceros sin autorización previa del titular.\n\n3. REGLAMENTO DEL EVENTO\nEl ingreso al evento está sujeto a la disponibilidad de cupos y a las condiciones informadas en la sección de Registro. La organización se reserva el derecho de admisión.\n\n4. USO DEL SITIO WEB\nEste contenido es de carácter informativo. Queda prohibida la reproducción total o parcial del contenido sin autorización expresa de la Cámara de Comercio de Montería.\n\n(Texto de ejemplo — reemplazar por el texto legal definitivo aprobado por la organización.)"]
        ])->saveData();
    }
}
