<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;

return [
    /*
    |--------------------------------------------------------------------------
    | Model Paths
    |--------------------------------------------------------------------------
    |
    | Define the paths where your Eloquent models are located. ModelPlus will
    | scan these directories to discover your models.
    |
    */
    'model_paths' => [
        App::path('Models'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the route prefix and middleware for ModelPlus.
    |
    */
    'route' => [
        'prefix' => 'modelplus',
        'middleware' => ['web'],
        'require_auth' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Hidden Fields
    |--------------------------------------------------------------------------
    |
    | Specify fields that should be hidden by default when displaying models.
    |
    */
    'hidden_fields' => [
        'password',
        'remember_token',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default pagination settings for model listings.
    |
    */
    'pagination' => [
        'per_page' => 15,
        'per_page_options' => [15, 30, 50, 100],
    ],
]; 