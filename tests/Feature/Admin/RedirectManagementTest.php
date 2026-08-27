<?php

use App\Enums\AdminRole;
use App\Models\Redirect;
use Inertia\Testing\AssertableInertia as Assert;

function validRedirectPayload(array $overrides = []): array
{
    return array_merge([
        'from_path' => '/old-programs',
        'to_path' => '/programs',
        'status_code' => 301,
    ], $overrides);
}

test('an editor can view the redirects index', function () {
    Redirect::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.redirects.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/redirects/Index')
        ->has('redirects.data', 1)
    );
});

test('a viewer can view but not manage redirects', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.redirects.index'))->assertOk();
    $this->actingAs($viewer)->post(route('admin.redirects.store'), validRedirectPayload())->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.redirects.index'))->assertRedirect(route('login'));
});

test('an editor can create a redirect and it is audit logged', function () {
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->post(route('admin.redirects.store'), validRedirectPayload());

    $response->assertRedirect(route('admin.redirects.index'));
    $redirect = Redirect::query()->where('from_path', '/old-programs')->firstOrFail();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'create',
        'entity_type' => 'redirect',
        'entity_id' => $redirect->id,
    ]);
});

test('a duplicate from_path fails validation', function () {
    Redirect::factory()->create(['from_path' => '/old-programs']);
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.redirects.store'), validRedirectPayload())
        ->assertSessionHasErrors('from_path');
});

test('a from_path equal to its to_path fails validation', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.redirects.store'), validRedirectPayload(['to_path' => '/old-programs']))
        ->assertSessionHasErrors('to_path');
});

test('a path not starting with a slash fails validation', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.redirects.store'), validRedirectPayload(['from_path' => 'old-programs']))
        ->assertSessionHasErrors('from_path');
});

test('an editor can update a redirect and it is audit logged', function () {
    $redirect = Redirect::factory()->create(['to_path' => '/old-target']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->put(
        route('admin.redirects.update', $redirect),
        validRedirectPayload(['from_path' => $redirect->from_path, 'to_path' => '/new-target'])
    );

    $response->assertRedirect(route('admin.redirects.index'));
    $this->assertDatabaseHas('redirects', ['id' => $redirect->id, 'to_path' => '/new-target']);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'update',
        'entity_type' => 'redirect',
        'entity_id' => $redirect->id,
    ]);
});

test('a viewer cannot update or delete a redirect', function () {
    $redirect = Redirect::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)
        ->put(route('admin.redirects.update', $redirect), validRedirectPayload(['from_path' => $redirect->from_path]))
        ->assertForbidden();
    $this->actingAs($viewer)->delete(route('admin.redirects.destroy', $redirect))->assertForbidden();
});

test('an editor can delete a redirect and it is audit logged', function () {
    $redirect = Redirect::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->delete(route('admin.redirects.destroy', $redirect));

    $response->assertRedirect(route('admin.redirects.index'));
    $this->assertDatabaseMissing('redirects', ['id' => $redirect->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'redirect',
        'entity_id' => $redirect->id,
    ]);
});
