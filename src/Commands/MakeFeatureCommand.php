<?php

namespace Intrfce\FFFlags\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Intrfce\FFFlags\FeatureFlagDiscovery;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:feature')]
class MakeFeatureCommand extends GeneratorCommand
{
    protected $name = 'make:feature';

    protected $description = 'Create a new feature flag class';

    protected $type = 'Feature';

    public function handle(): ?bool
    {
        $slug = Str::kebab(class_basename($this->getNameInput()));

        $discovery = $this->laravel->make(FeatureFlagDiscovery::class);
        $existing = $discovery->discover()->firstWhere('slug', $slug);

        if ($existing !== null) {
            $this->error("A feature flag with the slug [{$slug}] already exists: {$existing->class}");

            return false;
        }

        return parent::handle();
    }

    protected function getStub(): string
    {
        if ($this->option('managed')) {
            $customPath = $this->laravel->basePath('stubs/managed-feature.stub');

            return file_exists($customPath)
                ? $customPath
                : __DIR__.'/../../stubs/managed-feature.stub';
        }

        $customPath = $this->laravel->basePath('stubs/feature.stub');

        return file_exists($customPath)
            ? $customPath
            : __DIR__.'/../../stubs/feature.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Features';
    }

    protected function buildClass($name): string
    {
        $class = parent::buildClass($name);

        $className = class_basename($name);
        $class = str_replace('{{ slug }}', Str::kebab($className), $class);

        if ($this->option('managed') && $this->option('model')) {
            $model = $this->option('model');
            $class = str_replace('{{ model }}', class_basename($model), $class);

            if (! Str::startsWith($model, '\\')) {
                $model = '\\'.$model;
            }

            $class = str_replace(
                "use Intrfce\\FFFlags\\ManagedFeatureFlag;\n",
                "use Intrfce\\FFFlags\\ManagedFeatureFlag;\nuse {$model};\n",
                $class,
            );
        } else {
            $class = str_replace('{{ model }}', 'YourModel', $class);
        }

        return $class;
    }

    protected function getOptions(): array
    {
        return [
            ['managed', null, InputOption::VALUE_NONE, 'Create a managed feature flag (database-driven)'],
            ['model', null, InputOption::VALUE_OPTIONAL, 'The Eloquent model class for the managed feature flag'],
        ];
    }
}
