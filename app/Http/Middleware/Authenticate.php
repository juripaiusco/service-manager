<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        /**
         * Se non sono presenti utenti, viene mostrato direttamente la pagina di registrazione
         */
        $users = \App\Models\User::count();

        return $request->expectsJson() ? null : route($users > 0 ? 'login' : 'register');
    }
}
