<?php

declare(strict_types=1);

use Vinksyunit\NotTodayHoney\Enums\TrapBehavior;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;
use Vinksyunit\NotTodayHoney\Models\CredentialAttempt;
use Vinksyunit\NotTodayHoney\Models\TrapAttempt;

it('GET wp-login.php records a PROBING attempt', function () {
    $this->get('/wp-login.php');

    expect(AttackerDetection::count())->toBe(1);
    expect(TrapAttempt::count())->toBe(1);
    expect(TrapAttempt::first()->trap_name)->toBe('wordpress');
});

it('GET wp-login.php returns WordPress login form', function () {
    $response = $this->get('/wp-login.php');

    $response->assertStatus(200);
    $response->assertSee('WordPress', false);
    $response->assertSee('Log In', false);
    $response->assertSee('name="log"', false);
    $response->assertSee('name="pwd"', false);
});

it('POST wp-login.php records INTRUSION_ATTEMPT with wrong credentials', function () {
    $this->post('/wp-login.php', ['log' => 'hacker', 'pwd' => 'wrong']);

    expect(AttackerDetection::first()->alert_level->value)->toBe('intrusion_attempt');
    expect(CredentialAttempt::count())->toBe(1);
    expect(CredentialAttempt::first()->password_matched)->toBeFalse();
});

it('POST wp-login.php returns WordPress-like error page on wrong credentials', function () {
    $response = $this->post('/wp-login.php', ['log' => 'hacker', 'pwd' => 'wrong']);

    $response->assertStatus(200);
    $response->assertSee('WordPress', false);
    $response->assertSee('Log In', false);
});

it('POST wp-login.php records ATTACKING when known password is used', function () {
    $this->post('/wp-login.php', ['log' => 'admin', 'pwd' => 'password']);

    expect(AttackerDetection::first()->alert_level->value)->toBe('attacking');
    expect(CredentialAttempt::first()->password_matched)->toBeTrue();
});

it('POST wp-login.php with known password responds with configured login_success_behavior', function () {
    config()->set('not-today-honey.traps.wordpress.login_success_behavior', TrapBehavior::FORBIDDEN);

    $this->post('/wp-login.php', ['log' => 'admin', 'pwd' => 'password'])
        ->assertStatus(403);
});
