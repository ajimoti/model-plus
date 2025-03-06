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

        $query = $modelClass::query();
        
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            // Implementation depends on your requirements
        }

        // Detect and eager load relationships
        $relationships = $this->modelDiscovery->getModelRelationships($modelClass);

        if (!empty($relationships['methods'])) {
            $query->with(array_keys($relationships['methods']));
        }

        $records = $query->paginate(
            Config::get('modelplus.pagination.per_page', 15)
        );

        $viewData = [
            'model' => $modelClass,
            'modelName' => Str::title(Str::snake(class_basename($modelClass), ' ')),
            'records' => $records,
            'models' => $this->modelDiscovery->getModels(),
            'modelMap' => $this->modelDiscovery->getModelMap(),
            'title' => Str::title(class_basename($modelClass)),
            'relationships' => $relationships,
        ];

        // Return only the content portion for AJAX requests
        if ($request->get('partial')) {
            return View::make('modelplus::show-partial', $viewData);
        }

        return View::make('modelplus::show', $viewData);
    }
} 