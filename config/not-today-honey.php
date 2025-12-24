<?php

use Vinksyunit\NotTodayHoney\Enums\TrapBehavior;

// Extract env values before conversion to enum for config caching compatibility
$wpBehavior = env('NOT_TODAY_HONEY_WP_BEHAVIOR', '403');
$pmaBehavior = env('NOT_TODAY_HONEY_PMA_BEHAVIOR', '403');
$genericBehavior = env('NOT_TODAY_HONEY_GENERIC_BEHAVIOR', '403');

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
    | Si un mot de passe de cette liste est utilisé -> Niveau 3 direct.
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
                'hash' => env('NOT_TODAY_HONEY_HASH_2', '$2y$10$V8y.f6vB6Y...'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Levels Configuration
    |--------------------------------------------------------------------------
    |
    | Level 1: Simple visite du piège.
    | Level 2: Tentative de login (quelconque).
    | Level 3: Utilisation d'un mot de passe présent dans la liste 'passwords'.
    |
    */
    'alerts' => [
        'level_1' => [
            'threshold' => env('NOT_TODAY_HONEY_L1_THRESHOLD', 3),
            'mark_as_insecure' => env('NOT_TODAY_HONEY_L1_BLOCK', true),
            'duration' => env('NOT_TODAY_HONEY_L1_DURATION', 1440),
            'notify' => env('NOT_TODAY_HONEY_L1_NOTIFY', false),
            'channels' => explode(',', env('NOT_TODAY_HONEY_L1_CHANNELS', 'stack')),
        ],
        'level_2' => [
            'threshold' => env('NOT_TODAY_HONEY_L2_THRESHOLD', 1),
            'mark_as_insecure' => env('NOT_TODAY_HONEY_L2_BLOCK', true),
            'duration' => env('NOT_TODAY_HONEY_L2_DURATION', 10080),
            'notify' => env('NOT_TODAY_HONEY_L2_NOTIFY', true),
            'channels' => explode(',', env('NOT_TODAY_HONEY_L2_CHANNELS', 'stack,slack')),
        ],
        'level_3' => [
            'threshold' => env('NOT_TODAY_HONEY_L3_THRESHOLD', 1),
            'mark_as_insecure' => env('NOT_TODAY_HONEY_L3_BLOCK', true),
            'duration' => env('NOT_TODAY_HONEY_L3_DURATION', null), // Permanent
            'notify' => env('NOT_TODAY_HONEY_L3_NOTIFY', true),
            'channels' => explode(',', env('NOT_TODAY_HONEY_L3_CHANNELS', 'stack,slack,mail')),
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
            'behavior' => TrapBehavior::from($wpBehavior),
            'specific' => [
                'version' => env('NOT_TODAY_HONEY_WP_VERSION', '6.4.2'),
            ],
        ],

        'phpmyadmin' => [
            'enabled' => env('NOT_TODAY_HONEY_PMA_ENABLED', true),
            'path' => env('NOT_TODAY_HONEY_PMA_PATH', '/phpmyadmin'),
            'behavior' => TrapBehavior::from($pmaBehavior),
            'specific' => [
                'pma_version' => env('NOT_TODAY_HONEY_PMA_VERSION', '5.2.1'),
            ],
        ],

        'generic_admin' => [
            'enabled' => env('NOT_TODAY_HONEY_GENERIC_ENABLED', true),
            'path' => env('NOT_TODAY_HONEY_GENERIC_PATH', '/admin'),
            'behavior' => TrapBehavior::from($genericBehavior),
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
