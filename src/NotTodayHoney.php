<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney;

use Illuminate\Database\Eloquent\Collection;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;
use Vinksyunit\NotTodayHoney\Services\AttackerDetectionService;

class NotTodayHoney
{
    public function __construct(
        protected AttackerDetectionService $service,
    ) {}

    /**
     * Record an attempt from an IP address.
     */
    public function recordAttempt(string $ip, AlertLevel $level): AttackerDetection
    {
        return $this->service->recordAttempt($ip, $level);
    }

    /**
     * Check if an IP is currently blocked.
     */
    public function isBlocked(string $ip): bool
    {
        return $this->service->isBlocked($ip);
    }

    /**
     * Get the detection record for an IP address.
     */
    public function getDetection(string $ip): ?AttackerDetection
    {
        return $this->service->getDetection($ip);
    }

    /**
     * Get all currently blocked IPs.
     */
    public function getBlockedIps(): Collection
    {
        return $this->service->getBlockedIps();
    }

    /**
     * Get detections filtered by alert level.
     */
    public function getDetectionsByLevel(AlertLevel $level): Collection
    {
        return $this->service->getDetectionsByLevel($level);
    }

    /**
     * Reset (delete) a detection record for an IP.
     */
    public function resetDetection(string $ip): void
    {
        $this->service->resetDetection($ip);
    }

    /**
     * Check if an IP is whitelisted.
     */
    public function isWhitelisted(string $ip): bool
    {
        $whitelist = config('not-today-honey.whitelist', []);

        return in_array($ip, $whitelist, true);
    }
}
