<?php

namespace App\Presentation;

use App\Application\Auth\LoginUseCase;
use Exception;

class AuthController
{
    private $loginUseCase;

    public function __construct(LoginUseCase $loginUseCase)
    {
        $this->loginUseCase = $loginUseCase;
    }

    public function handleRequest(string $method, array $pathParts)
    {
        if ($method === 'POST' && ($pathParts[2] ?? '') === 'login') {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';

            if (!$username || !$password) {
                http_response_code(400);
                echo json_encode(['error' => 'Usuario y contraseña son requeridos']);
                exit;
            }

            try {
                $token = $this->loginUseCase->execute($username, $password);
                http_response_code(200);
                echo json_encode([
                    'ok' => true,
                    'token' => $token,
                    'message' => 'Login exitoso'
                ]);
            } catch (Exception $e) {
                http_response_code(401);
                echo json_encode(['error' => $e->getMessage()]);
            }
            exit;
        }

        http_response_code(404);
        echo json_encode(['error' => 'Endpoint de auth no encontrado']);
        exit;
    }
}
