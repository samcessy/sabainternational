<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces mandatory MFA for every admin route, per saba.md §10.4 and
 * docs/architecture/authentication.md §3. Fortify's TOTP feature exists but
 * doesn't itself require enrollment before granting access — this middleware
 * is what makes MFA actually mandatory rather than a skippable setting.
 */
class EnsureTwoFactorEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasConfirmedTwoFactor()) {
            Inertia::flash('toast', [
                'type' => 'info',
                'message' => 'Two-factor authentication is required for admin accounts. Please finish setting it up to continue.',
            ]);

            return Redirect::route('security.edit');
        }

        return $next($request);
    }
}
