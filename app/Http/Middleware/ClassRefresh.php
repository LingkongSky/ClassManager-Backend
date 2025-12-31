<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ClassModel;

class ClassRefresh
{
    public function handle(Request $request, Closure $next)
    {
        ClassModel::updateClassStatus();

        return $next($request);
    }
}
