<?php

declare(strict_types=1);

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\Facades\Route;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;
use Vinksyunit\NotTodayHoney\Http\Middleware\HoneypotBlockMiddleware;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;

beforeEach(function (): void {
    Route::middleware(HoneypotBlockMiddleware::class)
        ->get('/protected', fn (): ResponseFactory|\Illuminate\Http\Response => response('ok', 200));
});

it('allows non-blocked IPs through', function (): void {
    $this->withServerVariables(['REMOTE_ADDR' => '9.9.9.9'])
        ->get('/protected')
        ->assertStatus(200);
});

it('blocks a blocked IP with 403', function (): void {
    AttackerDetection::create([
        'ip' => '1.2.3.4',
        'ip_hash' => hash('sha256', '1.2.3.4'),
        'alert_level' => AlertLevel::PROBING,
        'attempt_count' => 3,
        'blocked_at' => now(),
        'blocked_until' => now()->addHour(),
    ]);

    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])
        ->get('/protected')
        ->assertStatus(403);
});

it('allows a previously blocked IP whose block has expired', function (): void {
    AttackerDetection::create([
        'ip' => '1.2.3.4',
        'ip_hash' => hash('sha256', '1.2.3.4'),
        'alert_level' => AlertLevel::PROBING,
        'attempt_count' => 3,
        'blocked_at' => now()->subHours(2),
        'blocked_until' => now()->subHour(),
    ]);

    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])
        ->get('/protected')
        ->assertStatus(200);
});

it('never blocks whitelisted IPs', function (): void {
    config()->set('not-today-honey.whitelist', ['1.2.3.4']);
    AttackerDetection::create([
        'ip' => '1.2.3.4',
        'ip_hash' => hash('sha256', '1.2.3.4'),
        'alert_level' => AlertLevel::ATTACKING,
        'attempt_count' => 1,
        'blocked_at' => now(),
        'blocked_until' => now()->addYears(100),
    ]);

    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])
        ->get('/protected')
        ->assertStatus(200);
});
