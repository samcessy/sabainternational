<?php

use App\Enums\AdminRole;
use App\Enums\ImageConsentStatus;
use App\Jobs\GenerateMediaVariants;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;

test('an editor can upload valid media and it dispatches variant generation', function () {
    Queue::fake();
    $editor = actingAsAdmin();

    $response = $this->actingAs($editor)->post(route('media.store'), [
        'file' => UploadedFile::fake()->image('photo.jpg', 1600, 1200),
        'alt_text' => 'Students at New Dawn during a science lesson',
        'consent_status' => ImageConsentStatus::Yes->value,
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('media', ['alt_text' => 'Students at New Dawn during a science lesson']);
    Queue::assertPushed(GenerateMediaVariants::class);
});

test('media upload requires alt text', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->post(route('media.store'), [
        'file' => UploadedFile::fake()->image('photo.jpg'),
        'consent_status' => ImageConsentStatus::Yes->value,
    ])->assertSessionHasErrors('alt_text');
});

test('media upload requires a consent status', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->post(route('media.store'), [
        'file' => UploadedFile::fake()->image('photo.jpg'),
        'alt_text' => 'A photo',
    ])->assertSessionHasErrors('consent_status');
});

test('media upload rejects disallowed file types', function () {
    $editor = actingAsAdmin();

    $this->actingAs($editor)->post(route('media.store'), [
        'file' => UploadedFile::fake()->create('script.php', 10),
        'alt_text' => 'A photo',
        'consent_status' => ImageConsentStatus::Yes->value,
    ])->assertSessionHasErrors('file');
});

test('a viewer cannot upload media', function () {
    $viewer = actingAsAdmin(AdminRole::Viewer);

    $this->actingAs($viewer)->post(route('media.store'), [
        'file' => UploadedFile::fake()->image('photo.jpg'),
        'alt_text' => 'A photo',
        'consent_status' => ImageConsentStatus::Yes->value,
    ])->assertForbidden();
});

test('a finance manager cannot upload media', function () {
    $financeManager = actingAsAdmin(AdminRole::FinanceManager);

    $this->actingAs($financeManager)->post(route('media.store'), [
        'file' => UploadedFile::fake()->image('photo.jpg'),
        'alt_text' => 'A photo',
        'consent_status' => ImageConsentStatus::Yes->value,
    ])->assertForbidden();
});

test('guests cannot upload media', function () {
    $this->post(route('media.store'), [
        'file' => UploadedFile::fake()->image('photo.jpg'),
        'alt_text' => 'A photo',
        'consent_status' => ImageConsentStatus::Yes->value,
    ])->assertRedirect(route('login'));
});
