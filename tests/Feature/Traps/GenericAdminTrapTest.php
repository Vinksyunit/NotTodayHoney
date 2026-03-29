<?php

declare(strict_types=1);

use Vinksyunit\NotTodayHoney\Models\AttackerDetection;
use Vinksyunit\NotTodayHoney\Models\CredentialAttempt;

it('GET /admin/login records a PROBING attempt', function () {
    $this->get('/admin/login');

    expect(AttackerDetection::count())->toBe(1);
    expect(AttackerDetection::first()->alert_level->value)->toBe('probing');
});

it('GET /admin/login returns generic admin login form', function () {
    $response = $this->get('/admin/login');

    $response->assertStatus(200);
    $response->assertSee('Control Panel', false);
    $response->assertSee('name="username"', false);
    $response->assertSee('name="password"', false);
});

it('POST /admin/login returns a generic admin error page', function () {
    $response = $this->post('/admin/login', [
        'username' => 'admin',
        'password' => 'wrong',
    ]);

    $response->assertStatus(200);
    $response->assertSee('Control Panel', false);
});

it('POST /admin/login records credential attempt', function () {
    $this->post('/admin/login', [
        'username' => 'admin',
        'password' => 'wrong',
    ]);

    expect(CredentialAttempt::count())->toBe(1);
    expect(CredentialAttempt::first()->username_used)->toBe('admin');
});
