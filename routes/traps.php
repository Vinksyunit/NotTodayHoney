<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\GenericAdmin\GenericAdminLoginController;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\GenericAdmin\GenericAdminLoginSubmitController;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\PhpMyAdmin\PhpMyAdminLoginController;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\PhpMyAdmin\PhpMyAdminLoginSubmitController;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\WordPress\WordPressLoginController;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\WordPress\WordPressLoginSubmitController;

/*
|--------------------------------------------------------------------------
| WordPress Trap Routes
|--------------------------------------------------------------------------
*/
if (config('not-today-honey.traps.wordpress.enabled', false)) {
    Route::get('wp-login.php', WordPressLoginController::class);
    Route::post('wp-login.php', WordPressLoginSubmitController::class);
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
        Route::post('index.php', PhpMyAdminLoginSubmitController::class);
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
