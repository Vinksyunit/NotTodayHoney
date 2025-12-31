<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Commands;

use Illuminate\Console\Command;

class NotTodayHoneyCommand extends Command
{
    public $signature = 'not-today-honey';

    public $description = 'NotTodayHoney honeypot command';

    public function handle(): int
    {
        $this->comment('NotTodayHoney is active');

        return self::SUCCESS;
    }
}
