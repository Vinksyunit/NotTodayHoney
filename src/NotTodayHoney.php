<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney;

use Illuminate\Database\Eloquent\Collection;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;
use Vinksyunit\NotTodayHoney\Services\AttackerDetectionService;

class NotTodayHoney
{
    public function __construct(private readonly AttackerDetectionService $service) {}

    public function isBlocked(string $ip): bool
    {
        return $this->service->isBlocked($ip);
    }

    public function getBlockedIps(): Collection
    {
        return $this->service->getBlockedIps();
    }

    public function unblock(string $ip): void
    {
        $this->service->resetDetection($ip);
    }

    public function getDetection(string $ip): ?AttackerDetection
    {
        return $this->service->getDetection($ip);
    }

    public function getDetectionsByLevel(AlertLevel $level): Collection
    {
        return $this->service->getDetectionsByLevel($level);
    }
}
