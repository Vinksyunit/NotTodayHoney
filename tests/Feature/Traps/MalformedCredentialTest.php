<?php

declare(strict_types=1);

use Vinksyunit\NotTodayHoney\Models\CredentialAttempt;

/*
|--------------------------------------------------------------------------
| Malformed credential inputs must never 500, and must never be accepted.
|--------------------------------------------------------------------------
| Attackers and scanners routinely submit array-style fields (log[]=x) or
| JSON bodies with null values. These are non-string and previously caused
| a TypeError (500) when passed to the strict-typed checkCredentials().
*/

it('WordPress login does not 500 when username is sent as an array', function (): void {
    $response = $this->post('/wp-admin/wp-login.php', ['log' => ['a', 'b'], 'pwd' => 'wrong']);

    $response->assertStatus(200);
    expect(CredentialAttempt::first()->password_matched)->toBeFalse();
});

it('WordPress login does not 500 when password is sent as an array', function (): void {
    $response = $this->post('/wp-admin/wp-login.php', ['log' => 'admin', 'pwd' => ['x']]);

    $response->assertStatus(200);
    expect(CredentialAttempt::first()->password_matched)->toBeFalse();
});

it('WordPress login does not 500 when credentials are JSON null', function (): void {
    $response = $this->postJson('/wp-admin/wp-login.php', ['log' => null, 'pwd' => null]);

    $response->assertStatus(200);
    expect(CredentialAttempt::first()->password_matched)->toBeFalse();
});

it('phpMyAdmin login does not 500 with array credentials', function (): void {
    $this->post('/phpmyadmin/', ['pma_username' => ['a'], 'pma_password' => ['b']])
        ->assertStatus(200);

    expect(CredentialAttempt::first()->password_matched)->toBeFalse();
});

it('Generic admin login does not 500 with array credentials', function (): void {
    $this->post('/admin/login', ['username' => ['a'], 'password' => ['b']])
        ->assertStatus(200);

    expect(CredentialAttempt::first()->password_matched)->toBeFalse();
});

it('a non-string password is never treated as a valid login', function (): void {
    // The matching known default password is "letmein"; an array must not match.
    $this->post('/wp-admin/wp-login.php', ['log' => 'admin', 'pwd' => ['letmein']])
        ->assertStatus(200); // FORBIDDEN behavior would be 403 if accepted

    expect(CredentialAttempt::first()->password_matched)->toBeFalse();
});
