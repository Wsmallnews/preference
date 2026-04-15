<?php

namespace Wsmallnews\Preference\Commands;

use Illuminate\Console\Command;

class PreferenceCommand extends Command
{
    public $signature = 'preference';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
