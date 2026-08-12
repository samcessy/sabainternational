<?php

use App\Enums\AdminRole;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

test('a super administrator can view the users index', function () {
    User::factory()->count(2)->create();
    $superAdmin = actingAsAdmin(AdminRole::SuperAdministrator);

    $response = $this->actingAs($superAdmin)->get(route('admin.users.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/users/Index')
        ->has('users.data', 3) // the 2 factory users + $superAdmin itself
    );
});

test('an editor cannot view the users index', function () {
    $editor = actingAsAdmin(AdminRole::Editor);

    $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();
});

test('a viewer cannot view the users index', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.users.index'))->assertForbidden();
});

test('a finance manager cannot view the users index', function () {
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $this->actingAs($financeManager)->get(route('admin.users.index'))->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.users.index'))->assertRedirect(route('login'));
});

test('creating a user sends a password reset link and marks the email verified, without a password field ever appearing', function () {
    Notification::fake();
    $superAdmin = actingAsAdmin(AdminRole::SuperAdministrator);

    $response = $this->actingAs($superAdmin)->post(route('admin.users.store'), [
        'name' => 'New Editor',
        'email' => 'new-editor@example.com',
        'admin_role' => AdminRole::Editor->value,
    ]);

    $response->assertRedirect(route('admin.users.index'));

    $user = User::query()->where('email', 'new-editor@example.com')->firstOrFail();
    expect($user->email_verified_at)->not->toBeNull()
        ->and($user->admin_role)->toBe(AdminRole::Editor);

    Notification::assertSentTo($user, ResetPassword::class);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $superAdmin->id,
        'action' => 'create',
        'entity_type' => 'user',
        'entity_id' => $user->id,
    ]);
});

test('an editor cannot create a user', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->post(route('admin.users.store'), [
        'name' => 'New Editor',
        'email' => 'new-editor@example.com',
        'admin_role' => AdminRole::Editor->value,
    ])->assertForbidden();
});

test('a super administrator can update another user and it is audit logged', function () {
    $target = User::factory()->create(['name' => 'Old Name', 'admin_role' => AdminRole::Viewer]);
    $superAdmin = actingAsAdmin(AdminRole::SuperAdministrator);

    $response = $this->actingAs($superAdmin)->put(route('admin.users.update', $target), [
        'name' => 'New Name',
        'email' => $target->email,
        'admin_role' => AdminRole::Editor->value,
    ]);

    $response->assertRedirect(route('admin.users.index'));
    $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'New Name', 'admin_role' => 'editor']);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $superAdmin->id,
        'action' => 'update',
        'entity_type' => 'user',
        'entity_id' => $target->id,
    ]);
});

test('a super administrator can send a password reset link to another user and it is audit logged', function () {
    Notification::fake();
    $target = User::factory()->create();
    $superAdmin = actingAsAdmin(AdminRole::SuperAdministrator);

    $response = $this->actingAs($superAdmin)->post(route('admin.users.send-password-reset', $target));

    $response->assertRedirect();
    Notification::assertSentTo($target, ResetPassword::class);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $superAdmin->id,
        'action' => 'send-password-reset',
        'entity_type' => 'user',
        'entity_id' => $target->id,
    ]);
});

test('a super administrator can delete another user and it is audit logged', function () {
    $target = User::factory()->create();
    $superAdmin = actingAsAdmin(AdminRole::SuperAdministrator);

    $response = $this->actingAs($superAdmin)->delete(route('admin.users.destroy', $target));

    $response->assertRedirect(route('admin.users.index'));
    $this->assertSoftDeleted('users', ['id' => $target->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $superAdmin->id,
        'action' => 'delete',
        'entity_type' => 'user',
        'entity_id' => $target->id,
    ]);
});

test('a super administrator cannot delete their own account via the destroy route', function () {
    $superAdmin = actingAsAdmin(AdminRole::SuperAdministrator);

    $this->actingAs($superAdmin)->delete(route('admin.users.destroy', $superAdmin))->assertForbidden();
});
