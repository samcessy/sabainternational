<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public self-registration is deliberately disabled (config/fortify.php's
 * features list has no Features::registration()) - this is the only way
 * admin accounts get provisioned. Creating a user here sets an unusable
 * random password and emails a Fortify password-reset link rather than
 * exposing a password field, so no one but the new user ever knows their
 * password. The admin vouches for the email by typing it in, so it's
 * marked verified immediately instead of also requiring email verification
 * on top of the reset-link flow.
 */
class UserController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('admin/users/Index', [
            'users' => User::query()
                ->orderBy('name')
                ->paginate(20)
                ->through(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'admin_role' => $user->admin_role?->value,
                    'admin_role_label' => $user->admin_role?->label(),
                    'two_factor_enabled' => $user->hasConfirmedTwoFactor(),
                    'created_at' => $user->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('admin/users/Create', [
            'roleOptions' => AdminRole::options(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            ...$data,
            'password' => Hash::make(Str::random(40)),
        ]);

        // email_verified_at isn't mass-assignable (by design - it's not
        // something a request should ever set directly), but this is a
        // trusted admin action vouching for the address, not user input.
        $user->forceFill(['email_verified_at' => now()])->save();

        Password::sendResetLink(['email' => $user->email]);

        $this->auditLogger->log($request->user(), 'create', $user, newValues: $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$user->name}\" was created and sent a link to set their password.",
        ]);

        return to_route('admin.users.index');
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('admin/users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'admin_role' => $user->admin_role?->value,
            ],
            'roleOptions' => AdminRole::options(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $oldValues = $user->only(array_keys($request->validated()));
        $data = $request->validated();

        $user->update($data);

        $this->auditLogger->log($request->user(), 'update', $user, $oldValues, $data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$user->name}\" was updated.",
        ]);

        return to_route('admin.users.index');
    }

    public function sendPasswordReset(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $status = Password::sendResetLink(['email' => $user->email]);

        $this->auditLogger->log(request()->user(), 'send-password-reset', $user);

        Inertia::flash('toast', [
            'type' => $status === Password::RESET_LINK_SENT ? 'success' : 'error',
            'message' => $status === Password::RESET_LINK_SENT
                ? "A password reset link was sent to {$user->email}."
                : 'Could not send the reset link. Please try again.',
        ]);

        return back();
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $name = $user->name;
        $this->auditLogger->log(request()->user(), 'delete', $user, oldValues: $user->only(['name', 'email', 'admin_role']));
        $user->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$name}\" was deleted.",
        ]);

        return to_route('admin.users.index');
    }
}
