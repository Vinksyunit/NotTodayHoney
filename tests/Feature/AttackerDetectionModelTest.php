<?php

declare(strict_types=1);

use Vinksyunit\NotTodayHoney\Enums\AlertLevel;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;

it('reports as blocked when blocked_until is in the future', function () {
    $detection = AttackerDetection::create([
        'ip'            => '1.2.3.4',
        'ip_hash'       => hash('sha256', '1.2.3.4'),
        'alert_level'   => AlertLevel::PROBING,
        'attempt_count' => 1,
        'blocked_at'    => now(),
        'blocked_until' => now()->addHour(),
    ]);

    expect($detection->isBlocked())->toBeTrue();
    expect($detection->getRemainingBlockTime())->toBeGreaterThan(0);
});

it('reports as not blocked when blocked_until is in the past', function () {
    $detection = AttackerDetection::create([
        'ip'            => '1.2.3.4',
        'ip_hash'       => hash('sha256', '1.2.3.4'),
        'alert_level'   => AlertLevel::PROBING,
        'attempt_count' => 1,
        'blocked_until' => now()->subHour(),
    ]);

    expect($detection->isBlocked())->toBeFalse();
    expect($detection->getRemainingBlockTime())->toBeNull();
});

it('reports as not blocked when blocked_until is null', function () {
    $detection = AttackerDetection::create([
        'ip'            => '1.2.3.4',
        'ip_hash'       => hash('sha256', '1.2.3.4'),
        'alert_level'   => AlertLevel::PROBING,
        'attempt_count' => 1,
    ]);

    expect($detection->isBlocked())->toBeFalse();
});

it('scope blocked returns only blocked detections', function () {
    AttackerDetection::create([
        'ip' => '1.1.1.1', 'ip_hash' => hash('sha256', '1.1.1.1'),
        'alert_level' => AlertLevel::PROBING, 'attempt_count' => 1,
        'blocked_until' => now()->addHour(),
    ]);
    AttackerDetection::create([
        'ip' => '2.2.2.2', 'ip_hash' => hash('sha256', '2.2.2.2'),
        'alert_level' => AlertLevel::PROBING, 'attempt_count' => 1,
    ]);

    expect(AttackerDetection::blocked()->count())->toBe(1);
});

it('scope forIpAndLevel returns detection within time window', function () {
    $detection = AttackerDetection::create([
        'ip'            => '1.2.3.4',
        'ip_hash'       => hash('sha256', '1.2.3.4'),
        'alert_level'   => AlertLevel::PROBING,
        'attempt_count' => 1,
    ]);

    $found = AttackerDetection::forIpAndLevel(hash('sha256', '1.2.3.4'), AlertLevel::PROBING, 60);
    expect($found)->not->toBeNull();
    expect($found->id)->toBe($detection->id);
});

it('scope forIpAndLevel returns null for different alert level', function () {
    AttackerDetection::create([
        'ip'            => '1.2.3.4',
        'ip_hash'       => hash('sha256', '1.2.3.4'),
        'alert_level'   => AlertLevel::PROBING,
        'attempt_count' => 1,
    ]);

    $found = AttackerDetection::forIpAndLevel(hash('sha256', '1.2.3.4'), AlertLevel::INTRUSION_ATTEMPT, 60);
    expect($found)->toBeNull();
});
