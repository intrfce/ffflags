<?php

namespace Intrfce\FFFlags\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:feature')]
class MakeFeatureCommand extends GeneratorCommand
{
    protected $name = 'make:feature';

    protected $description = 'Create a new feature flag class';

    protected $type = 'Feature';

    protected function getStub(): string
    {
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

        return str_replace('{{ slug }}', Str::kebab($className), $class);
    }
}
