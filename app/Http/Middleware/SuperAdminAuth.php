<?php

namespace App\Http\Middleware;

class SuperAdminAuth extends BaseAuthMiddleware
{
    protected function checkLevel($user): bool
    {
        return $user->level >= 10;
    }
}