<?php

declare(strict_types=1);

it('outputs the truncated SHA256 hash using the configured salt', function (): void {
    config()->set('not-today-honey.credentials.passwords.salt', 'not-today-honey');

    $expected = substr(hash('sha256', 'not-today-honey'.'testpassword'), 0, 8);

    $this->artisan('honey:hash-password', ['password' => 'testpassword'])
        ->assertSuccessful()
        ->expectsOutputToContain($expected);
});

it('uses a custom configured salt when hashing', function (): void {
    config()->set('not-today-honey.credentials.passwords.salt', 'mysalt');

    $expected = substr(hash('sha256', 'mysalt'.'testpassword'), 0, 8);

    $this->artisan('honey:hash-password', ['password' => 'testpassword'])
        ->assertSuccessful()
        ->expectsOutputToContain($expected);
});

it('displays a warning about not using real passwords', function (): void {
    $this->artisan('honey:hash-password', ['password' => 'anything'])
        ->assertSuccessful()
        ->expectsOutputToContain('never add credentials currently in use');
});
