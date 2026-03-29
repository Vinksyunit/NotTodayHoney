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
    | log_level: Niveau de log Laravel utilisé lors du déclenchement de l'alerte.
    | Valeurs possibles : debug, info, notice, warning, error, critical, alert, emergency.
    |
    */
    'alerts' => [
        'probing' => [
            'threshold' => env('NOT_TODAY_HONEY_PROBING_THRESHOLD', 3),
            'time_window' => env('NOT_TODAY_HONEY_PROBING_TIME_WINDOW', 1440), // Minutes (default: 1 day)
            'mark_as_insecure' => env('NOT_TODAY_HONEY_PROBING_BLOCK', true),
            'duration' => env('NOT_TODAY_HONEY_PROBING_DURATION', 20), // Minutes
            'log_level' => env('NOT_TODAY_HONEY_PROBING_LOG_LEVEL', 'info'),
        ],
        'intrusion_attempt' => [
            'threshold' => env('NOT_TODAY_HONEY_INTRUSION_THRESHOLD', 1),
            'time_window' => env('NOT_TODAY_HONEY_INTRUSION_TIME_WINDOW', 1440), // Minutes (default: 1 day)
            'mark_as_insecure' => env('NOT_TODAY_HONEY_INTRUSION_BLOCK', true),
            'duration' => env('NOT_TODAY_HONEY_INTRUSION_DURATION', 1440), // Minutes (24 hours)
            'log_level' => env('NOT_TODAY_HONEY_INTRUSION_LOG_LEVEL', 'warning'),
        ],
        'attacking' => [
            'threshold' => env('NOT_TODAY_HONEY_ATTACKING_THRESHOLD', 1),
            'time_window' => env('NOT_TODAY_HONEY_ATTACKING_TIME_WINDOW', 1440), // Minutes (default: 1 day)
            'mark_as_insecure' => env('NOT_TODAY_HONEY_ATTACKING_BLOCK', true),
            'duration' => env('NOT_TODAY_HONEY_ATTACKING_DURATION', 43200), // Minutes (30 days)
            'log_level' => env('NOT_TODAY_HONEY_ATTACKING_LOG_LEVEL', 'critical'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Honeypot Traps
    |--------------------------------------------------------------------------
    |
    | Comportements disponibles après un faux login réussi :
    | '403'             -> Accès interdit.
    | '500'             -> Simule une erreur serveur.
    | 'infinite_loading'-> Fait ramer la requête jusqu'au timeout.
    | 'fake_success'    -> Simule un dashboard vide (comportement par défaut).
    |
    */
    'traps' => [

        'wordpress' => [
            'enabled' => env('NOT_TODAY_HONEY_WP_ENABLED', true),
            'path' => env('NOT_TODAY_HONEY_WP_PATH', '/wp-admin'),
            'login_success_behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_WP_LOGIN_SUCCESS_BEHAVIOR', 'fake_success')),
            'specific' => [
                'version' => env('NOT_TODAY_HONEY_WP_VERSION', '6.4.2'),
                'site_name' => env('NOT_TODAY_HONEY_WP_SITE_NAME', 'WordPress'),
                'logo_url' => env('NOT_TODAY_HONEY_WP_LOGO_URL', null),
            ],
        ],

        'phpmyadmin' => [
            'enabled' => env('NOT_TODAY_HONEY_PMA_ENABLED', true),
            'path' => env('NOT_TODAY_HONEY_PMA_PATH', '/phpmyadmin'),
            'login_success_behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_PMA_LOGIN_SUCCESS_BEHAVIOR', 'fake_success')),
            'specific' => [
                'pma_version' => env('NOT_TODAY_HONEY_PMA_VERSION', '5.2.1'),
                'server' => env('NOT_TODAY_HONEY_PMA_SERVER', 'localhost'),
            ],
        ],

        'generic_admin' => [
            'enabled' => env('NOT_TODAY_HONEY_GENERIC_ENABLED', true),
            'path' => env('NOT_TODAY_HONEY_GENERIC_PATH', '/admin'),
            'login_success_behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_GENERIC_LOGIN_SUCCESS_BEHAVIOR', 'fake_success')),
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
