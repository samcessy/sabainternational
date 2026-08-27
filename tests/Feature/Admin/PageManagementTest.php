<?php

use App\Enums\AdminRole;
use App\Enums\ContentStatus;
use App\Models\Page;
use Inertia\Testing\AssertableInertia as Assert;

function validPagePayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Our Mission',
        'slug' => 'our-mission',
        'body' => null,
        'seo_title' => null,
        'seo_description' => null,
        'og_image' => null,
        'status' => ContentStatus::Draft->value,
    ], $overrides);
}

test('an editor can view the pages index', function () {
    Page::factory()->create(['title' => 'A Page']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.pages.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/pages/Index')
        ->has('pages.data', 1)
    );
});

test('a viewer can view but not manage pages', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.pages.index'))->assertOk();
    $this->actingAs($viewer)->post(route('admin.pages.store'), validPagePayload())->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.pages.index'))->assertRedirect(route('login'));
});

test('an editor can create a page and it is audit logged', function () {
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->post(route('admin.pages.store'), validPagePayload());

    $response->assertRedirect(route('admin.pages.index'));
    $page = Page::query()->where('slug', 'our-mission')->firstOrFail();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'create',
        'entity_type' => 'page',
        'entity_id' => $page->id,
    ]);
});

test('publishing a page on creation sets published_at', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->post(route('admin.pages.store'), validPagePayload([
        'status' => ContentStatus::Published->value,
    ]));

    $page = Page::query()->where('slug', 'our-mission')->firstOrFail();
    expect($page->published_at)->not->toBeNull();
});

test('a duplicate slug fails validation', function () {
    Page::factory()->create(['slug' => 'our-mission']);
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.pages.store'), validPagePayload())
        ->assertSessionHasErrors('slug');
});

test('an editor can update a page and it is audit logged', function () {
    $page = Page::factory()->create(['title' => 'Old Title']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->put(
        route('admin.pages.update', $page),
        validPagePayload(['title' => 'New Title', 'slug' => $page->slug])
    );

    $response->assertRedirect(route('admin.pages.index'));
    $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'New Title']);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'update',
        'entity_type' => 'page',
        'entity_id' => $page->id,
    ]);
});

test('a viewer cannot update or delete a page', function () {
    $page = Page::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)
        ->put(route('admin.pages.update', $page), validPagePayload(['slug' => $page->slug]))
        ->assertForbidden();
    $this->actingAs($viewer)->delete(route('admin.pages.destroy', $page))->assertForbidden();
});

test('an editor can delete a page and it is audit logged', function () {
    $page = Page::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->delete(route('admin.pages.destroy', $page));

    $response->assertRedirect(route('admin.pages.index'));
    $this->assertSoftDeleted('pages', ['id' => $page->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'page',
        'entity_id' => $page->id,
    ]);
});
