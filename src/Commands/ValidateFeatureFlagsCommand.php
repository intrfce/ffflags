<?php

namespace Intrfce\FFFlags\Commands;

use Illuminate\Console\Command;
use Intrfce\FFFlags\FeatureFlagDiscovery;
use Intrfce\FFFlags\ManagedFeatureFlag;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'ffflags:validate')]
class ValidateFeatureFlagsCommand extends Command
{
    protected $signature = 'ffflags:validate';

    protected $description = 'Validate all discovered feature flags and report any issues';

    public function handle(FeatureFlagDiscovery $discovery): int
    {
        $features = $discovery->discover();

        if ($features->isEmpty()) {
            $this->warn('No feature flags discovered.');

            return self::SUCCESS;
        }

        $codeResolved = $features->filter(fn ($f) => ! $f->isManaged);
        $managed = $features->filter(fn ($f) => $f->isManaged);

        $this->info('Feature Flags Summary');
        $this->line("  Total:         {$features->count()}");
        $this->line("  Code-resolved: {$codeResolved->count()}");
        $this->line("  Managed:       {$managed->count()}");
        $this->newLine();

        $hasErrors = false;

        // Check for duplicate slugs.
        $duplicates = $discovery->findDuplicateSlugs();

        if (! empty($duplicates)) {
            $hasErrors = true;
            $this->error('Duplicate slugs detected:');

            foreach ($duplicates as $slug => $classes) {
                $this->line("  [{$slug}] is used by:");
                foreach ($classes as $class) {
                    $this->line("    - {$class}");
                }
            }

            $this->newLine();
        }

        // Check code-resolved features have a resolve() method.
        $missingResolve = $codeResolved->filter(function ($flag) {
            return ! method_exists($flag->class, 'resolve');
        });

        if ($missingResolve->isNotEmpty()) {
            $hasErrors = true;
            $this->error('Code-resolved features missing resolve() method:');

            foreach ($missingResolve as $flag) {
                $this->line("  - {$flag->class} [{$flag->slug}]");
            }

            $this->newLine();
        }

        // Check managed features have a #[Model] attribute.
        $missingModel = $managed->filter(fn ($flag) => $flag->modelClass === null);

        if ($missingModel->isNotEmpty()) {
            $hasErrors = true;
            $this->error('Managed features missing #[Model] attribute:');

            foreach ($missingModel as $flag) {
                $this->line("  - {$flag->class} [{$flag->slug}]");
            }

            $this->newLine();
        }

        if ($hasErrors) {
            $this->error('Validation failed.');

            return self::FAILURE;
        }

        $this->info('All checks passed.');

        return self::SUCCESS;
    }
}
