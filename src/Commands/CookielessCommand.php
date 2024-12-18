<?php

declare(strict_types=1);

namespace Katalam\Cookieless\Commands;

use Illuminate\Console\Command;

class CookielessCommand extends Command
{
    public $signature = 'laravel-cookieless-session';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
