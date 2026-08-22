<?php

namespace App\Application\Admin\Footer;

use App\Domain\Interfaces\FooterRepositoryInterface;
use Exception;

class UpdateFooterUseCase
{
    private $repository;

    public function __construct(FooterRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, array $data): void
    {
        if (empty($data['tipo']) || empty($data['columna'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $current = $this->repository->getById($id);
        if (!$current) throw new Exception("Registro no encontrado");


        $oldOrden = (int) $current['orden'];
        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : $oldOrden;
        $orden = max(1, min($count, $requestedOrden));

        $this->repository->shiftForUpdate($oldOrden, $orden);

        $updateData = [
            'tipo' => isset($data['tipo']) ? $data['tipo'] : null,
            'titulo' => isset($data['titulo']) ? $data['titulo'] : null,
            'contenido' => isset($data['contenido']) ? $data['contenido'] : null,
            'url' => isset($data['url']) ? $data['url'] : null,
            'icono' => isset($data['icono']) ? $data['icono'] : null,
            'columna' => isset($data['columna']) ? $data['columna'] : null,
            'color' => isset($data['color']) ? $data['color'] : null,
            'orden' => $orden
        ];


        $this->repository->update($id, $updateData);
    }
}
