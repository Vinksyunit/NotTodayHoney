<?php

declare(strict_types=1);

use Vinksyunit\NotTodayHoney\Enums\TrapBehavior;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;
use Vinksyunit\NotTodayHoney\Models\CredentialAttempt;

it('GET /phpmyadmin records a PROBING attempt', function (): void {
    $this->get('/phpmyadmin/');

    expect(AttackerDetection::count())->toBe(1);
    expect(AttackerDetection::first()->alert_level->value)->toBe('probing');
});

it('GET /phpmyadmin returns phpMyAdmin login form', function (): void {
    $response = $this->get('/phpmyadmin/');

    $response->assertStatus(200);
    $response->assertSee('phpMyAdmin', false);
    $response->assertSee('name="pma_username"', false);
    $response->assertSee('name="pma_password"', false);
    $response->assertSee('Log in', false);
});

it('GET /phpmyadmin shows configured server name', function (): void {
    config()->set('not-today-honey.traps.phpmyadmin.specific.server', 'db.example.com');

    $response = $this->get('/phpmyadmin/');

    $response->assertSee('db.example.com', false);
});

it('POST /phpmyadmin/ returns phpMyAdmin-like error with username prefilled', function (): void {
    $response = $this->post('/phpmyadmin/', [
        'pma_username' => 'root',
        'pma_password' => 'wrong',
    ]);

    $response->assertStatus(200);
    $response->assertSee('phpMyAdmin', false);
    $response->assertSee('#1045', false);
    $response->assertSee('root', false);
});

it('POST /phpmyadmin/ records credential attempt', function (): void {
    $this->post('/phpmyadmin/', [
        'pma_username' => 'root',
        'pma_password' => 'wrong',
    ]);

    expect(CredentialAttempt::count())->toBe(1);
    expect(CredentialAttempt::first()->username_used)->toBe('root');
});

it('POST /phpmyadmin/ with known password returns fake dashboard on fake_success behavior', function (): void {
    config()->set('not-today-honey.traps.phpmyadmin.login_success_behavior', TrapBehavior::FAKE_SUCCESS);

    $response = $this->post('/phpmyadmin/', ['pma_username' => 'admin', 'pma_password' => 'password']);

    $response->assertStatus(200);
    $response->assertSee('General settings', false);
    $response->assertSee('Database server', false);
});

it('POST /phpmyadmin/ with known password responds with configured login_success_behavior', function (): void {
    config()->set('not-today-honey.traps.phpmyadmin.login_success_behavior', TrapBehavior::FORBIDDEN);

    $this->post('/phpmyadmin/', ['pma_username' => 'admin', 'pma_password' => 'password'])
        ->assertStatus(403);
});
