<?php

if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        if (!file_exists($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"");
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

loadEnv(__DIR__ . '/../.env');

return [
    'db' => [
        'driver' => getenv('DB_DRIVER') ?: 'pgsql',
        'host' => getenv('DB_HOST') ?: 'aws-0-ca-central-1.pooler.supabase.com',
        'port' => (int)(getenv('DB_PORT') ?: 5432),
        'database' => getenv('DB_DATABASE') ?: 'postgres',
        'username' => getenv('DB_USERNAME') ?: '',
        'password' => getenv('DB_PASSWORD') ?: '',
    ],
    'supabase' => [
        'url' => getenv('SUPABASE_URL') ?: '',
        'secret_key' => getenv('SUPABASE_SECRET_KEY') ?: '',
    ],
    'jwt' => [
        'secret' => getenv('JWT_SECRET') ?: 'default-secret-change-me',
        'expiration' => 86400 // 24 hours
    ],
    'cors' => [
        'origin' => getenv('APP_CORS_ORIGIN') ?: '*',
    ]
];
