<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\GenericAdmin\GenericAdminLoginController;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\GenericAdmin\GenericAdminLoginSubmitController;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\PhpMyAdmin\PhpMyAdminLoginController;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\PhpMyAdmin\PhpMyAdminLoginSubmitController;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\WordPress\WordPressLoginController;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\WordPress\WordPressLoginSubmitController;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\WordPress\WpRestApiIndexController;

/*
|--------------------------------------------------------------------------
| WordPress Trap Routes
|--------------------------------------------------------------------------
*/
if (config('not-today-honey.traps.wordpress.enabled', false)) {
    $wpPath = ltrim(config('not-today-honey.traps.wordpress.path', '/wp-admin'), '/');

    Route::get($wpPath, fn () => redirect("/{$wpPath}/wp-login.php"));
    Route::get($wpPath.'/', fn () => redirect("/{$wpPath}/wp-login.php"));
    Route::get($wpPath.'/wp-login.php', WordPressLoginController::class);
    Route::post($wpPath.'/wp-login.php', WordPressLoginSubmitController::class);

    $fingerprintEnabled = config('not-today-honey.traps.wordpress.specific.fingerprint.enabled', false);

    if ($fingerprintEnabled && config('not-today-honey.traps.wordpress.specific.fingerprint.rest_api', false)) {
        // Registered at root level intentionally — mimics real WordPress REST API path
        Route::get('wp-json/', WpRestApiIndexController::class);
    }
}

/*
|--------------------------------------------------------------------------
| PhpMyAdmin Trap Routes
|--------------------------------------------------------------------------
*/
if (config('not-today-honey.traps.phpmyadmin.enabled', false)) {
    $pmaPath = ltrim(config('not-today-honey.traps.phpmyadmin.path', '/phpmyadmin'), '/');

    Route::prefix($pmaPath)->group(function (): void {
        Route::get('/', PhpMyAdminLoginController::class);
        Route::post('/', PhpMyAdminLoginSubmitController::class);
    });
}

/*
|--------------------------------------------------------------------------
| Generic Admin Trap Routes
|--------------------------------------------------------------------------
*/
if (config('not-today-honey.traps.generic_admin.enabled', false)) {
    $adminPath = ltrim(config('not-today-honey.traps.generic_admin.path', '/admin'), '/');

    Route::prefix($adminPath)->group(function (): void {
        Route::get('login', GenericAdminLoginController::class);
        Route::post('login', GenericAdminLoginSubmitController::class);
    });
}
