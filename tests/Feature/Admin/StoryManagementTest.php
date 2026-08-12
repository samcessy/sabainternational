<?php

use App\Enums\AdminRole;
use App\Enums\ApprovalStage;
use App\Enums\ConsentStatus;
use App\Enums\ContentStatus;
use App\Enums\SensitiveContentClassification;
use App\Enums\StoryType;
use App\Models\Story;
use Inertia\Testing\AssertableInertia as Assert;

function validStoryPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'A Story of Change',
        'slug' => 'a-story-of-change',
        'excerpt' => null,
        'body' => null,
        'program_id' => null,
        'story_type' => StoryType::StoryOfChange->value,
        'location' => null,
        'consent_status' => ConsentStatus::NotRequired->value,
        'image_consent' => null,
        'guardian_consent' => null,
        'anonymity_requested' => false,
        'sensitive_content_classification' => SensitiveContentClassification::None->value,
        'approval_stage' => ApprovalStage::Draft->value,
        'attribution' => null,
        'seo_title' => null,
        'seo_description' => null,
        'og_image' => null,
        'status' => ContentStatus::Draft->value,
        'featured' => false,
    ], $overrides);
}

test('an editor can view the stories index', function () {
    Story::factory()->create(['title' => 'A Story']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.stories.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/stories/Index')
        ->has('stories.data', 1)
    );
});

test('a viewer can view but not manage stories', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.stories.index'))->assertOk();
    $this->actingAs($viewer)->get(route('admin.stories.create'))->assertForbidden();
    $this->actingAs($viewer)->post(route('admin.stories.store'), validStoryPayload())->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.stories.index'))->assertRedirect(route('login'));
});

test('an editor can create a story, becomes its author, and it is audit logged', function () {
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->post(route('admin.stories.store'), validStoryPayload());

    $response->assertRedirect(route('admin.stories.index'));
    $story = Story::query()->where('slug', 'a-story-of-change')->firstOrFail();
    expect($story->author_id)->toBe($editor->id);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'create',
        'entity_type' => 'story',
        'entity_id' => $story->id,
    ]);
});

test('creating a story requires a consent status, satisfying the publish guard', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.stories.store'), [...validStoryPayload(), 'consent_status' => null])
        ->assertSessionHasErrors('consent_status');
});

test('creating a story with status published sets published_at automatically', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->post(route('admin.stories.store'), validStoryPayload([
        'status' => ContentStatus::Published->value,
    ]));

    $story = Story::query()->where('slug', 'a-story-of-change')->firstOrFail();
    expect($story->published_at)->not->toBeNull();
});

test('an unselected optional program does not fail exists validation', function () {
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->post(route('admin.stories.store'), [
        ...validStoryPayload(),
        'program_id' => '',
        'image_consent' => '',
    ]);

    $response->assertSessionDoesntHaveErrors(['program_id', 'image_consent']);
});

test('unchecking featured on update actually clears it, not just omits it', function () {
    $story = Story::factory()->create(['featured' => true]);
    $editor = actingAsAdmin();

    $payload = validStoryPayload(['slug' => $story->slug]);
    unset($payload['featured']);

    $this->actingAs($editor)
        ->put(route('admin.stories.update', $story), $payload)
        ->assertRedirect(route('admin.stories.index'));

    expect($story->fresh()->featured)->toBeFalse();
});

test('an editor can update a story and it is audit logged', function () {
    $story = Story::factory()->create(['title' => 'Old Title']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->put(
        route('admin.stories.update', $story),
        validStoryPayload(['title' => 'New Title', 'slug' => $story->slug])
    );

    $response->assertRedirect(route('admin.stories.index'));
    $this->assertDatabaseHas('stories', ['id' => $story->id, 'title' => 'New Title']);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'update',
        'entity_type' => 'story',
        'entity_id' => $story->id,
    ]);
});

test('a viewer cannot update or delete a story', function () {
    $story = Story::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)
        ->put(route('admin.stories.update', $story), validStoryPayload(['slug' => $story->slug]))
        ->assertForbidden();
    $this->actingAs($viewer)->delete(route('admin.stories.destroy', $story))->assertForbidden();
});

test('an editor can delete a story and it is audit logged', function () {
    $story = Story::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->delete(route('admin.stories.destroy', $story));

    $response->assertRedirect(route('admin.stories.index'));
    $this->assertSoftDeleted('stories', ['id' => $story->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'story',
        'entity_id' => $story->id,
    ]);
});
