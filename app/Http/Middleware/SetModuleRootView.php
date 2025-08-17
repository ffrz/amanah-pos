<?php

// App/Http/Middleware/SetModuleRootView.php
namespace App\Http\Middleware;

use Closure;
use Inertia\Inertia;

class SetModuleRootView
{
    public function handle($request, Closure $next)
    {
        $firstSegment = strtolower($request->segment(1) ?? 'app');

        $viewFile = resource_path("views/modules/{$firstSegment}/app.blade.php");
        if (file_exists($viewFile)) {
            $request->attributes->set('module_root_view', $firstSegment);
            Inertia::share('currentModule', $firstSegment);
        } else {
            Inertia::share('currentModule', 'app');
        }

        return $next($request);
    }
}
