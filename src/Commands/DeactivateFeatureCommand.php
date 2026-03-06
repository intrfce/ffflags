<?php

namespace Intrfce\FFFlags\Commands;

use Illuminate\Console\Command;
use Intrfce\FFFlags\Actions\DeactivateFeature;
use Intrfce\FFFlags\Commands\Concerns\ResolvesFeatureFromDiscovery;
use Intrfce\FFFlags\FeatureFlagDiscovery;
use Intrfce\FFFlags\ManagedFeatureFlag;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'feature:deactivate')]
class DeactivateFeatureCommand extends Command
{
    use ResolvesFeatureFromDiscovery;

    protected $signature = 'feature:deactivate {feature} {--any=} {--all} {--replace}';

    protected $description = 'Deactivate a managed feature flag for specific model IDs or all';

    public function handle(FeatureFlagDiscovery $discovery, DeactivateFeature $action): int
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

        if ($this->option('all')) {
            if (! $this->confirm('Are you sure you want to deactivate this feature for all models?')) {
                $this->info('Cancelled.');
                return self::SUCCESS;
            }

            $action->handle($feature, all: true);

            $this->info('Feature ['.$feature->getSlug().'] deactivated for all models.');

            return self::SUCCESS;
        }

        $any = $this->option('any');

        if ($any === null) {
            $this->error('You must provide --any with a comma-separated list of model IDs, or use --all.');
            return self::FAILURE;
        }

        $ids = array_map('intval', explode(',', $any));

        $action->handle($feature, $ids);

        $this->info('Feature ['.$feature->getSlug().'] deactivated for model IDs: '.implode(', ', $ids));

        return self::SUCCESS;
    }
}
