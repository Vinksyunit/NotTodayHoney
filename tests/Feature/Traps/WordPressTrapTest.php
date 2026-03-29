<?php

declare(strict_types=1);

use Vinksyunit\NotTodayHoney\Enums\TrapBehavior;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;
use Vinksyunit\NotTodayHoney\Models\CredentialAttempt;
use Vinksyunit\NotTodayHoney\Models\TrapAttempt;

it('GET /wp-admin redirects to /wp-admin/wp-login.php', function (): void {
    $this->get('/wp-admin')
        ->assertRedirect('/wp-admin/wp-login.php');
});

it('GET /wp-admin/ redirects to /wp-admin/wp-login.php', function (): void {
    $this->get('/wp-admin/')
        ->assertRedirect('/wp-admin/wp-login.php');
});

it('GET /wp-admin/wp-login.php records a PROBING attempt', function (): void {
    $this->get('/wp-admin/wp-login.php');

    expect(AttackerDetection::count())->toBe(1);
    expect(TrapAttempt::count())->toBe(1);
    expect(TrapAttempt::first()->trap_name)->toBe('wordpress');
});

it('GET /wp-admin/wp-login.php returns WordPress login form', function (): void {
    $response = $this->get('/wp-admin/wp-login.php');

    $response->assertStatus(200);
    $response->assertSee('WordPress', false);
    $response->assertSee('Log In', false);
    $response->assertSee('name="log"', false);
    $response->assertSee('name="pwd"', false);
    $response->assertSee('Remember Me', false);
    $response->assertSee('Lost your password?', false);
});

it('GET /wp-admin/wp-login.php uses custom logo URL when configured', function (): void {
    config()->set('not-today-honey.traps.wordpress.specific.logo_url', 'https://example.com/logo.png');

    $response = $this->get('/wp-admin/wp-login.php');

    $response->assertSee('https://example.com/logo.png', false);
});

it('GET /wp-admin/wp-login.php uses custom site name when configured', function (): void {
    config()->set('not-today-honey.traps.wordpress.specific.site_name', 'My Company');

    $response = $this->get('/wp-admin/wp-login.php');

    $response->assertSee('My Company', false);
});

it('POST /wp-admin/wp-login.php records INTRUSION_ATTEMPT with wrong credentials', function (): void {
    $this->post('/wp-admin/wp-login.php', ['log' => 'hacker', 'pwd' => 'wrong']);

    expect(AttackerDetection::first()->alert_level->value)->toBe('intrusion_attempt');
    expect(CredentialAttempt::count())->toBe(1);
    expect(CredentialAttempt::first()->password_matched)->toBeFalse();
});

it('POST /wp-admin/wp-login.php returns WordPress-like error page on wrong credentials', function (): void {
    $response = $this->post('/wp-admin/wp-login.php', ['log' => 'hacker', 'pwd' => 'wrong']);

    $response->assertStatus(200);
    $response->assertSee('WordPress', false);
    $response->assertSee('Log In', false);
});

it('POST /wp-admin/wp-login.php records ATTACKING when known password is used', function (): void {
    $this->post('/wp-admin/wp-login.php', ['log' => 'admin', 'pwd' => 'password']);

    expect(AttackerDetection::first()->alert_level->value)->toBe('attacking');
    expect(CredentialAttempt::first()->password_matched)->toBeTrue();
});

it('POST /wp-admin/wp-login.php with known password responds with configured login_success_behavior', function (): void {
    config()->set('not-today-honey.traps.wordpress.login_success_behavior', TrapBehavior::FORBIDDEN);

    $this->post('/wp-admin/wp-login.php', ['log' => 'admin', 'pwd' => 'password'])
        ->assertStatus(403);
});

it('POST /wp-admin/wp-login.php with known password returns fake dashboard on fake_success behavior', function (): void {
    config()->set('not-today-honey.traps.wordpress.login_success_behavior', TrapBehavior::FAKE_SUCCESS);

    $response = $this->post('/wp-admin/wp-login.php', ['log' => 'admin', 'pwd' => 'password']);

    $response->assertStatus(200);
    $response->assertSee('Dashboard', false);
    $response->assertSee('At a Glance', false);
    $response->assertSee('Quick Draft', false);
});
