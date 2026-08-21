<?php

namespace App\Application;

use App\Domain\Interfaces\ContactRepositoryInterface;
use Exception;

class SubmitContactUseCase
{
    private $repository;

    public function __construct(ContactRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data)
    {
        $nombre = $this->requireField($data, 'nombre', 'Nombre');
        $correo = $this->validateEmail($data, 'correo');
        $telefono = trim($data['telefono'] ?? '') ?: null;
        $mensaje = $this->requireField($data, 'mensaje', 'Mensaje');

        $this->repository->save([
            'nombre' => $nombre,
            'correo' => $correo,
            'telefono' => $telefono,
            'mensaje' => $mensaje
        ]);
    }

    private function requireField(array $data, string $key, string $fieldName): string
    {
        $value = trim((string)($data[$key] ?? ''));
        if ($value === '') {
            throw new Exception("El campo \"$fieldName\" es obligatorio", 422);
        }
        return $value;
    }

    private function validateEmail(array $data, string $key): string
    {
        $email = trim((string)($data[$key] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Correo electrónico no válido', 422);
        }
        return $email;
    }
}
