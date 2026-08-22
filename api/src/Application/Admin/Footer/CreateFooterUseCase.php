<?php

namespace App\Application\Admin\Footer;

use App\Domain\Interfaces\FooterRepositoryInterface;
use Exception;

class CreateFooterUseCase
{
    private $repository;

    public function __construct(FooterRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data): void
    {
        if (empty($data['tipo']) || empty($data['columna'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'tipo' => isset($data['tipo']) ? $data['tipo'] : null,
            'titulo' => isset($data['titulo']) ? $data['titulo'] : null,
            'contenido' => isset($data['contenido']) ? $data['contenido'] : null,
            'url' => isset($data['url']) ? $data['url'] : null,
            'icono' => isset($data['icono']) ? $data['icono'] : null,
            'columna' => isset($data['columna']) ? $data['columna'] : null,
            'color' => isset($data['color']) ? $data['color'] : null,
            'orden' => $orden
        ]);
    }
}
