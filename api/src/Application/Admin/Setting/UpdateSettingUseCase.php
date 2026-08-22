<?php

namespace App\Application\Admin\Setting;

use App\Domain\Interfaces\SettingRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class UpdateSettingUseCase
{
    private $repository;
    private $storageService;

    public function __construct(SettingRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(string $action, array $data, array $files): void
    {
        $map = [
            'save_hero_texto' => [
                'table' => 'hero_texto',
                'fields' => ['titulo', 'subtitulo', 'texto_badge', 'color'],
                'key' => 'id'
            ],
            'save_identidad' => [
                'table' => 'seccion_identidad',
                'fields' => ['titulo', 'descripcion', 'color_fondo'],
                'key' => 'id'
            ],
            'save_subtitulo' => [
                'table' => 'subtitulos_secciones',
                'fields' => ['hero', 'nosotros', 'participantes', 'programa', 'faqs', 'contacto', 'terminos'],
                'key' => 'id'
            ],
            'save_config' => [
                'table' => 'configuraciones_sitio',
                'fields' => ['valor'],
                'key' => 'clave'
            ],
            'save_terminos' => [
                'table' => 'terminos_condiciones',
                'fields' => ['contenido'],
                'key' => 'id'
            ]
        ];

        // Especiales con imágenes
        if ($action === 'save_about') {
            $updateData = [
                'titulo' => $data['titulo'] ?? '',
                'descripcion' => $data['descripcion'] ?? '',
                'color' => $data['color'] ?? '#1a1a1a',
            ];
            
            if (!empty($files['imagen']['tmp_name'])) {
                $imgUrl = $this->storageService->uploadImage('image_nosotros', 'about', $files['imagen']);
                if ($imgUrl) {
                    $current = $this->repository->getSetting('secciones_about');
                    if ($current && !empty($current['imagen'])) {
                        $this->storageService->deleteImage('image_nosotros', $current['imagen']);
                    }
                    $updateData['imagen'] = $imgUrl;
                }
            }

            $this->repository->updateSetting('secciones_about', $updateData);
            return;
        }

        if ($action === 'save_participa') {
            $updateData = [
                'titulo' => $data['titulo'] ?? '',
                'descripcion' => $data['descripcion'] ?? '',
                'activo' => $data['activo'] ?? '1',
                'color' => $data['color'] ?? '#ffffff'
            ];
            
            if (!empty($files['imagen']['tmp_name'])) {
                $imgUrl = $this->storageService->uploadImage('image_participa', 'participa', $files['imagen']);
                if ($imgUrl) {
                    $current = $this->repository->getSetting('seccion_participa');
                    if ($current && !empty($current['imagen'])) {
                        $this->storageService->deleteImage('image_participa', $current['imagen']);
                    }
                    $updateData['imagen'] = $imgUrl;
                }
            }

            $this->repository->updateSetting('seccion_participa', $updateData);
            return;
        }

        if (!isset($map[$action])) {
            throw new Exception("Acción de configuración no válida");
        }

        $cfg = $map[$action];
        $updateData = [];
        foreach ($cfg['fields'] as $f) {
            if (isset($data[$f])) {
                $updateData[$f] = $data[$f];
            }
        }

        if ($cfg['key'] === 'clave') {
            // Caso configuraciones_sitio
            $clave = $data['clave'] ?? '';
            if (!$clave) throw new Exception("Clave requerida");
            // update Setting no funciona directamente con 'clave' si asume id=1,
            // pero el repositorio modificado puede ser ajustado o usar un update nativo
            $this->updateConfig($clave, $updateData['valor']);
        } else {
            // Caso id=1
            $this->repository->updateSetting($cfg['table'], $updateData);
        }
    }

    private function updateConfig(string $clave, string $valor)
    {
        // Al ser genérico, inyectamos DB directo para este caso aislado
        \Illuminate\Database\Capsule\Manager::table('configuraciones_sitio')
            ->where('clave', $clave)
            ->update(['valor' => $valor]);
    }
}
