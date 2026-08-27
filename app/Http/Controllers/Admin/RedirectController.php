<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRedirectRequest;
use App\Http\Requests\Admin\UpdateRedirectRequest;
use App\Models\Redirect;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RedirectController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Redirect::class);

        return Inertia::render('admin/redirects/Index', [
            'redirects' => Redirect::query()
                ->orderBy('from_path')
                ->paginate(20)
                ->through(fn (Redirect $redirect) => [
                    'id' => $redirect->id,
                    'from_path' => $redirect->from_path,
                    'to_path' => $redirect->to_path,
                    'status_code' => $redirect->status_code,
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Redirect::class);

        return Inertia::render('admin/redirects/Create');
    }

    public function store(StoreRedirectRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $redirect = Redirect::create($data);

        $this->auditLogger->log($request->user(), 'create', $redirect, newValues: $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Redirect from \"{$redirect->from_path}\" was created.",
        ]);

        return to_route('admin.redirects.index');
    }

    public function edit(Redirect $redirect): Response
    {
        $this->authorize('update', $redirect);

        return Inertia::render('admin/redirects/Edit', [
            'redirect' => $redirect->only(['id', 'from_path', 'to_path', 'status_code']),
        ]);
    }

    public function update(UpdateRedirectRequest $request, Redirect $redirect): RedirectResponse
    {
        $oldValues = $redirect->only(array_keys($request->validated()));
        $data = $request->validated();

        $redirect->update($data);

        $this->auditLogger->log($request->user(), 'update', $redirect, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Redirect from \"{$redirect->from_path}\" was updated.",
        ]);

        return to_route('admin.redirects.index');
    }

    public function destroy(Redirect $redirect): RedirectResponse
    {
        $this->authorize('delete', $redirect);

        $fromPath = $redirect->from_path;
        $this->auditLogger->log(request()->user(), 'delete', $redirect, oldValues: $redirect->only(['from_path', 'to_path', 'status_code']));
        $redirect->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Redirect from \"{$fromPath}\" was deleted.",
        ]);

        return to_route('admin.redirects.index');
    }
}
