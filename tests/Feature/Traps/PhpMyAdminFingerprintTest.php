<?php

declare(strict_types=1);

it('GET /phpmyadmin/ sets phpMyAdmin session cookie', function () {
    $response = $this->get('/phpmyadmin/');

    $response->assertCookie('phpMyAdmin');
});

it('GET /phpmyadmin/ sets pma_lang cookie with configured language', function () {
    config()->set('not-today-honey.traps.phpmyadmin.specific.fingerprint.lang', 'fr');

    $response = $this->get('/phpmyadmin/');

    $response->assertPlainCookie('pma_lang', 'fr');
});

it('GET /phpmyadmin/ sets pmaCookieVer cookie with major version digit', function () {
    config()->set('not-today-honey.traps.phpmyadmin.specific.pma_version', '5.2.1');

    $response = $this->get('/phpmyadmin/');

    $response->assertPlainCookie('pmaCookieVer', '5');
});

it('GET /phpmyadmin/ pma_lang cookie is not httpOnly', function () {
    $response = $this->get('/phpmyadmin/');

    $cookie = collect($response->headers->getCookies())
        ->first(fn ($c) => $c->getName() === 'pma_lang');

    expect($cookie)->not->toBeNull();
    expect($cookie->isHttpOnly())->toBeFalse();
});

it('GET /phpmyadmin/ does not set cookies when fingerprint is disabled', function () {
    config()->set('not-today-honey.traps.phpmyadmin.specific.fingerprint.enabled', false);

    $response = $this->get('/phpmyadmin/');

    $response->assertCookieMissing('phpMyAdmin');
    $response->assertCookieMissing('pma_lang');
    $response->assertCookieMissing('pmaCookieVer');
});
