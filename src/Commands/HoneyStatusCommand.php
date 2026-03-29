<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Commands;

use Illuminate\Console\Command;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;

class HoneyStatusCommand extends Command
{
    public $signature = 'honey:status';
    public $description = 'Display currently blocked IPs and detection statistics';

    public function handle(): int
    {
        $blocked = AttackerDetection::blocked()->get();

        if ($blocked->isEmpty()) {
            $this->info('No IPs are currently blocked.');

            return self::SUCCESS;
        }

        $this->table(
            ['IP', 'Alert Level', 'Attempts', 'Blocked Until'],
            $blocked->map(fn ($d) => [
                $d->ip,
                $d->alert_level->value,
                $d->attempt_count,
                $d->blocked_until->format('Y-m-d H:i:s'),
            ])->toArray()
        );

        return self::SUCCESS;
    }
}
