<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HoneyGenerateSaltCommand extends Command
{
    public $signature = 'honey:generate-salt';

    public $description = 'Generate a random password salt and write it to .env';

    public function handle(): int
    {
        $salt = Str::random(32);
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            $this->error('.env file not found.');

            return self::FAILURE;
        }

        $env = File::get($envPath);

        if (str_contains($env, 'NOT_TODAY_HONEY_PASSWORD_SALT=')) {
            File::put($envPath, preg_replace(
                '/NOT_TODAY_HONEY_PASSWORD_SALT=.*/',
                'NOT_TODAY_HONEY_PASSWORD_SALT='.$salt,
                $env
            ));
            $this->info('NOT_TODAY_HONEY_PASSWORD_SALT updated in .env');
        } else {
            File::append($envPath, "\nNOT_TODAY_HONEY_PASSWORD_SALT={$salt}\n");
            $this->info('NOT_TODAY_HONEY_PASSWORD_SALT added to .env');
        }

        $this->line('Salt: <fg=green>'.$salt.'</>');

        return self::SUCCESS;
    }
}
