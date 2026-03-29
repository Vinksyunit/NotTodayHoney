<?php

declare(strict_types=1);

use Vinksyunit\NotTodayHoney\Enums\AlertLevel;
use Vinksyunit\NotTodayHoney\Events\AttackerAttackingEvent;
use Vinksyunit\NotTodayHoney\Events\AttackerIntrusionAttemptEvent;
use Vinksyunit\NotTodayHoney\Events\AttackerProbingEvent;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;
use Vinksyunit\NotTodayHoney\Services\AttackerDetectionService;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->service = app(AttackerDetectionService::class);
});

it('creates a new detection record on first attempt', function () {
    $this->service->recordAttempt('1.2.3.4', AlertLevel::PROBING);

    expect(AttackerDetection::count())->toBe(1);
    $detection = AttackerDetection::first();
    expect($detection->attempt_count)->toBe(1);
    expect($detection->alert_level)->toBe(AlertLevel::PROBING);
});

it('increments attempt count for same IP within time window', function () {
    $this->service->recordAttempt('1.2.3.4', AlertLevel::PROBING);
    $this->service->recordAttempt('1.2.3.4', AlertLevel::PROBING);

    expect(AttackerDetection::count())->toBe(1);
    expect(AttackerDetection::first()->attempt_count)->toBe(2);
});

it('creates separate records for different IPs', function () {
    $this->service->recordAttempt('1.2.3.4', AlertLevel::PROBING);
    $this->service->recordAttempt('5.6.7.8', AlertLevel::PROBING);

    expect(AttackerDetection::count())->toBe(2);
});

it('stores both plain IP and hashed IP', function () {
    $this->service->recordAttempt('1.2.3.4', AlertLevel::PROBING);

    $detection = AttackerDetection::first();
    expect($detection->ip)->toBe('1.2.3.4');
    expect($detection->ip_hash)->toBe(hash('sha256', '1.2.3.4'));
    expect($detection->ip_hash)->not->toBe('1.2.3.4');
});

it('dispatches AttackerProbingEvent when probing threshold is reached', function () {
    Event::fake();
    config()->set('not-today-honey.alerts.probing.threshold', 2);

    $this->service->recordAttempt('1.2.3.4', AlertLevel::PROBING);
    Event::assertNotDispatched(AttackerProbingEvent::class);

    $this->service->recordAttempt('1.2.3.4', AlertLevel::PROBING);
    Event::assertDispatched(AttackerProbingEvent::class);
});

it('dispatches AttackerIntrusionAttemptEvent when intrusion threshold is reached', function () {
    Event::fake();
    config()->set('not-today-honey.alerts.intrusion_attempt.threshold', 1);

    $this->service->recordAttempt('1.2.3.4', AlertLevel::INTRUSION_ATTEMPT);
    Event::assertDispatched(AttackerIntrusionAttemptEvent::class);
});

it('dispatches AttackerAttackingEvent when attacking threshold is reached', function () {
    Event::fake();
    config()->set('not-today-honey.alerts.attacking.threshold', 1);

    $this->service->recordAttempt('1.2.3.4', AlertLevel::ATTACKING);
    Event::assertDispatched(AttackerAttackingEvent::class);
});

it('blocks IP when threshold is reached', function () {
    config()->set('not-today-honey.alerts.probing.threshold', 1);
    config()->set('not-today-honey.alerts.probing.duration', 60);

    $this->service->recordAttempt('1.2.3.4', AlertLevel::PROBING);

    expect($this->service->isBlocked('1.2.3.4'))->toBeTrue();
});

it('reports IP as not blocked before threshold', function () {
    config()->set('not-today-honey.alerts.probing.threshold', 5);

    $this->service->recordAttempt('1.2.3.4', AlertLevel::PROBING);

    expect($this->service->isBlocked('1.2.3.4'))->toBeFalse();
});

it('blocks IP permanently when duration is null', function () {
    config()->set('not-today-honey.alerts.attacking.threshold', 1);
    config()->set('not-today-honey.alerts.attacking.duration', null);

    $this->service->recordAttempt('1.2.3.4', AlertLevel::ATTACKING);

    $detection = AttackerDetection::first();
    expect($detection->blocked_until->gt(now()->addYears(50)))->toBeTrue();
});

it('resets a detection record', function () {
    $this->service->recordAttempt('1.2.3.4', AlertLevel::PROBING);
    expect(AttackerDetection::count())->toBe(1);

    $this->service->resetDetection('1.2.3.4');
    expect(AttackerDetection::count())->toBe(0);
});

it('does not block whitelisted IPs', function () {
    config()->set('not-today-honey.whitelist', ['127.0.0.1']);
    config()->set('not-today-honey.alerts.probing.threshold', 1);

    $this->service->recordAttempt('127.0.0.1', AlertLevel::PROBING);

    expect($this->service->isBlocked('127.0.0.1'))->toBeFalse();
    expect(AttackerDetection::count())->toBe(0);
});
