<?php

namespace App\Infrastructure;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    public static function init(array $config)
    {
        $capsule = new Capsule;

        $dbConfig = $config['db'];

        $capsule->addConnection([
            'driver'    => $dbConfig['driver'] ?? 'pgsql',
            'host'      => $dbConfig['host'],
            'port'      => $dbConfig['port'],
            'database' => $dbConfig['database'],
            'username' => $dbConfig['username'],
            'password' => $dbConfig['password'],
            'charset'  => 'utf8',
            'collation'=> 'utf8_unicode_ci',
            'prefix'   => '',
        ]);

        // Hacer la instancia Capsule disponible globalmente via metodos estaticos
        $capsule->setAsGlobal();

        // Inicializar Eloquent ORM
        $capsule->bootEloquent();
    }
}
