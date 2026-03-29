<?php

declare(strict_types=1);

it('GET /wp-admin/wp-login.php includes Link header pointing to wp-json', function () {
    $response = $this->get('/wp-admin/wp-login.php');

    $response->assertHeader('Link');
    expect($response->headers->get('Link'))->toContain('api.w.org');
});

it('GET /wp-admin/wp-login.php includes X-Powered-By header', function () {
    config()->set('not-today-honey.traps.wordpress.specific.fingerprint.php_version', '8.2.5');

    $response = $this->get('/wp-admin/wp-login.php');

    $response->assertHeader('X-Powered-By', 'PHP/8.2.5');
});

it('GET /wp-admin/wp-login.php does not add fingerprint headers when disabled', function () {
    config()->set('not-today-honey.traps.wordpress.specific.fingerprint.enabled', false);

    $response = $this->get('/wp-admin/wp-login.php');

    $response->assertHeaderMissing('Link');
    $response->assertHeaderMissing('X-Powered-By');
});
