<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Commands;

use Illuminate\Console\Command;
use Vinksyunit\NotTodayHoney\Services\AttackerDetectionService;

class HoneyUnblockCommand extends Command
{
    public $signature = 'honey:unblock {ip : The IP address to unblock}';

    public $description = 'Remove all detection records for a given IP address';

    public function __construct(private readonly AttackerDetectionService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $ip = $this->argument('ip');

        if (! $this->service->isBlocked($ip)) {
            $this->warn("IP {$ip} is not currently blocked.");

            return self::SUCCESS;
        }

        $this->service->resetDetection($ip);
        $this->info("IP {$ip} has been unblocked.");

        return self::SUCCESS;
    }
}
