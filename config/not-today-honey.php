<?php

use Vinksyunit\NotTodayHoney\Enums\TrapBehavior;

return [

    /*
    |--------------------------------------------------------------------------
    | IPs Whitelist
    |--------------------------------------------------------------------------
    |
    | IPs that will never be blocked. They trigger events with the
    | 'is_test' attribute set to true.
    |
    */
    'whitelist' => explode(',', env('NOT_TODAY_HONEY_WHITELIST', '127.0.0.1')),

    /*
    |--------------------------------------------------------------------------
    | Leaked Credentials Database
    |--------------------------------------------------------------------------
    |
    | 'usernames': List of logins you are monitoring (e.g., admin, root).
    | 'passwords': List of password hashes known to be in data leaks.
    |
    | If a password from this list is used -> Direct "Attacking" level.
    | If the username ALSO matches -> Possibility to display a 'fake_success'.
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
    | Probing: Simple trap visit (reconnaissance/exploration).
    | Intrusion Attempt: Any login attempt.
    | Attacking: Use of a password present in the 'passwords' list.
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
            'duration' => env('NOT_TODAY_HONEY_ATTACKING_DURATION', null), // Permanent
            'notify' => env('NOT_TODAY_HONEY_ATTACKING_NOTIFY', true),
            'channels' => explode(',', env('NOT_TODAY_HONEY_ATTACKING_CHANNELS', 'stack,slack,mail')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Honeypot Traps
    |--------------------------------------------------------------------------
    |
    | Available behaviors:
    | '403'             -> Access forbidden.
    | '500'             -> Simulates a server error.
    | 'infinite_loading'-> Stalls the request until timeout.
    | 'fake_success'    -> Simulates an empty dashboard (if username matches).
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
