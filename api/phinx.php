<?php

$config = require __DIR__ . '/config.php';
$db = $config['db'];

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'production',
        'production' => [
            'adapter' => $db['driver'] === 'pgsql' ? 'pgsql' : 'mysql',
            'host' => $db['host'],
            'name' => $db['database'],
            'user' => $db['username'],
            'pass' => $db['password'],
            'port' => $db['port'],
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];
