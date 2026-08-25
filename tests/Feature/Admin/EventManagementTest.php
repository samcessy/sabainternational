<?php

use App\Enums\AdminRole;
use App\Enums\ContentStatus;
use App\Models\Event;
use App\Models\Media;
use Inertia\Testing\AssertableInertia as Assert;

function validEventPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Annual Gala',
        'slug' => 'annual-gala',
        'description' => null,
        'start_at' => '2026-12-01T18:00',
        'end_at' => null,
        'location' => null,
        'featured_image_media_id' => null,
        'status' => ContentStatus::Draft->value,
    ], $overrides);
}

test('an editor can view the events index', function () {
    Event::factory()->create(['title' => 'An Event']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.events.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/events/Index')
        ->has('events.data', 1)
    );
});

test('a viewer can view but not manage events', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.events.index'))->assertOk();
    $this->actingAs($viewer)->post(route('admin.events.store'), validEventPayload())->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.events.index'))->assertRedirect(route('login'));
});

test('an editor can create an event and it is audit logged', function () {
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->post(route('admin.events.store'), validEventPayload());

    $response->assertRedirect(route('admin.events.index'));
    $event = Event::query()->where('slug', 'annual-gala')->firstOrFail();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'create',
        'entity_type' => 'event',
        'entity_id' => $event->id,
    ]);
});

test('an end date before the start date fails validation', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.events.store'), validEventPayload([
            'start_at' => '2026-12-01T18:00',
            'end_at' => '2026-11-01T18:00',
        ]))
        ->assertSessionHasErrors('end_at');
});

test('an event can be created with a featured image from the media library', function () {
    $media = Media::factory()->create();
    $editor = actingAsAdmin();

    $this->actingAs($editor)->post(route('admin.events.store'), validEventPayload([
        'featured_image_media_id' => $media->id,
    ]));

    $this->assertDatabaseHas('events', ['slug' => 'annual-gala', 'featured_image_media_id' => $media->id]);
});

test('an unselected featured image does not fail exists validation', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.events.store'), [...validEventPayload(), 'featured_image_media_id' => ''])
        ->assertSessionDoesntHaveErrors('featured_image_media_id');
});

test('an editor can update an event and it is audit logged', function () {
    $event = Event::factory()->create(['title' => 'Old Title']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->put(
        route('admin.events.update', $event),
        validEventPayload(['title' => 'New Title', 'slug' => $event->slug])
    );

    $response->assertRedirect(route('admin.events.index'));
    $this->assertDatabaseHas('events', ['id' => $event->id, 'title' => 'New Title']);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'update',
        'entity_type' => 'event',
        'entity_id' => $event->id,
    ]);
});

test('a viewer cannot update or delete an event', function () {
    $event = Event::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)
        ->put(route('admin.events.update', $event), validEventPayload(['slug' => $event->slug]))
        ->assertForbidden();
    $this->actingAs($viewer)->delete(route('admin.events.destroy', $event))->assertForbidden();
});

test('an editor can delete an event and it is audit logged', function () {
    $event = Event::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->delete(route('admin.events.destroy', $event));

    $response->assertRedirect(route('admin.events.index'));
    $this->assertDatabaseMissing('events', ['id' => $event->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'event',
        'entity_id' => $event->id,
    ]);
});
