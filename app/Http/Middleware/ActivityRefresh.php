<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Activity;

class ActivityRefresh
{
    public function handle(Request $request, Closure $next)
    {
        Activity::updateActivityStatus();
        
        return $next($request);
    }
}