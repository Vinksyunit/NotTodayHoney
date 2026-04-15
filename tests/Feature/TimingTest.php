<?php

declare(strict_types=1);

it('per-trap min_response_ms overrides global when set', function (): void {
    // global is huge, per-trap is 0 — request must complete quickly
    config()->set('not-today-honey.timing.min_response_ms', 99999);
    config()->set('not-today-honey.traps.wordpress.min_response_ms', 0);

    $this->get('/wp-admin/wp-login.php')->assertStatus(200);
});

it('uses global min_response_ms when per-trap override is null', function (): void {
    config()->set('not-today-honey.timing.min_response_ms', 0);
    config()->set('not-today-honey.traps.wordpress.min_response_ms');

    $this->get('/wp-admin/wp-login.php')->assertStatus(200);
});
