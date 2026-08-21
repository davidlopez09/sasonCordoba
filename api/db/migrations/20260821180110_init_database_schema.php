<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitDatabaseSchema extends AbstractMigration
{
    public function change(): void
    {
        // 1. Usuarios
        $this->table('usuarios')
            ->addColumn('nombre', 'string', ['limit' => 255])
            ->addColumn('correo', 'string', ['limit' => 255])
            ->addColumn('email_verified_at', 'timestamp', ['null' => true])
            ->addColumn('contrasena', 'string', ['limit' => 255])
            ->addColumn('token_recordar', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('rol', 'string', ['limit' => 50, 'default' => 'user'])
            ->addIndex(['correo'], ['unique' => true])
            ->addTimestamps()
            ->create();

        // 2. Banner Principal
        $this->table('banner_principal')
            ->addColumn('imagen', 'string', ['limit' => 255])
            ->addColumn('activo', 'boolean', ['default' => true])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addTimestamps()
            ->create();

        // 3. Hero Texto
        $this->table('hero_texto')
            ->addColumn('texto_badge', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('titulo', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('subtitulo', 'text', ['null' => true])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => '#ffffff'])
            ->addTimestamps()
            ->create();

        // 4. Botones Hero
        $this->table('botones_hero')
            ->addColumn('texto', 'string', ['limit' => 100])
            ->addColumn('enlace', 'string', ['limit' => 255])
            ->addColumn('color_fondo', 'string', ['limit' => 20, 'default' => '#ff6b00'])
            ->addColumn('color_texto', 'string', ['limit' => 20, 'default' => '#ffffff'])
            ->addColumn('color_borde', 'string', ['limit' => 20, 'default' => 'transparent'])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('activo', 'boolean', ['default' => true])
            ->addTimestamps()
            ->create();

        // 5. Estadisticas Principales
        $this->table('estadisticas_principales')
            ->addColumn('numero', 'string', ['limit' => 255])
            ->addColumn('etiqueta', 'string', ['limit' => 255])
            ->addColumn('icono', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => '#ffffff'])
            ->addTimestamps()
            ->create();

        // 6. Secciones About
        $this->table('secciones_about')
            ->addColumn('imagen', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('titulo', 'string', ['limit' => 255])
            ->addColumn('descripcion', 'text')
            ->addColumn('color', 'string', ['limit' => 20, 'default' => '#1a1a1a'])
            ->addTimestamps()
            ->create();

        // 7. Caracteristicas About
        $this->table('caracteristicas_about')
            ->addColumn('icono', 'string', ['limit' => 255])
            ->addColumn('titulo', 'string', ['limit' => 255])
            ->addColumn('descripcion', 'text')
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => '#1a1a1a'])
            ->addTimestamps()
            ->create();

        // 8. Exponentes
        $this->table('exponentes')
            ->addColumn('nombre', 'string', ['limit' => 255])
            ->addColumn('especialidad', 'string', ['limit' => 255])
            ->addColumn('foto', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('instagram_url', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('twitter_url', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => '#1a1a1a'])
            ->addTimestamps()
            ->create();

        // 9. Platillos Destacados
        $this->table('platillos_destacados')
            ->addColumn('nombre', 'string', ['limit' => 255])
            ->addColumn('descripcion', 'text')
            ->addColumn('imagen', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => '#ffffff'])
            ->addColumn('etiqueta', 'string', ['limit' => 20, 'default' => 'ninguna'])
            ->addTimestamps()
            ->create();

        // 10. Itinerario Items
        $this->table('itinerario_items')
            ->addColumn('hora', 'string', ['limit' => 255])
            ->addColumn('dia', 'string', ['limit' => 255])
            ->addColumn('titulo', 'string', ['limit' => 255])
            ->addColumn('nombre_chef', 'string', ['limit' => 255])
            ->addColumn('descripcion', 'text')
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => '#1a1a1a'])
            ->addColumn('color_fondo', 'string', ['limit' => 40, 'default' => '#ffffff'])
            ->addColumn('color_borde', 'string', ['limit' => 40, 'default' => 'rgba(0,0,0,0.08)'])
            ->addColumn('tipo', 'string', ['limit' => 30, 'default' => 'general'])
            ->addTimestamps()
            ->create();

        // 11. Patrocinadores
        $this->table('patrocinadores')
            ->addColumn('nombre', 'string', ['limit' => 255])
            ->addColumn('logo', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('url', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addTimestamps()
            ->create();

        // 12. Configuraciones Sitio
        $this->table('configuraciones_sitio')
            ->addColumn('clave', 'string', ['limit' => 255])
            ->addColumn('valor', 'text', ['null' => true])
            ->addIndex(['clave'], ['unique' => true])
            ->addTimestamps()
            ->create();

        // 13. Seccion Identidad
        $this->table('seccion_identidad')
            ->addColumn('titulo', 'string', ['limit' => 255])
            ->addColumn('descripcion', 'text')
            ->addColumn('activo', 'boolean', ['default' => true])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => '#1a1a1a'])
            ->addTimestamps()
            ->create();

        // 14. Badges Identidad
        $this->table('badges_identidad')
            ->addColumn('texto', 'string', ['limit' => 255])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => '#ff6b00'])
            ->addColumn('color_fondo', 'string', ['limit' => 40, 'default' => 'rgba(255, 107, 0, 0.15)'])
            ->addTimestamps()
            ->create();

        // 15. Logos Nav
        $this->table('logos_nav')
            ->addColumn('logo', 'string', ['limit' => 500])
            ->addColumn('activo', 'boolean', ['default' => false])
            ->addTimestamps()
            ->create();

        // 16. Botones Nav
        $this->table('botones_nav')
            ->addColumn('texto', 'string', ['limit' => 100])
            ->addColumn('enlace', 'string', ['limit' => 255])
            ->addColumn('color_fondo', 'string', ['limit' => 20, 'default' => '#ff6b00'])
            ->addColumn('color_texto', 'string', ['limit' => 20, 'default' => '#ffffff'])
            ->addColumn('color_borde', 'string', ['limit' => 20, 'default' => 'transparent'])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('activo', 'boolean', ['default' => true])
            ->addTimestamps()
            ->create();

        // 17. Menu Navegacion
        $this->table('menu_navegacion')
            ->addColumn('etiqueta', 'string', ['limit' => 255])
            ->addColumn('enlace', 'string', ['limit' => 255])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('activo', 'boolean', ['default' => true])
            ->addColumn('color', 'string', ['limit' => 7, 'default' => '#ffffff'])
            ->addTimestamps()
            ->create();

        // 18. Preguntas Frecuentes
        $this->table('preguntas_frecuentes')
            ->addColumn('pregunta', 'text')
            ->addColumn('respuesta', 'text')
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('activo', 'boolean', ['default' => true])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => '#1a1a1a'])
            ->addTimestamps()
            ->create();

        // 19. Pie de Pagina
        $this->table('pie_pagina')
            ->addColumn('tipo', 'string', ['limit' => 50, 'default' => 'texto'])
            ->addColumn('titulo', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('contenido', 'text', ['null' => true])
            ->addColumn('url', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('icono', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('columna', 'string', ['limit' => 50, 'default' => '1'])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => ''])
            ->addTimestamps()
            ->create();

        // 20. Secciones Subtitulos
        $this->table('secciones_subtitulos')
            ->addColumn('seccion', 'string', ['limit' => 100])
            ->addColumn('titulo', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('subtitulo', 'text', ['null' => true])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => ''])
            ->addIndex(['seccion'], ['unique' => true])
            ->addTimestamps()
            ->create();

        // 21. Secciones Dinamicas
        $this->table('secciones_dinamicas')
            ->addColumn('nombre', 'string', ['limit' => 255])
            ->addColumn('insertar_despues', 'string', ['limit' => 50])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('activo', 'boolean', ['default' => true])
            ->addTimestamps()
            ->create();

        // 22. Bloques Dinamicos
        $this->table('bloques_dinamicos')
            ->addColumn('seccion_id', 'integer')
            ->addColumn('tipo', 'string', ['limit' => 30])
            ->addColumn('posicion', 'string', ['limit' => 20, 'default' => 'completo'])
            ->addColumn('contenido', 'text')
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addForeignKey('seccion_id', 'secciones_dinamicas', 'id', ['delete'=> 'CASCADE'])
            ->addTimestamps()
            ->create();

        // 23. Seccion Participa
        $this->table('seccion_participa')
            ->addColumn('titulo', 'string', ['limit' => 255])
            ->addColumn('descripcion', 'text')
            ->addColumn('imagen', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('activo', 'boolean', ['default' => true])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => '#1a1a1a'])
            ->addTimestamps()
            ->create();

        // 24. Botones Participa
        $this->table('botones_participa')
            ->addColumn('texto', 'string', ['limit' => 100])
            ->addColumn('enlace', 'string', ['limit' => 255])
            ->addColumn('color_fondo', 'string', ['limit' => 20, 'default' => '#ff6b00'])
            ->addColumn('color_texto', 'string', ['limit' => 20, 'default' => '#ffffff'])
            ->addColumn('color_borde', 'string', ['limit' => 20, 'default' => 'transparent'])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('activo', 'boolean', ['default' => true])
            ->addTimestamps()
            ->create();

        // 25. Directorio Expositores
        $this->table('directorio_expositores')
            ->addColumn('nombre', 'string', ['limit' => 255])
            ->addColumn('categoria', 'string', ['limit' => 100, 'default' => 'Gastronomía'])
            ->addColumn('descripcion', 'text', ['null' => true])
            ->addColumn('contacto', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('logo', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('activo', 'boolean', ['default' => true])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => '#1a1a1a'])
            ->addTimestamps()
            ->create();

        // 26. Galeria Items
        $this->table('galeria_items')
            ->addColumn('tipo', 'string', ['limit' => 20, 'default' => 'foto'])
            ->addColumn('url', 'string', ['limit' => 255])
            ->addColumn('titulo', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('edicion', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('orden', 'integer', ['default' => 0])
            ->addColumn('activo', 'boolean', ['default' => true])
            ->addTimestamps()
            ->create();

        // 27. Registros Visitantes
        $this->table('registros_visitantes')
            ->addColumn('nombre', 'string', ['limit' => 255])
            ->addColumn('correo', 'string', ['limit' => 255])
            ->addColumn('telefono', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();

        // 28. Registros Expositores
        $this->table('registros_expositores')
            ->addColumn('nombre_empresa', 'string', ['limit' => 255])
            ->addColumn('categoria', 'string', ['limit' => 100, 'default' => 'Gastronomía'])
            ->addColumn('nombre_contacto', 'string', ['limit' => 255])
            ->addColumn('correo', 'string', ['limit' => 255])
            ->addColumn('telefono', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('descripcion', 'text', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();

        // 29. Mensajes Contacto
        $this->table('mensajes_contacto')
            ->addColumn('nombre', 'string', ['limit' => 255])
            ->addColumn('correo', 'string', ['limit' => 255])
            ->addColumn('telefono', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('mensaje', 'text')
            ->addColumn('leido', 'boolean', ['default' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();

        // 30. Terminos Condiciones
        $this->table('terminos_condiciones')
            ->addColumn('contenido', 'text')
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
