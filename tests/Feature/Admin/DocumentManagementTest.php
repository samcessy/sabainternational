<?php

use App\Enums\AdminRole;
use App\Enums\ContentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Media;
use Inertia\Testing\AssertableInertia as Assert;

function validDocumentPayload(array $overrides = []): array
{
    return array_merge([
        'title' => '2025 Annual Report',
        'document_type' => DocumentType::AnnualReport->value,
        'year' => 2025,
        'summary' => null,
        'file_media_id' => Media::factory()->create()->id,
        'cover_image_media_id' => null,
        'status' => ContentStatus::Draft->value,
    ], $overrides);
}

test('an editor can view the documents index', function () {
    Document::factory()->create(['title' => 'A Report']);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.documents.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/documents/Index')
        ->has('documents.data', 1)
    );
});

test('a viewer can view but not manage documents', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->get(route('admin.documents.index'))->assertOk();
    $this->actingAs($viewer)->post(route('admin.documents.store'), validDocumentPayload())->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.documents.index'))->assertRedirect(route('login'));
});

test('a document requires a file', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.documents.store'), validDocumentPayload(['file_media_id' => null]))
        ->assertSessionHasErrors('file_media_id');
});

test('a document cannot reference a nonexistent media id', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.documents.store'), validDocumentPayload(['file_media_id' => 99999]))
        ->assertSessionHasErrors('file_media_id');
});

test('an editor can create a document and it is audit logged', function () {
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->post(route('admin.documents.store'), validDocumentPayload());

    $response->assertRedirect(route('admin.documents.index'));
    $document = Document::query()->where('title', '2025 Annual Report')->firstOrFail();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'create',
        'entity_type' => 'document',
        'entity_id' => $document->id,
    ]);
});

test('creating a document with status published sets published_at automatically', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->post(route('admin.documents.store'), validDocumentPayload([
        'status' => ContentStatus::Published->value,
    ]));

    $document = Document::query()->where('title', '2025 Annual Report')->firstOrFail();
    expect($document->published_at)->not->toBeNull();
});

test('an unselected optional cover image does not fail exists validation', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)
        ->post(route('admin.documents.store'), [...validDocumentPayload(), 'cover_image_media_id' => ''])
        ->assertSessionDoesntHaveErrors('cover_image_media_id');
});

test('editing a document exposes its file name and thumbnail for the picker preview', function () {
    $media = Media::factory()->create(['filename' => 'report.pdf']);
    $document = Document::factory()->create(['file_media_id' => $media->id]);
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->get(route('admin.documents.edit', $document));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('document.file_media_id', $media->id)
        ->where('document.file_name', 'report.pdf')
    );
});

test('an editor can delete a document and it is audit logged', function () {
    $document = Document::factory()->create();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->delete(route('admin.documents.destroy', $document));

    $response->assertRedirect(route('admin.documents.index'));
    $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $editor->id,
        'action' => 'delete',
        'entity_type' => 'document',
        'entity_id' => $document->id,
    ]);
});
