<?php

declare(strict_types=1);

namespace Vendor\ModelPlus\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Abort;

final class ModelPlusAccess
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!Config::get('app.debug') && !$request->user()?->can('access-modelplus')) {
            Abort::throw(403, 'Unauthorized access to ModelPlus.');
        }

        return $next($request);
    }
} 