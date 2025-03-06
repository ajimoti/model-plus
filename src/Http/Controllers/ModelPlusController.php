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

    public function show(Request $request, string $model): mixed
    {
        $modelClass = $this->modelDiscovery->resolveModelClass($model);
        
        if (!$modelClass || !class_exists($modelClass)) {
            abort(404, 'Model not found');
        }

        // Get relationships first to check for table existence
        $relationships = $this->modelDiscovery->getModelRelationships($modelClass);

        // Handle missing table case
        if (isset($relationships['error']) && $relationships['error'] === 'table_not_found') {
            $viewData = [
                'model' => $modelClass,
                'modelName' => Str::title(Str::snake(class_basename($modelClass), ' ')),
                'error' => 'table_not_found',
                'table' => $relationships['table'],
                'models' => $this->modelDiscovery->getModels(),
                'modelMap' => $this->modelDiscovery->getModelMap(),
                'title' => Str::title(class_basename($modelClass)),
            ];

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
            $query->orderBy($sortColumn, $sortDirection);
        }

        // Detect and eager load relationships
        if (!empty($relationships['methods'])) {
            $query->with(array_keys($relationships['methods']));
        }

        $records = $query->paginate(
            Config::get('modelplus.pagination.per_page', 15)
        )->withQueryString(); // Important: Keep sort parameters in pagination links

        $viewData = [
            'model' => $modelClass,
            'modelName' => Str::title(Str::snake(class_basename($modelClass), ' ')),
            'records' => $records,
            'models' => $this->modelDiscovery->getModels(),
            'modelMap' => $this->modelDiscovery->getModelMap(),
            'title' => Str::title(class_basename($modelClass)),
            'relationships' => $relationships,
            'sortColumn' => $request->get('sort'),
            'sortDirection' => $request->get('direction', 'asc'),
        ];

        if ($request->get('partial') && $request->ajax()) {
            return View::make('modelplus::partials.table-rows', $viewData);
        }

        if ($request->get('partial')) {
            return View::make('modelplus::show-partial', $viewData);
        }

        return View::make('modelplus::show', $viewData);
    }
} 