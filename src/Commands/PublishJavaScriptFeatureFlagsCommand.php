<?php

namespace Intrfce\FFFlags\Commands;

use Illuminate\Console\Command;
use Intrfce\FFFlags\FeatureFlagDiscovery;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'ffflags:publish-js')]
class PublishJavaScriptFeatureFlagsCommand extends Command
{
    protected $signature = 'ffflags:publish-js';

    protected $description = 'Publish feature flags as a JavaScript file with named exports';

    public function handle(FeatureFlagDiscovery $discovery): int
    {
        $outputDir = config('ffflags.js_usage.output_directory', 'resources/js/enums');
        $basename = config('ffflags.js_usage.filename', 'flags');
        $format = config('ffflags.js_usage.format', 'js');

        $absoluteDir = str_starts_with($outputDir, '/') ? $outputDir : base_path($outputDir);
        $outputPath = $absoluteDir.'/'.$basename.'.'.$format;

        if (! is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $flags = $discovery->discover();

        if ($flags->isEmpty()) {
            $this->warn('No feature flags discovered. Nothing to publish.');

            return self::SUCCESS;
        }

        $typescript = $format === 'ts';

        $exports = $flags->map(function ($flag) use ($typescript) {
            $exportName = class_basename($flag->class).'FeatureFlag';
            $slug = addslashes($flag->slug);
            $name = addslashes($flag->name);

            $typeAnnotation = $typescript ? ': FeatureFlag' : '';

            return <<<JS
            export const {$exportName}{$typeAnnotation} = {
                slug() { return '{$slug}'; },
                name() { return '{$name}'; },
            };
            JS;
        });

        $parts = collect();

        if ($typescript) {
            $parts->push(<<<'TS'
            interface FeatureFlag {
                slug(): string;
                name(): string;
            }
            TS);
        }

        $parts->push($exports->implode("\n\n"));

        $content = $parts->implode("\n\n")."\n";

        file_put_contents($outputPath, $content);

        $this->info("Feature flags published to {$outputPath}");

        return self::SUCCESS;
    }
}
