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
            $this->warn(sprintf('IP %s is not currently blocked.', $ip));

            return self::SUCCESS;
        }

        $this->service->resetDetection($ip);
        $this->info(sprintf('IP %s has been unblocked.', $ip));

        return self::SUCCESS;
    }
}
