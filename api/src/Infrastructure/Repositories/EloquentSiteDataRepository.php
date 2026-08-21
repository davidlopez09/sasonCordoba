<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Interfaces\SiteDataRepositoryInterface;
use Illuminate\Database\Capsule\Manager as DB;

class EloquentSiteDataRepository implements SiteDataRepositoryInterface
{
    public function getTerminos(): string
    {
        $terminos = DB::table('terminos_condiciones')->limit(1)->value('contenido');
        return $terminos ?: '';
    }

    public function getNavData(): array
    {
        $data = [];
        $data['menu_nav'] = DB::table('menu_navegacion')->where('activo', true)->orderBy('orden')->get()->toArray();
        $data['botones_nav'] = DB::table('botones_nav')->where('activo', true)->orderBy('orden')->get()->toArray();
        $data['configuraciones'] = [];

        $logoActivo = DB::table('logos_nav')->where('activo', true)->limit(1)->value('logo');
        if ($logoActivo) {
            $data['configuraciones']['logo_nav'] = $logoActivo;
        }

        $colorNavFondo = DB::table('configuraciones_sitio')->where('clave', 'color_nav_fondo')->value('valor');
        if ($colorNavFondo) {
            $data['configuraciones']['color_nav_fondo'] = $colorNavFondo;
        }

        return $data;
    }

    public function getSiteData(): array
    {
        $data = [];

        $data['hero']['slides'] = DB::table('banner_principal')->where('activo', true)->orderBy('orden')->get()->toArray();
        $data['hero']['texto'] = DB::table('hero_texto')->limit(1)->first() ?: null;
        $data['hero']['botones'] = DB::table('botones_hero')->where('activo', true)->orderBy('orden')->get()->toArray();
        $data['hero']['estadisticas'] = DB::table('estadisticas_principales')->orderBy('orden')->get()->toArray();

        $data['about']['seccion'] = DB::table('secciones_about')->limit(1)->first() ?: null;
        $data['about']['caracteristicas'] = DB::table('caracteristicas_about')->orderBy('orden')->get()->toArray();

        $data['exponentes'] = DB::table('exponentes')->orderBy('orden')->get()->toArray();
        $data['platillos_destacados'] = DB::table('platillos_destacados')->orderBy('orden')->get()->toArray();
        $data['itinerario'] = DB::table('itinerario_items')->orderBy('orden')->get()->toArray();
        $data['patrocinadores'] = DB::table('patrocinadores')->orderBy('orden')->get()->toArray();

        $data['identidad']['seccion'] = DB::table('seccion_identidad')->where('activo', true)->limit(1)->first() ?: null;
        $data['identidad']['badges'] = DB::table('badges_identidad')->orderBy('orden')->get()->toArray();

        $data['menu_nav'] = DB::table('menu_navegacion')->where('activo', true)->orderBy('orden')->get()->toArray();
        $data['botones_nav'] = DB::table('botones_nav')->where('activo', true)->orderBy('orden')->get()->toArray();
        $data['faq'] = DB::table('preguntas_frecuentes')->where('activo', true)->orderBy('orden')->get()->toArray();
        $data['footer'] = DB::table('pie_pagina')->orderBy('columna')->orderBy('orden')->get()->toArray();

        // Subtítulos
        $subtitulos = DB::table('secciones_subtitulos')->get();
        $data['subtitulos'] = [];
        foreach ($subtitulos as $row) {
            $data['subtitulos'][$row->seccion] = [
                'titulo' => $row->titulo,
                'subtitulo' => $row->subtitulo,
                'color' => $row->color,
            ];
        }

        // Configuraciones
        $configuraciones = DB::table('configuraciones_sitio')->get();
        $data['configuraciones'] = [];
        foreach ($configuraciones as $row) {
            $data['configuraciones'][$row->clave] = $row->valor;
        }

        $logoActivo = DB::table('logos_nav')->where('activo', true)->limit(1)->value('logo');
        if ($logoActivo) {
            $data['configuraciones']['logo_nav'] = $logoActivo;
        }

        // Secciones dinámicas
        $seccionesDin = DB::table('secciones_dinamicas')->where('activo', true)->orderBy('orden')->get()->toArray();
        foreach ($seccionesDin as &$sd) {
            $bloques = DB::table('bloques_dinamicos')->where('seccion_id', $sd->id)->orderBy('orden')->get()->toArray();
            foreach ($bloques as &$b) {
                $b->contenido = json_decode($b->contenido, true);
            }
            $sd->bloques = $bloques;
        }
        $data['secciones_dinamicas'] = $seccionesDin;

        $data['participa']['seccion'] = DB::table('seccion_participa')->where('activo', true)->limit(1)->first() ?: null;
        $data['participa']['botones'] = DB::table('botones_participa')->where('activo', true)->orderBy('orden')->get()->toArray();
        
        $data['directorio_expositores'] = DB::table('directorio_expositores')->where('activo', true)->orderBy('orden')->get()->toArray();
        $data['galeria'] = DB::table('galeria_items')->where('activo', true)->orderBy('orden')->get()->toArray();

        return $data;
    }
}
