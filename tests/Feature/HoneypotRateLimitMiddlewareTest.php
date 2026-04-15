<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Vinksyunit\NotTodayHoney\Events\TrapCampaignDetectedEvent;

beforeEach(function (): void {
    RateLimiter::clear('honey_global');
    RateLimiter::clear('honey_ip:1.2.3.4');
    RateLimiter::clear('honey_ip:5.6.7.8');
    RateLimiter::clear('honey_ip:9.9.9.9');
    Cache::forget('honey_global:campaign_detected');
});

it('allows requests through when rate limiting is disabled', function (): void {
    config()->set('not-today-honey.rate_limiting.per_ip.enabled', false);
    config()->set('not-today-honey.rate_limiting.global.enabled', false);

    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])
        ->get('/wp-admin/wp-login.php')
        ->assertStatus(200);
});

it('returns 429 when per-IP limit is exceeded', function (): void {
    config()->set('not-today-honey.rate_limiting.per_ip.enabled', true);
    config()->set('not-today-honey.rate_limiting.per_ip.max_hits', 2);
    config()->set('not-today-honey.rate_limiting.per_ip.decay_minutes', 1);

    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])->get('/wp-admin/wp-login.php')->assertStatus(200);
    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])->get('/wp-admin/wp-login.php')->assertStatus(200);
    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])->get('/wp-admin/wp-login.php')->assertStatus(429);
});

it('counts per-IP limits independently across IPs', function (): void {
    config()->set('not-today-honey.rate_limiting.per_ip.enabled', true);
    config()->set('not-today-honey.rate_limiting.per_ip.max_hits', 1);
    config()->set('not-today-honey.rate_limiting.per_ip.decay_minutes', 1);

    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])->get('/wp-admin/wp-login.php')->assertStatus(200);
    $this->withServerVariables(['REMOTE_ADDR' => '5.6.7.8'])->get('/wp-admin/wp-login.php')->assertStatus(200);
});

it('returns 429 and dispatches TrapCampaignDetectedEvent when global limit exceeded', function (): void {
    Event::fake([TrapCampaignDetectedEvent::class]);
    config()->set('not-today-honey.rate_limiting.global.enabled', true);
    config()->set('not-today-honey.rate_limiting.global.max_hits', 2);
    config()->set('not-today-honey.rate_limiting.global.decay_minutes', 1);

    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])->get('/wp-admin/wp-login.php')->assertStatus(200);
    $this->withServerVariables(['REMOTE_ADDR' => '5.6.7.8'])->get('/wp-admin/wp-login.php')->assertStatus(200);
    $this->withServerVariables(['REMOTE_ADDR' => '9.9.9.9'])->get('/wp-admin/wp-login.php')->assertStatus(429);
    $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->get('/wp-admin/wp-login.php')->assertStatus(429);

    Event::assertDispatchedTimes(TrapCampaignDetectedEvent::class, 1);
    Event::assertDispatched(TrapCampaignDetectedEvent::class, function (TrapCampaignDetectedEvent $event): bool {
        return $event->maxHits === 2 && $event->decayMinutes === 1;
    });
});

it('does not rate limit whitelisted IPs', function (): void {
    config()->set('not-today-honey.whitelist', ['1.2.3.4']);
    config()->set('not-today-honey.rate_limiting.per_ip.enabled', true);
    config()->set('not-today-honey.rate_limiting.per_ip.max_hits', 1);
    config()->set('not-today-honey.rate_limiting.per_ip.decay_minutes', 1);

    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])->get('/wp-admin/wp-login.php')->assertStatus(200);
    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])->get('/wp-admin/wp-login.php')->assertStatus(200);
});

it('applies rate limiting to phpmyadmin routes', function (): void {
    config()->set('not-today-honey.rate_limiting.per_ip.enabled', true);
    config()->set('not-today-honey.rate_limiting.per_ip.max_hits', 1);
    config()->set('not-today-honey.rate_limiting.per_ip.decay_minutes', 1);

    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])->get('/phpmyadmin')->assertStatus(200);
    $this->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])->get('/phpmyadmin')->assertStatus(429);
});
