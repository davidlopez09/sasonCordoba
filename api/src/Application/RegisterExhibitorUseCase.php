<?php

namespace App\Application;

use App\Domain\Interfaces\ExhibitorRepositoryInterface;
use App\Domain\Interfaces\MailServiceInterface;
use Exception;

class RegisterExhibitorUseCase
{
    private $repository;
    private $mailService;

    public function __construct(ExhibitorRepositoryInterface $repository, MailServiceInterface $mailService)
    {
        $this->repository = $repository;
        $this->mailService = $mailService;
    }

    public function execute(array $data)
    {
        $nombreEmpresa = $this->requireField($data, 'nombre_empresa', 'Nombre del negocio');
        $nombreContacto = $this->requireField($data, 'nombre_contacto', 'Nombre de contacto');
        $correo = $this->validateEmail($data, 'correo');
        $categoria = trim($data['categoria'] ?? '') ?: 'Gastronomía';
        $telefono = trim($data['telefono'] ?? '') ?: null;
        $descripcion = trim($data['descripcion'] ?? '') ?: null;

        $this->repository->save([
            'nombre_empresa' => $nombreEmpresa,
            'nombre_contacto' => $nombreContacto,
            'correo' => $correo,
            'categoria' => $categoria,
            'telefono' => $telefono,
            'descripcion' => $descripcion
        ]);

        $this->mailService->sendConfirmation(
            $correo,
            'Postulación recibida - Sazón Córdoba',
            "Hola $nombreContacto,\n\nRecibimos la postulación de \"$nombreEmpresa\" como expositor de Sazón Córdoba.\n\nLa organización revisará tu información y se pondrá en contacto contigo pronto.\n\n¡Gracias por querer hacer parte del evento!"
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
