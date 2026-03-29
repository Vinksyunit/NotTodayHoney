<?php

declare(strict_types=1);

use Vinksyunit\NotTodayHoney\Models\AttackerDetection;
use Vinksyunit\NotTodayHoney\Models\CredentialAttempt;

it('GET /phpmyadmin records a PROBING attempt', function () {
    $this->get('/phpmyadmin/');

    expect(AttackerDetection::count())->toBe(1);
    expect(AttackerDetection::first()->alert_level->value)->toBe('probing');
});

it('GET /phpmyadmin returns phpMyAdmin login form', function () {
    $response = $this->get('/phpmyadmin/');

    $response->assertStatus(200);
    $response->assertSee('phpMyAdmin', false);
    $response->assertSee('name="pma_username"', false);
    $response->assertSee('name="pma_password"', false);
});

it('POST /phpmyadmin/index.php returns phpMyAdmin-like error HTML', function () {
    $response = $this->post('/phpmyadmin/index.php', [
        'pma_username' => 'root',
        'pma_password' => 'wrong',
    ]);

    $response->assertStatus(200);
    $response->assertSee('phpMyAdmin', false);
    $response->assertSee('#1045', false);
});

it('POST /phpmyadmin/index.php records credential attempt', function () {
    $this->post('/phpmyadmin/index.php', [
        'pma_username' => 'root',
        'pma_password' => 'wrong',
    ]);

    expect(CredentialAttempt::count())->toBe(1);
    expect(CredentialAttempt::first()->username_used)->toBe('root');
});
