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

it('GET /wp-json/wp/v2/users returns 200 JSON array of fake users', function (): void {
    $response = $this->get('/wp-json/wp/v2/users');

    $response->assertStatus(200);
    $response->assertJsonStructure([['id', 'name', 'slug', 'link', 'avatar_urls']]);
});

it('GET /wp-json/wp/v2/users returns users from fake_users config', function (): void {
    config()->set('not-today-honey.traps.wordpress.specific.fingerprint.fake_users', ['alice', 'bob']);

    $response = $this->get('/wp-json/wp/v2/users');

    $response->assertJsonFragment(['slug' => 'alice']);
    $response->assertJsonFragment(['slug' => 'bob']);
});

it('GET /wp-json/wp/v2/users records a PROBING attempt', function (): void {
    $this->get('/wp-json/wp/v2/users');

    expect(AttackerDetection::count())->toBe(1);
    expect(TrapAttempt::first()->trap_name)->toBe('wordpress');
});

it('GET /wp-json/wp/v2/users has Link and X-Powered-By headers', function (): void {
    $response = $this->get('/wp-json/wp/v2/users');

    $response->assertHeader('Link');
    $response->assertHeader('X-Powered-By');
});
