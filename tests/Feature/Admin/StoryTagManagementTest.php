<?php

use App\Enums\AdminRole;
use App\Models\Story;
use App\Models\StoryTag;
use Inertia\Testing\AssertableInertia as Assert;

function validStoryTagPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Education',
        'slug' => 'education',
    ], $overrides);
}

test('an editor can view the story tags index', function () {
    StoryTag::factory()->create(['name' => 'Nutrition']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.story-tags.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/story-tags/Index')
        ->has('storyTags.data', 1)
        ->where('storyTags.data.0.name', 'Nutrition')
    );
});

test('the story tags index reports how many stories use each tag', function () {
    $tag = StoryTag::factory()->create();
    $stories = Story::factory()->count(2)->create();
    $tag->stories()->attach($stories);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.story-tags.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('storyTags.data.0.stories_count', 2)
    );
});

test('a viewer can view but not manage story tags', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.story-tags.index'))->assertOk();
    $this->actingAs($viewer)->post(route('admin.story-tags.store'), validStoryTagPayload())->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.story-tags.index'))->assertRedirect(route('login'));
});

test('an editor can create a story tag and it is audit logged', function () {
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->post(route('admin.story-tags.store'), validStoryTagPayload());

    $response->assertRedirect(route('admin.story-tags.index'));
    $tag = StoryTag::query()->where('slug', 'education')->firstOrFail();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'create',
        'entity_type' => 'story_tag',
        'entity_id' => $tag->id,
    ]);
});

test('a duplicate slug fails validation', function () {
    StoryTag::factory()->create(['slug' => 'education']);
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.story-tags.store'), validStoryTagPayload())
        ->assertSessionHasErrors('slug');
});

test('an editor can update a story tag and it is audit logged', function () {
    $tag = StoryTag::factory()->create(['name' => 'Old Name']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->put(
        route('admin.story-tags.update', $tag),
        validStoryTagPayload(['name' => 'New Name', 'slug' => $tag->slug])
    );

    $response->assertRedirect(route('admin.story-tags.index'));
    $this->assertDatabaseHas('story_tags', ['id' => $tag->id, 'name' => 'New Name']);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'update',
        'entity_type' => 'story_tag',
        'entity_id' => $tag->id,
    ]);
});

test('a viewer cannot update or delete a story tag', function () {
    $tag = StoryTag::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)
        ->put(route('admin.story-tags.update', $tag), validStoryTagPayload(['slug' => $tag->slug]))
        ->assertForbidden();
    $this->actingAs($viewer)->delete(route('admin.story-tags.destroy', $tag))->assertForbidden();
});

test('an editor can delete a story tag and it is audit logged', function () {
    $tag = StoryTag::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->delete(route('admin.story-tags.destroy', $tag));

    $response->assertRedirect(route('admin.story-tags.index'));
    $this->assertDatabaseMissing('story_tags', ['id' => $tag->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'story_tag',
        'entity_id' => $tag->id,
    ]);
});
