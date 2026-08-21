<?php

namespace App\Application;

use App\Domain\Interfaces\VisitorRepositoryInterface;
use App\Domain\Interfaces\MailServiceInterface;
use Exception;

class RegisterVisitorUseCase
{
    private $repository;
    private $mailService;

    public function __construct(VisitorRepositoryInterface $repository, MailServiceInterface $mailService)
    {
        $this->repository = $repository;
        $this->mailService = $mailService;
    }

    public function execute(array $data)
    {
        $nombre = $this->requireField($data, 'nombre', 'Nombre');
        $correo = $this->validateEmail($data, 'correo');
        $telefono = trim($data['telefono'] ?? '') ?: null;

        $this->repository->save([
            'nombre' => $nombre,
            'correo' => $correo,
            'telefono' => $telefono
        ]);

        $this->mailService->sendConfirmation(
            $correo,
            'Confirmación de registro - Sazón Córdoba',
            "Hola $nombre,\n\nTu registro como visitante a Sazón Córdoba fue recibido exitosamente.\n\nTe esperamos el 11 y 12 de septiembre en el Centro de Eventos de Montería.\n\n¡Nos vemos allá!"
        );
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
