<?php

declare(strict_types=1);

use Vinksyunit\NotTodayHoney\Enums\TrapBehavior;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;
use Vinksyunit\NotTodayHoney\Models\CredentialAttempt;

it('GET /admin/login records a PROBING attempt', function (): void {
    $this->get('/admin/login');

    expect(AttackerDetection::count())->toBe(1);
    expect(AttackerDetection::first()->alert_level->value)->toBe('probing');
});

it('GET /admin/login returns generic admin login form', function (): void {
    $response = $this->get('/admin/login');

    $response->assertStatus(200);
    $response->assertSee('Control Panel', false);
    $response->assertSee('name="username"', false);
    $response->assertSee('name="password"', false);
    $response->assertSee('Sign In', false);
});

it('GET /admin/login shows configured title', function (): void {
    config()->set('not-today-honey.traps.generic_admin.specific.title', 'Acme Admin');

    $response = $this->get('/admin/login');

    $response->assertSee('Acme Admin', false);
});

it('POST /admin/login returns error page with username prefilled on wrong credentials', function (): void {
    $response = $this->post('/admin/login', [
        'username' => 'admin',
        'password' => 'wrong',
    ]);

    $response->assertStatus(200);
    $response->assertSee('Control Panel', false);
    $response->assertSee('Invalid username or password', false);
    $response->assertSee('value="admin"', false);
});

it('POST /admin/login records credential attempt', function (): void {
    $this->post('/admin/login', [
        'username' => 'admin',
        'password' => 'wrong',
    ]);

    expect(CredentialAttempt::count())->toBe(1);
    expect(CredentialAttempt::first()->username_used)->toBe('admin');
});

it('POST /admin/login with known password returns fake dashboard on fake_success behavior', function (): void {
    config()->set('not-today-honey.traps.generic_admin.login_success_behavior', TrapBehavior::FAKE_SUCCESS);

    $response = $this->post('/admin/login', ['username' => 'admin', 'password' => 'password']);

    $response->assertStatus(200);
    $response->assertSee('Dashboard', false);
    $response->assertSee('Control Panel', false);
    $response->assertSee('Recent Orders', false);
});

it('POST /admin/login with known password responds with configured login_success_behavior', function (): void {
    config()->set('not-today-honey.traps.generic_admin.login_success_behavior', TrapBehavior::FORBIDDEN);

    $this->post('/admin/login', ['username' => 'admin', 'password' => 'password'])
        ->assertStatus(403);
});
