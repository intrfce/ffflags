<?php

namespace Intrfce\FFFlags\Commands;

use Illuminate\Console\Command;
use Intrfce\FFFlags\Actions\ActivateFeature;
use Intrfce\FFFlags\Commands\Concerns\ResolvesFeatureFromDiscovery;
use Intrfce\FFFlags\FeatureFlagDiscovery;
use Intrfce\FFFlags\ManagedFeatureFlag;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'feature:activate')]
class ActivateFeatureCommand extends Command
{
    use ResolvesFeatureFromDiscovery;

    protected $signature = 'feature:activate {feature} {--any=} {--replace}';

    protected $description = 'Activate a managed feature flag for specific model IDs';

    public function handle(FeatureFlagDiscovery $discovery, ActivateFeature $action): int
    {
        if ($this->hasDuplicateSlugs($discovery)) {
            return self::FAILURE;
        }

        $featureName = $this->argument('feature');
        $feature = $this->resolveFeature($discovery, $featureName);

        if ($feature === null) {
            $this->error("Feature [{$featureName}] not found.");
            return self::FAILURE;
        }

        if (! ($feature instanceof ManagedFeatureFlag)) {
            $this->error("Feature [{$featureName}] is not a managed feature flag.");
            return self::FAILURE;
        }

        $any = $this->option('any');

        if ($any === null) {
            $this->error('You must provide --any with a comma-separated list of model IDs.');
            return self::FAILURE;
        }

        $ids = array_map('intval', explode(',', $any));
        $replace = $this->option('replace');

        $action->handle($feature, $ids, $replace);

        $this->info('Feature ['.$feature->getSlug().'] activated for model IDs: '.implode(', ', $ids).($replace ? ' (replaced)' : ' (additive)'));

        return self::SUCCESS;
    }
}
