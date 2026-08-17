<?php

use App\Enums\AdminRole;
use App\Models\Media;
use App\Models\MediaVariant;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('an editor can view the media library', function () {
    Media::factory()->create(['filename' => 'photo.jpg', 'alt_text' => 'Students in class']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.media.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/media/Index')
        ->has('media.data', 1)
        ->where('media.data.0.alt_text', 'Students in class')
    );
});

test('a viewer can view but not delete media', function () {
    $media = Media::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.media.index'))->assertOk();
    $this->actingAs($viewer)->delete(route('admin.media.destroy', $media))->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.media.index'))->assertRedirect(route('login'));
});

test('an editor can delete media, its files, and it is audit logged', function () {
    Storage::fake('public');

    $media = Media::factory()->create(['path' => 'media/abc-123/original.jpg']);
    MediaVariant::factory()->for($media)->create(['path' => 'media/abc-123/variants/thumbnail.webp']);
    Storage::disk('public')->put('media/abc-123/original.jpg', 'fake-image-content');
    Storage::disk('public')->put('media/abc-123/variants/thumbnail.webp', 'fake-variant-content');

    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->delete(route('admin.media.destroy', $media));

    $response->assertRedirect();
    $this->assertDatabaseMissing('media', ['id' => $media->id]);
    $this->assertDatabaseMissing('media_variants', ['media_id' => $media->id]);
    Storage::disk('public')->assertMissing('media/abc-123/original.jpg');
    Storage::disk('public')->assertMissing('media/abc-123/variants/thumbnail.webp');

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'media',
        'entity_id' => $media->id,
    ]);
});
