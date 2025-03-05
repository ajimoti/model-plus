<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Vendor\ModelPlus\Http\Controllers\ModelPlusController;

Route::middleware(Config::get('modelplus.route.middleware', ['web', 'auth']))
    ->prefix(Config::get('modelplus.route.prefix', 'modelplus'))
    ->group(function () {
        Route::get('/', [ModelPlusController::class, 'index'])->name('modelplus.index');
        Route::get('/model/{model}', [ModelPlusController::class, 'show'])->name('modelplus.show');
    }); 