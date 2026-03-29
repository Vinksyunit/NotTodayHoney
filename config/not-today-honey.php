<?php

use Vinksyunit\NotTodayHoney\Enums\TrapBehavior;

return [

    /*
    |--------------------------------------------------------------------------
    | IPs Whitelist
    |--------------------------------------------------------------------------
    |
    | Les IPs qui ne seront jamais bloquées. Elles déclenchent les événements
    | avec l'attribut 'is_test' à true.
    |
    */
    'whitelist' => explode(',', env('NOT_TODAY_HONEY_WHITELIST', '127.0.0.1')),

    /*
    |--------------------------------------------------------------------------
    | Leaked Credentials Database
    |--------------------------------------------------------------------------
    |
    | 'usernames' : Liste des logins que vous surveillez (ex: admin, root).
    | 'passwords' : Liste de hashs de mots de passe connus pour être dans des leaks.
    |
    | Si un mot de passe de cette liste est utilisé -> Niveau "Attacking" direct.
    | Si le username match AUSSI -> Possibilité d'afficher un 'fake_success'.
    |
    */
    'credentials' => [
        'usernames' => explode(',', env('NOT_TODAY_HONEY_USERNAMES', 'admin,administrator,webmaster,root,maintenance')),

        'passwords' => [
            [
                'id' => 'rockyou_top_1',
                'hash' => env('NOT_TODAY_HONEY_HASH_1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), // password
            ],
            [
                'id' => 'common_bot_pass',
                'hash' => env('NOT_TODAY_HONEY_HASH_2', '$2y$12$MpIJDXlMSbIaTHh9DcXIsOA.7RdxNkd1cFXwv1O0zy2UoO0DzA.Uq'), // 123456
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Levels Configuration
    |--------------------------------------------------------------------------
    |
    | Probing: Simple visite du piège (reconnaissance/exploration).
    | Intrusion Attempt: Tentative de login (quelconque).
    | Attacking: Utilisation d'un mot de passe présent dans la liste 'passwords'.
    |
    */
    'alerts' => [
        'probing' => [
            'threshold' => env('NOT_TODAY_HONEY_PROBING_THRESHOLD', 3),
            'time_window' => env('NOT_TODAY_HONEY_PROBING_TIME_WINDOW', 1440), // Minutes (default: 1 day)
            'mark_as_insecure' => env('NOT_TODAY_HONEY_PROBING_BLOCK', true),
            'duration' => env('NOT_TODAY_HONEY_PROBING_DURATION', 1440), // Minutes
            'notify' => env('NOT_TODAY_HONEY_PROBING_NOTIFY', false),
            'channels' => explode(',', env('NOT_TODAY_HONEY_PROBING_CHANNELS', 'stack')),
        ],
        'intrusion_attempt' => [
            'threshold' => env('NOT_TODAY_HONEY_INTRUSION_THRESHOLD', 1),
            'time_window' => env('NOT_TODAY_HONEY_INTRUSION_TIME_WINDOW', 1440), // Minutes (default: 1 day)
            'mark_as_insecure' => env('NOT_TODAY_HONEY_INTRUSION_BLOCK', true),
            'duration' => env('NOT_TODAY_HONEY_INTRUSION_DURATION', 10080), // Minutes (7 days)
            'notify' => env('NOT_TODAY_HONEY_INTRUSION_NOTIFY', true),
            'channels' => explode(',', env('NOT_TODAY_HONEY_INTRUSION_CHANNELS', 'stack,slack')),
        ],
        'attacking' => [
            'threshold' => env('NOT_TODAY_HONEY_ATTACKING_THRESHOLD', 1),
            'time_window' => env('NOT_TODAY_HONEY_ATTACKING_TIME_WINDOW', 1440), // Minutes (default: 1 day)
            'mark_as_insecure' => env('NOT_TODAY_HONEY_ATTACKING_BLOCK', true),
            'duration' => env('NOT_TODAY_HONEY_ATTACKING_DURATION'), // Permanent
            'notify' => env('NOT_TODAY_HONEY_ATTACKING_NOTIFY', true),
            'channels' => explode(',', env('NOT_TODAY_HONEY_ATTACKING_CHANNELS', 'stack,slack,mail')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Honeypot Traps
    |--------------------------------------------------------------------------
    |
    | Comportements disponibles :
    | '403'             -> Accès interdit.
    | '500'             -> Simule une erreur serveur.
    | 'infinite_loading'-> Fait ramer la requête jusqu'au timeout.
    | 'fake_success'    -> Simule un dashboard vide (si le username match).
    |
    */
    'traps' => [

        'wordpress' => [
            'enabled' => env('NOT_TODAY_HONEY_WP_ENABLED', true),
            'path' => env('NOT_TODAY_HONEY_WP_PATH', '/wp-admin'),
            'behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_WP_BEHAVIOR', '403')),
            'specific' => [
                'version' => env('NOT_TODAY_HONEY_WP_VERSION', '6.4.2'),
            ],
        ],

        'phpmyadmin' => [
            'enabled' => env('NOT_TODAY_HONEY_PMA_ENABLED', true),
            'path' => env('NOT_TODAY_HONEY_PMA_PATH', '/phpmyadmin'),
            'behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_PMA_BEHAVIOR', '403')),
            'specific' => [
                'pma_version' => env('NOT_TODAY_HONEY_PMA_VERSION', '5.2.1'),
            ],
        ],

        'generic_admin' => [
            'enabled' => env('NOT_TODAY_HONEY_GENERIC_ENABLED', true),
            'path' => env('NOT_TODAY_HONEY_GENERIC_PATH', '/admin'),
            'behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_GENERIC_BEHAVIOR', '403')),
            'specific' => [
                'title' => env('NOT_TODAY_HONEY_GENERIC_TITLE', 'Control Panel'),
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'driver' => env('NOT_TODAY_HONEY_STORAGE', 'database'),
        'table' => env('NOT_TODAY_HONEY_TABLE', 'not_today_honey_logs'),
    ],

];
