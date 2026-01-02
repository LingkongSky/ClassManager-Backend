<?php

namespace App\Http\Middleware;

class AdminAuth extends BaseAuthMiddleware
{
    protected function checkLevel($user): bool
    {
        return $user->level >= 5;
    }
}
