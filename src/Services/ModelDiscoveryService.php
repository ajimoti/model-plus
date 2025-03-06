<?php

declare(strict_types=1);

namespace Vendor\ModelPlus\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
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

    public function getModelRelationships(string $modelClass): array
    {
        // First check if table exists
        if (!$this->tableExists($modelClass)) {
            return [
                'error' => 'table_not_found',
                'table' => (new $modelClass)->getTable()
            ];
        }

        $relationships = [];
        $reflection = new ReflectionClass($modelClass);
        
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getNumberOfParameters() > 0) {
                continue;
            }

            try {
                $returnType = $method->getReturnType();
                if (!$returnType) {
                    continue;
                }

                $returnTypeName = $returnType->getName();
                if (!is_subclass_of($returnTypeName, Relation::class)) {
                    continue;
                }

                $relationships[$method->getName()] = [
                    'type' => class_basename($returnTypeName),
                    'method' => $method->getName(),
                ];
            } catch (\Throwable) {
                continue;
            }
        }

        // Map foreign keys to relationship methods
        $foreignKeyMap = $this->mapForeignKeyToRelationship($modelClass, $relationships);
        
        // Add the mapping to the relationships array
        foreach ($relationships as $method => &$info) {
            $info['foreign_key'] = array_search($method, $foreignKeyMap) ?: null;
        }

        // Add inverse mapping for easy lookup
        return [
            'methods' => $relationships,
            'foreign_keys' => $foreignKeyMap
        ];
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

    private function getFirstStringColumn(Model $model): ?string
    {
        $columns = $model->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($model->getTable());

        foreach ($columns as $column) {
            $type = $model->getConnection()
                ->getSchemaBuilder()
                ->getColumnType($model->getTable(), $column);
            
            if (in_array($type, ['string', 'text']) && !in_array($column, ['password', 'remember_token'])) {
                return $column;
            }
        }

        return null;
    }

    private function mapForeignKeyToRelationship(string $modelClass, array $relationships): array
    {
        $mapping = [];
        $reflection = new ReflectionClass($modelClass);
        $instance = new $modelClass;

        foreach ($relationships as $methodName => $info) {
            try {
                $method = $reflection->getMethod($methodName);
                $relation = $instance->{$methodName}();
                
                // Get the foreign key from the relation
                $foreignKey = match (true) {
                    $relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo => $relation->getForeignKeyName(),
                    $relation instanceof \Illuminate\Database\Eloquent\Relations\HasOne => $relation->getForeignKeyName(),
                    $relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany => $relation->getForeignKeyName(),
                    default => null
                };

                if ($foreignKey) {
                    $mapping[$foreignKey] = $methodName;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $mapping;
    }

    private function tableExists(string $modelClass): bool
    {
        try {
            $model = new $modelClass;
            return $model->getConnection()
                ->getSchemaBuilder()
                ->hasTable($model->getTable());
        } catch (\Throwable) {
            return false;
        }
    }
} 