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
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | per_ip: Limits requests from a single IP across all traps.
    |         Exceeding returns 429 with no event.
    |
    | global: Limits total requests across all IPs and all traps.
    |         Exceeding returns 429 and dispatches TrapCampaignDetectedEvent.
    |
    */
    'rate_limiting' => [
        'per_ip' => [
            'enabled'       => env('NOT_TODAY_HONEY_RATE_IP_ENABLED', true),
            'max_hits'      => env('NOT_TODAY_HONEY_RATE_IP_MAX', 30),
            'decay_minutes' => env('NOT_TODAY_HONEY_RATE_IP_DECAY', 1),
        ],
        'global' => [
            'enabled'       => env('NOT_TODAY_HONEY_RATE_GLOBAL_ENABLED', true),
            'max_hits'      => env('NOT_TODAY_HONEY_RATE_GLOBAL_MAX', 200),
            'decay_minutes' => env('NOT_TODAY_HONEY_RATE_GLOBAL_DECAY', 1),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Timing Normalization
    |--------------------------------------------------------------------------
    |
    | Ensures every trap response takes at least this many milliseconds,
    | preventing timing-based reconnaissance.
    |
    | Per-trap overrides (null = use global):
    | NOT_TODAY_HONEY_WP_MIN_RESPONSE_MS
    | NOT_TODAY_HONEY_PMA_MIN_RESPONSE_MS
    | NOT_TODAY_HONEY_GENERIC_MIN_RESPONSE_MS
    |
    */
    'timing' => [
        'min_response_ms' => env('NOT_TODAY_HONEY_MIN_RESPONSE_MS', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Leaked Credentials Database
    |--------------------------------------------------------------------------
    |
    | 'usernames' : List of monitored logins (e.g.: admin, root).
    |
    | 'passwords.include_defaults': Enables the built-in password list
    |   ("letmein", "iloveyou"). Automatically disabled when
    |   NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST is set.
    |
    | 'passwords.custom': Comma-separated truncated SHA256 hashes (8 chars).
    |   Generate entries with: php artisan honey:hash-password {password}
    |
    | 'passwords.salt': Salt used when hashing custom passwords.
    |   Generate once with: php artisan honey:generate-salt
    |
    */
    'credentials' => [
        'usernames' => explode(',', env('NOT_TODAY_HONEY_USERNAMES', 'admin,administrator,webmaster,root,maintenance')),

        'passwords' => [
            'include_defaults' => env('NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST') === null,
            'custom'           => array_filter(explode(',', env('NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST', ''))),
            'salt'             => env('NOT_TODAY_HONEY_PASSWORD_SALT', 'not-today-honey'),
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
            'threshold'        => env('NOT_TODAY_HONEY_PROBING_THRESHOLD', 3),
            'time_window'      => env('NOT_TODAY_HONEY_PROBING_TIME_WINDOW', 1440),
            'mark_as_insecure' => env('NOT_TODAY_HONEY_PROBING_BLOCK', true),
            'duration'         => env('NOT_TODAY_HONEY_PROBING_DURATION', 20),
            'log_level'        => env('NOT_TODAY_HONEY_PROBING_LOG_LEVEL', 'info'),
        ],
        'intrusion_attempt' => [
            'threshold'        => env('NOT_TODAY_HONEY_INTRUSION_THRESHOLD', 1),
            'time_window'      => env('NOT_TODAY_HONEY_INTRUSION_TIME_WINDOW', 1440),
            'mark_as_insecure' => env('NOT_TODAY_HONEY_INTRUSION_BLOCK', true),
            'duration'         => env('NOT_TODAY_HONEY_INTRUSION_DURATION', 1440),
            'log_level'        => env('NOT_TODAY_HONEY_INTRUSION_LOG_LEVEL', 'warning'),
        ],
        'attacking' => [
            'threshold'        => env('NOT_TODAY_HONEY_ATTACKING_THRESHOLD', 1),
            'time_window'      => env('NOT_TODAY_HONEY_ATTACKING_TIME_WINDOW', 1440),
            'mark_as_insecure' => env('NOT_TODAY_HONEY_ATTACKING_BLOCK', true),
            'duration'         => env('NOT_TODAY_HONEY_ATTACKING_DURATION', 43200),
            'log_level'        => env('NOT_TODAY_HONEY_ATTACKING_LOG_LEVEL', 'critical'),
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
    | 'infinite_loading'-> Stalls the request until timeout.
    | 'fake_success'    -> Simulates an empty dashboard (default behavior).
    |
    */
    'traps' => [

        'wordpress' => [
            'enabled'                => env('NOT_TODAY_HONEY_WP_ENABLED', true),
            'path'                   => env('NOT_TODAY_HONEY_WP_PATH', '/wp-admin'),
            'login_success_behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_WP_LOGIN_SUCCESS_BEHAVIOR', 'fake_success')),
            'min_response_ms'        => env('NOT_TODAY_HONEY_WP_MIN_RESPONSE_MS'),
            'specific' => [
                'version'   => env('NOT_TODAY_HONEY_WP_VERSION', '6.4.2'),
                'site_name' => env('NOT_TODAY_HONEY_WP_SITE_NAME', 'WordPress'),
                'logo_url'  => env('NOT_TODAY_HONEY_WP_LOGO_URL'),
                'fingerprint' => [
                    'enabled'     => env('NOT_TODAY_HONEY_WP_FINGERPRINT_ENABLED', true),
                    'php_version' => env('NOT_TODAY_HONEY_WP_FINGERPRINT_PHP_VERSION', '8.1.0'),
                    'rest_api'    => env('NOT_TODAY_HONEY_WP_FINGERPRINT_REST_API', true),
                    'fake_users'  => array_filter(explode(',', env('NOT_TODAY_HONEY_WP_FINGERPRINT_FAKE_USERS', 'admin'))),
                    'plugins'     => [],
                ],
            ],
        ],

        'phpmyadmin' => [
            'enabled'                => env('NOT_TODAY_HONEY_PMA_ENABLED', true),
            'path'                   => env('NOT_TODAY_HONEY_PMA_PATH', '/phpmyadmin'),
            'login_success_behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_PMA_LOGIN_SUCCESS_BEHAVIOR', 'fake_success')),
            'min_response_ms'        => env('NOT_TODAY_HONEY_PMA_MIN_RESPONSE_MS'),
            'specific' => [
                'pma_version' => env('NOT_TODAY_HONEY_PMA_VERSION', '5.2.1'),
                'server'      => env('NOT_TODAY_HONEY_PMA_SERVER', 'localhost'),
                'fingerprint' => [
                    'enabled' => env('NOT_TODAY_HONEY_PMA_FINGERPRINT_ENABLED', true),
                    'lang'    => env('NOT_TODAY_HONEY_PMA_FINGERPRINT_LANG', 'en'),
                ],
            ],
        ],

        'generic_admin' => [
            'enabled'                => env('NOT_TODAY_HONEY_GENERIC_ENABLED', true),
            'path'                   => env('NOT_TODAY_HONEY_GENERIC_PATH', '/admin'),
            'login_success_behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_GENERIC_LOGIN_SUCCESS_BEHAVIOR', 'fake_success')),
            'min_response_ms'        => env('NOT_TODAY_HONEY_GENERIC_MIN_RESPONSE_MS'),
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
    | 'tables' : Table names used by the package.
    |
    */
    'storage' => [
        'connection' => env('NOT_TODAY_HONEY_DB_CONNECTION'),
        'tables' => [
            'attacker_detections'  => env('NOT_TODAY_HONEY_TABLE_ATTACKER_DETECTIONS', 'nt_honey_attacker_detections'),
            'trap_attempts'        => env('NOT_TODAY_HONEY_TABLE_TRAP_ATTEMPTS', 'nt_honey_trap_attempts'),
            'credential_attempts'  => env('NOT_TODAY_HONEY_TABLE_CREDENTIAL_ATTEMPTS', 'nt_honey_credential_attempts'),
        ],
    ],

];
