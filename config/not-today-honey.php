<?php

use Vinksyunit\NotTodayHoney\Enums\TrapBehavior;

return [

    /*
    |--------------------------------------------------------------------------
    | IPs Whitelist
    |--------------------------------------------------------------------------
    |
    | IPs that will never be blocked. They trigger events
    | with the 'is_test' attribute set to true.
    |
    */
    'whitelist' => explode(',', env('NOT_TODAY_HONEY_WHITELIST', '127.0.0.1')),

    /*
    |--------------------------------------------------------------------------
    | Leaked Credentials Database
    |--------------------------------------------------------------------------
    |
    | 'usernames' : List of monitored logins (e.g.: admin, root).
    | 'passwords' : List of password hashes known to appear in credential leaks.
    |
    | If a password from this list is used -> "Attacking" level triggered immediately.
    | If the username also matches -> A 'fake_success' response may be displayed.
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
    | Probing: Simple visit to the trap (reconnaissance/exploration).
    | Intrusion Attempt: Any login attempt.
    | Attacking: Use of a password present in the 'passwords' list.
    |
    | log_level: Laravel log level used when the alert is triggered.
    | Possible values: debug, info, notice, warning, error, critical, alert, emergency.
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
    | Available behaviors after a fake successful login:
    | '403'             -> Access forbidden.
    | '500'             -> Simulates a server error.
    | 'fake_success'    -> Simulates an empty dashboard (default behavior).
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
                'logo_url' => env('NOT_TODAY_HONEY_WP_LOGO_URL'),
                'fingerprint' => [
                    'enabled' => env('NOT_TODAY_HONEY_WP_FINGERPRINT_ENABLED', true),
                    'php_version' => env('NOT_TODAY_HONEY_WP_FINGERPRINT_PHP_VERSION', '8.1.0'),
                    'rest_api' => env('NOT_TODAY_HONEY_WP_FINGERPRINT_REST_API', true),
                    'fake_users' => array_filter(explode(',', env('NOT_TODAY_HONEY_WP_FINGERPRINT_FAKE_USERS', 'admin'))),
                    'plugins' => [], // Configured programmatically; not settable via .env
                ],
            ],
        ],

        'phpmyadmin' => [
            'enabled' => env('NOT_TODAY_HONEY_PMA_ENABLED', true),
            'path' => env('NOT_TODAY_HONEY_PMA_PATH', '/phpmyadmin'),
            'login_success_behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_PMA_LOGIN_SUCCESS_BEHAVIOR', 'fake_success')),
            'specific' => [
                'pma_version' => env('NOT_TODAY_HONEY_PMA_VERSION', '5.2.1'),
                'server' => env('NOT_TODAY_HONEY_PMA_SERVER', 'localhost'),
                'fingerprint' => [
                    'enabled' => env('NOT_TODAY_HONEY_PMA_FINGERPRINT_ENABLED', true),
                    'lang' => env('NOT_TODAY_HONEY_PMA_FINGERPRINT_LANG', 'en'),
                ],
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
    |
    | 'connection' : Database connection to use (null = application's
    |                default connection).
    |
    | 'tables' : Table names used by the package. Useful if you have
    |            table prefixes or specific naming conventions.
    |
    */
    'storage' => [
        'connection' => env('NOT_TODAY_HONEY_DB_CONNECTION'),
        'tables' => [
            'attacker_detections' => env('NOT_TODAY_HONEY_TABLE_ATTACKER_DETECTIONS', 'nt_honey_attacker_detections'),
            'trap_attempts' => env('NOT_TODAY_HONEY_TABLE_TRAP_ATTEMPTS', 'nt_honey_trap_attempts'),
            'credential_attempts' => env('NOT_TODAY_HONEY_TABLE_CREDENTIAL_ATTEMPTS', 'nt_honey_credential_attempts'),
        ],
    ],

];
