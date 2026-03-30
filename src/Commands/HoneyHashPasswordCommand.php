<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Commands;

use Illuminate\Console\Command;

class HoneyHashPasswordCommand extends Command
{
    public $signature = 'honey:hash-password {password : The password to hash}';

    public $description = 'Generate a truncated SHA256 hash to add to the honeypot password list';

    public function handle(): int
    {
        $password = $this->argument('password');
        $salt = config('not-today-honey.credentials.passwords.salt', 'not-today-honey');
        $hash = substr(hash('sha256', $salt.$password), 0, 8);

        $this->newLine();
        $this->line('<fg=yellow>⚠  Only use old or generic passwords — never add credentials currently in use.</>');
        $this->newLine();
        $this->line('Hash: <fg=green>'.$hash.'</>');
        $this->newLine();
        $this->line('Add to your .env:');
        $this->line('NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST='.$hash);

        return self::SUCCESS;
    }
}
