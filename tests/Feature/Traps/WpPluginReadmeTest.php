<?php

declare(strict_types=1);

use Vinksyunit\NotTodayHoney\Models\AttackerDetection;
use Vinksyunit\NotTodayHoney\Models\TrapAttempt;

it('GET /wp-content/plugins/{plugin}/readme.txt returns 200 for configured plugin', function (): void {
    $response = $this->get('/wp-content/plugins/really-simple-ssl/readme.txt');

    $response->assertStatus(200);
});

it('GET /wp-content/plugins/{plugin}/readme.txt contains Stable tag for configured plugin', function (): void {
    $response = $this->get('/wp-content/plugins/really-simple-ssl/readme.txt');

    $response->assertSee('Stable tag: 9.1.1', false);
});

it('GET /wp-content/plugins/{plugin}/readme.txt returns 404 for unconfigured plugin', function (): void {
    $response = $this->get('/wp-content/plugins/unknown-plugin/readme.txt');

    $response->assertStatus(404);
});

it('GET /wp-content/plugins/{plugin}/readme.txt records a PROBING attempt for configured plugin', function (): void {
    $this->get('/wp-content/plugins/really-simple-ssl/readme.txt');

    expect(AttackerDetection::count())->toBe(1);
    expect(TrapAttempt::first()->trap_name)->toBe('wordpress');
});

it('GET /wp-content/plugins/{plugin}/readme.txt does not record attempt for unconfigured plugin', function (): void {
    $this->get('/wp-content/plugins/unknown-plugin/readme.txt');

    expect(AttackerDetection::count())->toBe(0);
});

it('GET /wp-content/plugins/{plugin}/readme.txt returns plain text content type', function (): void {
    $response = $this->get('/wp-content/plugins/really-simple-ssl/readme.txt');

    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
});
