<?php

declare(strict_types=1);

namespace Vendor\ModelPlus\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

final class ModelDiscoveryService
{
    private const CACHE_KEY = 'modelplus.discovered_models';
    private const MODEL_MAP_CACHE_KEY = 'modelplus.model_map';

    public function __construct(
        private readonly array $modelPaths
    ) {}

    public function getModels(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY, 
            Carbon::now()->addHour(), 
            fn() => $this->discoverModels()
        );
    }

    public function getModelMap(): array
    {
        return Cache::remember(
            self::MODEL_MAP_CACHE_KEY,
            Carbon::now()->addHour(),
            fn() => $this->buildModelMap()
        );
    }

    public function resolveModelClass(string $slug): ?string
    {
        $map = $this->getModelMap();
        return array_search($slug, $map) ?: null;
    }

    private function buildModelMap(): array
    {
        $map = [];
        foreach ($this->getModels() as $modelClass) {
            $slug = Str::plural(Str::lower(class_basename($modelClass)));
            $map[$modelClass] = $slug;
        }
        return $map;
    }

    private function discoverModels(): Collection
    {
        $models = new Collection();

        foreach ($this->modelPaths as $path) {
            if (!File::exists($path)) {
                continue;
            }

            $finder = new Finder();
            $files = $finder->files()->in($path)->name('*.php');

            foreach ($files as $file) {
                $class = $this->getClassFromFile($file->getRealPath());
                
                if ($this->isValidModel($class)) {
                    $models->push($class);
                }
            }
        }

        return $models->sort();
    }

    private function getClassFromFile(string $path): ?string
    {
        $contents = File::get($path);
        if (preg_match('/namespace\s+(.+?);/i', $contents, $matches)) {
            $namespace = $matches[1];
            if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
                return $namespace . '\\' . $matches[1];
            }
        }
        return null;
    }

    private function isValidModel(?string $class): bool
    {
        if (!$class) {
            return false;
        }

        try {
            $reflection = new ReflectionClass($class);
            return !$reflection->isAbstract() 
                && $reflection->isSubclassOf(Model::class);
        } catch (\Throwable) {
            return false;
        }
    }
} 