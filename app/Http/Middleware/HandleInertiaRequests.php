<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                // Derived here, not duplicated as a role->permission matrix
                // in the frontend, so AdminRole::permissions() stays the
                // single source of truth (saba.md §10.2).
                'permissions' => $user?->admin_role !== null
                    ? array_map(fn ($permission) => $permission->value, $user->admin_role->permissions())
                    : [],
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            // Canonical-URL default for the Seo component - individual
            // pages can still pass their own `canonical` prop when the
            // current request URL isn't the right one to index (e.g. a
            // filtered listing).
            'url' => $request->url(),
        ];
    }
}
