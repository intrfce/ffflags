<?php

namespace Intrfce\FFFlags\Commands;

use Illuminate\Console\Command;
use Intrfce\FFFlags\Contracts\ResultStore;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'ffflags:purge')]
class PurgeFeatureFlagResultsCommand extends Command
{
    protected $signature = 'ffflags:purge';

    protected $description = 'Purge all cached feature flag results';

    public function handle(ResultStore $store): int
    {
        $store->purge();

        $this->info('All cached feature flag results have been purged.');

        return self::SUCCESS;
    }
}
