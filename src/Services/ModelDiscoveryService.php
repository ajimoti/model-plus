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

    public function getDisplayColumnForModel(Model $model): ?string
    {
        // Core identifying patterns in order of preference
        $searchPatterns = [
            'name',      // Will match: name, full_name, first_name, last_name, display_name, etc.
            'title',     // Will match: title, post_title, job_title, etc.
            'label',     // Will match: label, menu_label, etc.
            'heading',   // Will match: heading, sub_heading, etc.
            'subject',   // Will match: subject, email_subject, etc.
            'code',      // Will match: code, product_code, reference_code, etc.
            'number',    // Will match: number, phone_number, order_number, etc.
            'email',     // Will match: email, email_address, etc.
            'username',  // Will match: username, user_name, etc.
            'handle',    // Will match: handle, twitter_handle, etc.
            'slug',      // Will match: slug, url_slug, etc.
            'sku',       // Will match: sku, product_sku, etc.
            'city',      // Will match: city, city_name, etc.
            'country',   // Will match: country, country_name, etc.
            'description', // Will match: description, short_description, etc.
            'identifier' // Will match: identifier, unique_identifier, etc.
        ];

        // Columns we should skip
        $excludedColumns = [
            'id', 'uuid', 'guid', 'password', 'remember_token', 'email_verified_at',
            'created_at', 'updated_at', 'deleted_at', 'meta', 'settings', 'preferences',
            'data', 'attributes', 'properties', 'token', 'hash', 'key', 'secret'
        ];

        $columns = $model->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($model->getTable());

        // First pass: Look for columns containing our preferred patterns
        foreach ($searchPatterns as $pattern) {
            $matchingColumns = array_filter($columns, function($column) use ($pattern, $excludedColumns) {
                // Skip excluded columns
                if (in_array($column, $excludedColumns)) {
                    return false;
                }
                
                // Check if the column contains our search pattern
                return str_contains(strtolower($column), $pattern);
            });

            if (!empty($matchingColumns)) {
                // Get the first matching column that's a string type
                foreach ($matchingColumns as $column) {
                    $type = $model->getConnection()
                        ->getSchemaBuilder()
                        ->getColumnType($model->getTable(), $column);

                    if (in_array($type, ['string', 'text', 'char', 'varchar'])) {
                        return $column;
                    }
                }
            }
        }

        // Second pass: Fall back to any string column not in excluded list
        foreach ($columns as $column) {
            if (in_array($column, $excludedColumns)) {
                continue;
            }

            $type = $model->getConnection()
                ->getSchemaBuilder()
                ->getColumnType($model->getTable(), $column);

            if (in_array($type, ['string', 'text', 'char', 'varchar'])) {
                return $column;
            }
        }

        // Last resort: Return null if no suitable column found
        return null;
    }
} 