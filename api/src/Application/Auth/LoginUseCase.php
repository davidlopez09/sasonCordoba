<?php

namespace App\Application\Auth;

use Illuminate\Database\Capsule\Manager as DB;
use Firebase\JWT\JWT;
use Exception;

class LoginUseCase
{
    private $jwtSecret;
    private $jwtExpiration;

    public function __construct(string $jwtSecret, int $jwtExpiration = 86400)
    {
        $this->jwtSecret = $jwtSecret;
        $this->jwtExpiration = $jwtExpiration;
    }

    public function execute(string $username, string $password): string
    {
        $user = DB::table('usuarios')->where('correo', $username)->first();

        if (!$user || !password_verify($password, $user->contrasena)) {
            throw new Exception("Usuario o contraseña incorrectos");
        }

        if ($user->rol !== 'admin') {
            throw new Exception("No tienes permisos de administrador");
        }

        $payload = [
            'iss' => 'sazon-cordoba-api',
            'iat' => time(),
            'exp' => time() + $this->jwtExpiration,
            'sub' => $user->id,
            'rol' => $user->rol
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
}
