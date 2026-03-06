<?php

namespace Intrfce\FFFlags;

use Illuminate\Support\Collection;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class FeatureFlagDiscovery
{
    /** @var Collection<int, DiscoveredFeatureFlag>|null */
    protected ?Collection $discovered = null;

    public function __construct(
        protected array $directories = [],
        protected array $classes = [],
    ) {}

    /**
     * @return Collection<int, DiscoveredFeatureFlag>
     */
    public function discover(): Collection
    {
        if ($this->discovered !== null) {
            return $this->discovered;
        }

        $featureClasses = collect();

        foreach ($this->directories as $directory) {
            $absolutePath = str_starts_with($directory, '/') ? $directory : base_path($directory);

            if (! is_dir($absolutePath)) {
                continue;
            }

            $finder = new Finder();
            $finder->files()->name('*.php')->in($absolutePath);

            foreach ($finder as $file) {
                $className = $this->classNameFromFile($file->getRealPath());

                if ($className !== null && $this->isFeatureFlagClass($className)) {
                    $featureClasses->push($className);
                }
            }
        }

        foreach ($this->classes as $className) {
            if (class_exists($className) && $this->isFeatureFlagClass($className)) {
                $featureClasses->push($className);
            }
        }

        $this->discovered = $featureClasses
            ->unique()
            ->map(fn (string $className) => DiscoveredFeatureFlag::fromClass($className))
            ->sortBy('name')
            ->values();

        return $this->discovered;
    }

    /**
     * @return array<string, list<class-string<FeatureFlag>>> Slugs that appear more than once, mapped to their classes.
     */
    public function findDuplicateSlugs(): array
    {
        $slugMap = [];

        foreach ($this->discover() as $flag) {
            $slugMap[$flag->slug][] = $flag->class;
        }

        return array_filter($slugMap, fn (array $classes) => count($classes) > 1);
    }

    public function flush(): void
    {
        $this->discovered = null;
    }

    protected function isFeatureFlagClass(string $className): bool
    {
        if (! class_exists($className)) {
            return false;
        }

        $ref = new ReflectionClass($className);

        return $ref->isSubclassOf(FeatureFlag::class) && ! $ref->isAbstract();
    }

    protected function classNameFromFile(string $filePath): ?string
    {
        $contents = file_get_contents($filePath);
        $namespace = '';
        $class = '';

        $tokens = token_get_all($contents);
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = '';
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j] === ';' || $tokens[$j] === '{') {
                        break;
                    }
                    if (in_array($tokens[$j][0], [T_NAME_QUALIFIED, T_STRING])) {
                        $namespace .= $tokens[$j][1];
                    }
                }
            }

            if ($tokens[$i][0] === T_CLASS) {
                if (isset($tokens[$i - 1]) && $tokens[$i - 1][0] === T_DOUBLE_COLON) {
                    continue;
                }
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j][0] === T_STRING) {
                        $class = $tokens[$j][1];
                        break 2;
                    }
                }
            }
        }

        if ($class === '') {
            return null;
        }

        $fqcn = $namespace !== '' ? $namespace.'\\'.$class : $class;

        return class_exists($fqcn) ? $fqcn : null;
    }
}
