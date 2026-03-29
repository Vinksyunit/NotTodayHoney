<?php

declare(strict_types=1);

use Vinksyunit\NotTodayHoney\Models\AttackerDetection;
use Vinksyunit\NotTodayHoney\Models\TrapAttempt;

it('GET /wp-json/ returns 200 JSON discovery document', function (): void {
    $response = $this->get('/wp-json/');

    $response->assertStatus(200);
    $response->assertJsonStructure(['namespaces', 'authentication', 'url']);
});

it('GET /wp-json/ namespaces include wp/v2', function (): void {
    $response = $this->get('/wp-json/');

    $response->assertJsonFragment(['namespaces' => ['oembed/1.0', 'wp/v2']]);
});

it('GET /wp-json/ records a PROBING attempt', function (): void {
    $this->get('/wp-json/');

    expect(AttackerDetection::count())->toBe(1);
    expect(TrapAttempt::first()->trap_name)->toBe('wordpress');
});

it('GET /wp-json/ has Link and X-Powered-By headers', function (): void {
    $response = $this->get('/wp-json/');

    $response->assertHeader('Link');
    $response->assertHeader('X-Powered-By');
});
