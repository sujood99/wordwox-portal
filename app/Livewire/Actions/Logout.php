<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     * Handles both CMS admin (web/cms guard) and customer (customer guard) logouts.
     */
    public function __invoke()
    {
        // Logout from all guards (CMS admin and customer)
        if (Auth::guard('cms')->check()) {
            Auth::guard('cms')->logout();
        }
        if (Auth::guard('customer')->check()) {
            Auth::guard('customer')->logout();
        }
        // Also logout from default web guard for backward compatibility
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect('/');
    }
}
