<?php

/**
 *  Decommentare per update del database  locale
 */
// return require 'settings_local.php';

return [
    'settings' => [
        // monolog settings
        'logger' => [
            'name' => 'app',
            'path' => __DIR__ . '/../log/app.log',
        ],
        'auth' => [
            'secret' => '##a##saEE#DcVeR##fd##btyu@@assxd@**'
        ],
        'session' => [
            // Session cookie settings
            'name'           => 'slim_session',
            'lifetime'       => 240,
            'path'           => '/',
            'domain'         => null,
            'secure'         => true,
            'httponly'       => true,

            // Set session cookie path, domain and secure automatically
            'cookie_autoset' => true,

            // Path where session files are stored, PHP's default path will be used if set null
            'save_path'      => null,

            // Session cache limiter
            'cache_limiter'  => 'nocache',

            // Extend session lifetime after each user activity
            'autorefresh'    => true,

            // Encrypt session data if string is set
            'encryption_key' => 'eShVmYq3t6w9z$C&F)J@McQfTjWnZr4u',

            // Session namespace
            'namespace'      => 'privacy_app'
        ],
        'doctrine_config' => [
            'encryption_key' => 'o71aD2Ep.Gj4I<5KL6MN7OP_qR98>-UW',
            'meta' => [
                'entity_path' => [
                    'app/src/Entity'
                ],
                'auto_generate_proxies' => true,
                'proxy_dir' =>  __DIR__.'/../cache/dconfig/proxies',
                'cache' => null,
            ],
            'connection' => [
                'driver'   => 'pdo_mysql',
                'host'     => '',
                'dbname'   => '',
                'user'     => '',
                'password' => '',
            ]
        ],
    ],
];
