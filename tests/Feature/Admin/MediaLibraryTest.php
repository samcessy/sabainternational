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

test('an editor can fetch the media picker list used by Story/TeamMember forms', function () {
    Media::factory()->create(['alt_text' => 'A photo']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->getJson(route('admin.media.picker'));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.alt_text', 'A photo');
});

test('a viewer can also fetch the media picker list (ViewContent, not ManageContent)', function () {
    Media::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->getJson(route('admin.media.picker'))->assertOk();
});

test('guests cannot fetch the media picker list', function () {
    $this->getJson(route('admin.media.picker'))->assertUnauthorized();
});

test('an editor can update media metadata and it is audit logged', function () {
    $media = Media::factory()->create(['alt_text' => 'Old alt text']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->put(route('admin.media.update', $media), [
        'alt_text' => 'New alt text',
        'caption' => 'A caption',
        'photographer' => 'Jane Doe',
        'copyright_license' => 'CC BY 4.0',
        'consent_status' => 'yes',
    ]);

    $response->assertRedirect(route('admin.media.index'));
    $this->assertDatabaseHas('media', [
        'id' => $media->id,
        'alt_text' => 'New alt text',
        'caption' => 'A caption',
        'photographer' => 'Jane Doe',
        'copyright_license' => 'CC BY 4.0',
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'update',
        'entity_type' => 'media',
        'entity_id' => $media->id,
    ]);
});

test('a viewer cannot update media', function () {
    $media = Media::factory()->create();
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)
        ->put(route('admin.media.update', $media), ['alt_text' => 'x', 'consent_status' => 'yes'])
        ->assertForbidden();
});

test('alt_text and consent_status are required when updating an image', function () {
    $media = Media::factory()->create(['path' => 'media/photo.jpg']);
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->put(route('admin.media.update', $media), ['alt_text' => '', 'consent_status' => ''])
        ->assertSessionHasErrors(['alt_text', 'consent_status']);
});

test('alt_text and consent_status are optional when updating a non-image file', function () {
    $media = Media::factory()->create(['path' => 'media/report.pdf', 'alt_text' => null, 'consent_status' => null]);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->put(route('admin.media.update', $media), [
        'alt_text' => '',
        'consent_status' => '',
        'caption' => 'Annual report',
    ]);

    $response->assertSessionDoesntHaveErrors(['alt_text', 'consent_status']);
    $this->assertDatabaseHas('media', ['id' => $media->id, 'caption' => 'Annual report']);
});

test('the media index reports whether each item is an image', function () {
    Media::factory()->create(['filename' => 'photo.jpg', 'path' => 'media/photo.jpg']);
    Media::factory()->create(['filename' => 'report.pdf', 'path' => 'media/report.pdf', 'alt_text' => null, 'consent_status' => null]);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.media.index'));

    $items = collect($response->viewData('page')['props']['media']['data']);

    expect($items->firstWhere('filename', 'photo.jpg')['is_image'])->toBeTrue();
    expect($items->firstWhere('filename', 'report.pdf')['is_image'])->toBeFalse();
});
