<?php

use App\Enums\MediaVariantType;
use App\Jobs\GenerateMediaVariants;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function createUploadedMedia(int $width = 2000, int $height = 1000, float $focalX = 0.5, float $focalY = 0.5): Media
{
    Storage::fake('public');

    $file = UploadedFile::fake()->image('original.jpg', $width, $height);
    $path = $file->storeAs('media/test-uuid', 'original.jpg', 'public');

    return Media::factory()->create([
        'path' => $path,
        'focal_point_x' => $focalX,
        'focal_point_y' => $focalY,
    ]);
}

/**
 * UploadedFile::fake()->image() produces a visually uniform image, which
 * is useless for verifying *where* a crop lands — cropping any region of a
 * uniform image yields identical bytes. This builds a genuinely two-toned
 * image (solid red left half, solid blue right half) via raw GD calls so a
 * left-vs-right focal point crop is actually distinguishable.
 */
function twoTonedImageBytes(): string
{
    $width = 2000;
    $height = 500;
    $image = imagecreatetruecolor($width, $height);
    imagefilledrectangle($image, 0, 0, (int) ($width / 2) - 1, $height, imagecolorallocate($image, 200, 0, 0));
    imagefilledrectangle($image, (int) ($width / 2), 0, $width, $height, imagecolorallocate($image, 0, 0, 200));

    $tempPath = tempnam(sys_get_temp_dir(), 'twotone').'.jpg';
    imagejpeg($image, $tempPath);
    imagedestroy($image);

    $bytes = file_get_contents($tempPath);
    unlink($tempPath);

    return $bytes;
}

test('the job creates all six variants with the correct dimensions and webp format', function () {
    $media = createUploadedMedia();

    (new GenerateMediaVariants($media))->handle();

    expect($media->variants()->count())->toBe(6);

    $expected = [
        MediaVariantType::Thumbnail->value => [150, 150],
        MediaVariantType::Social->value => [1200, 630],
    ];

    foreach ($expected as $type => [$width, $height]) {
        $variant = $media->variants()->where('variant_type', $type)->firstOrFail();
        expect($variant->width)->toBe($width)
            ->and($variant->height)->toBe($height)
            ->and($variant->path)->toEndWith('.webp');

        Storage::disk('public')->assertExists($variant->path);
    }
});

test('fit variants preserve aspect ratio without exceeding the target bounds', function () {
    $media = createUploadedMedia(width: 2000, height: 1000);

    (new GenerateMediaVariants($media))->handle();

    $medium = $media->variants()->where('variant_type', MediaVariantType::Medium->value)->firstOrFail();

    // Source is 2:1 — fitting within 800x600 should be constrained by
    // width (800x400), not height, and never exceed either bound.
    expect($medium->width)->toBeLessThanOrEqual(800)
        ->and($medium->height)->toBeLessThanOrEqual(600)
        ->and(round($medium->width / $medium->height, 1))->toBe(2.0);
});

test('different focal points on a wide source image produce genuinely different crop content', function () {
    Storage::fake('public');
    $bytes = twoTonedImageBytes();
    $leftPath = 'media/left-focused/original.jpg';
    $rightPath = 'media/right-focused/original.jpg';
    Storage::disk('public')->put($leftPath, $bytes);
    Storage::disk('public')->put($rightPath, $bytes);

    $leftFocused = Media::factory()->create(['path' => $leftPath, 'focal_point_x' => 0.0, 'focal_point_y' => 0.5]);
    $rightFocused = Media::factory()->create(['path' => $rightPath, 'focal_point_x' => 1.0, 'focal_point_y' => 0.5]);

    (new GenerateMediaVariants($leftFocused))->handle();
    (new GenerateMediaVariants($rightFocused))->handle();

    $leftThumbnail = $leftFocused->variants()->where('variant_type', MediaVariantType::Thumbnail->value)->firstOrFail();
    $rightThumbnail = $rightFocused->variants()->where('variant_type', MediaVariantType::Thumbnail->value)->firstOrFail();

    // Cropping the same red-left/blue-right source from the far left vs.
    // far right must yield different bytes — proves the focal point
    // actually moves the crop window rather than always center-cropping.
    expect(Storage::disk('public')->get($leftThumbnail->path))
        ->not->toBe(Storage::disk('public')->get($rightThumbnail->path));
});

test('the original exif data is stored on the media record without a job failure when absent', function () {
    $media = createUploadedMedia();

    (new GenerateMediaVariants($media))->handle();

    expect($media->fresh()->exif_data)->toBeArray();
});

test('re-running the job for the same media updates variants instead of duplicating them', function () {
    $media = createUploadedMedia();

    (new GenerateMediaVariants($media))->handle();
    (new GenerateMediaVariants($media))->handle();

    expect($media->variants()->count())->toBe(6);
});
