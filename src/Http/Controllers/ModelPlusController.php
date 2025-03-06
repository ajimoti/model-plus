<?php

declare(strict_types=1);

namespace Vendor\ModelPlus\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Abort;
use Illuminate\View\View as ViewResponse;
use Illuminate\Support\Facades\App;
use Vendor\ModelPlus\Services\ModelDiscoveryService;
// use Illuminate\Support\Facades\Request;

final class ModelPlusController extends Controller
{
    public function __construct(
        private readonly ModelDiscoveryService $modelDiscovery
    ) {}

    public function index(): ViewResponse
    {
        return View::make('modelplus::index', [
            'models' => $this->modelDiscovery->getModels(),
            'modelMap' => $this->modelDiscovery->getModelMap(),
            'title' => 'Dashboard'
        ]);
    }

    public function show(Request $request, string $model)
    {
        $modelClass = $this->modelDiscovery->resolveModelClass($model);
        
        if (!$modelClass || !class_exists($modelClass)) {
            abort(404, 'Model not found');
        }

        // Get relationships first to check for table existence
        $relationships = $this->modelDiscovery->getModelRelationships($modelClass);

        $viewData = [
            'model' => $modelClass,
            'modelName' => Str::title(str_replace('_', ' ', class_basename($modelClass))),
            'models' => $this->modelDiscovery->getModels(),
            'modelMap' => $this->modelDiscovery->getModelMap(),
            'relationships' => $relationships,
            'sortColumn' => $request->get('sort'),
            'sortDirection' => $request->get('direction', 'asc'),
            'title' => Str::title(class_basename($modelClass)),
        ];

        // Handle missing table case
        if (isset($relationships['error']) && $relationships['error'] === 'table_not_found') {
            $viewData['error'] = 'table_not_found';
            $viewData['table'] = $relationships['table'];

            if ($request->get('partial')) {
                return View::make('modelplus::show-partial', $viewData);
            }

            return View::make('modelplus::show', $viewData);
        }

        $query = $modelClass::query();
        
        // Handle search
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            // Implementation depends on your requirements
        }

        // Handle sorting
        if ($request->has('sort')) {
            $sortColumn = $request->get('sort');
            $sortDirection = $request->get('direction', 'asc');
            
            // Check if the column is a relationship
            if (isset($relationships['foreign_keys'][$sortColumn])) {
                $relationMethod = $relationships['foreign_keys'][$sortColumn];
                
                // Create a temporary model instance to get the relationship
                $tempModel = new $modelClass();
                $relation = $tempModel->{$relationMethod}();
                
                $relatedModel = $relation->getRelated();
                $relatedTable = $relatedModel->getTable();
                $localKey = $relation->getQualifiedForeignKeyName();
                
                // Join the related table and sort by its display column
                $displayColumn = $this->modelDiscovery->getDisplayColumnForModel($relatedModel);
                $query->join($relatedTable, $localKey, '=', $relatedTable . '.id')
                      ->orderBy($relatedTable . '.' . $displayColumn, $sortDirection)
                      ->select($tempModel->getTable() . '.*');
            } else {
                $query->orderBy($sortColumn, $sortDirection);
            }
        }

        // Detect and eager load relationships
        if (!empty($relationships['methods'])) {
            $query->with(array_keys($relationships['methods']));
        }

        $viewData['records'] = $query->paginate(
            Config::get('modelplus.pagination.per_page', 15)
        )->withQueryString();

        if ($request->get('partial')) {
            return View::make('modelplus::show-partial', $viewData);
        }

        return View::make('modelplus::show', $viewData);
    }
} 