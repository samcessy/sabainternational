<?php

namespace App\Jobs;

use App\Enums\MediaVariantType;
use App\Models\Media;
use App\Models\MediaVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\CollectionInterface;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Upload → variant pipeline per docs/architecture/media-architecture.md §1.
 * Runs as a queued job, not inline in the upload request, so a batch
 * upload doesn't hang the admin UI on image processing.
 */
class GenerateMediaVariants implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * crop=true: exact target size, focal-point-aware crop (thumbnail,
     * social — the two sizes small enough that a naive center-crop risks
     * cutting a subject out of frame). crop=false: scaled to fit within
     * bounds preserving aspect ratio, no distortion, no guaranteed exact
     * output size.
     *
     * @var array<int, array{type: MediaVariantType, width: int, height: int, crop: bool}>
     */
    private const VARIANTS = [
        ['type' => MediaVariantType::Thumbnail, 'width' => 150, 'height' => 150, 'crop' => true],
        ['type' => MediaVariantType::Small, 'width' => 400, 'height' => 300, 'crop' => false],
        ['type' => MediaVariantType::Medium, 'width' => 800, 'height' => 600, 'crop' => false],
        ['type' => MediaVariantType::Large, 'width' => 1200, 'height' => 800, 'crop' => false],
        ['type' => MediaVariantType::Hero, 'width' => 1920, 'height' => 1080, 'crop' => false],
        ['type' => MediaVariantType::Social, 'width' => 1200, 'height' => 630, 'crop' => true],
    ];

    public function __construct(public Media $media) {}

    public function handle(): void
    {
        $disk = Storage::disk('public');
        $manager = new ImageManager(new Driver);
        $original = $manager->decodePath($disk->path($this->media->path));

        $this->media->update(['exif_data' => $this->extractExif($original)]);

        foreach (self::VARIANTS as $spec) {
            $variantImage = $spec['crop']
                ? $this->cropToFocalPoint(clone $original, $spec['width'], $spec['height'])
                : (clone $original)->scale($spec['width'], $spec['height']);

            // strip: true — every publicly-served variant loses EXIF
            // (including GPS/location data) regardless of what the
            // original carried. See media-architecture.md §3.
            $encoded = $variantImage->encode(new WebpEncoder(quality: 82, strip: true));

            $path = $this->directory().'/variants/'.$spec['type']->value.'.webp';
            $disk->put($path, (string) $encoded);

            MediaVariant::query()->updateOrCreate(
                ['media_id' => $this->media->id, 'variant_type' => $spec['type']],
                ['path' => $path, 'width' => $variantImage->width(), 'height' => $variantImage->height()],
            );
        }
    }

    /**
     * Scales up/down so the image fully covers the target box, then crops
     * to exactly that box, offset within the available slack by the
     * media's focal point (0.0–1.0 fraction in each axis).
     */
    private function cropToFocalPoint(ImageInterface $image, int $targetWidth, int $targetHeight): ImageInterface
    {
        $scale = max($targetWidth / $image->width(), $targetHeight / $image->height());
        $scaledWidth = (int) ceil($image->width() * $scale);
        $scaledHeight = (int) ceil($image->height() * $scale);
        $image->resize($scaledWidth, $scaledHeight);

        $maxOffsetX = max(0, $scaledWidth - $targetWidth);
        $maxOffsetY = max(0, $scaledHeight - $targetHeight);
        $offsetX = (int) round($maxOffsetX * $this->media->focal_point_x);
        $offsetY = (int) round($maxOffsetY * $this->media->focal_point_y);

        return $image->crop($targetWidth, $targetHeight, $offsetX, $offsetY);
    }

    /**
     * Admin-only attribution/date metadata from the ORIGINAL — not stripped
     * here, only from the served variants above. Not every image (e.g.
     * PNGs) carries EXIF, so this degrades to an empty array rather than
     * failing the whole job.
     *
     * @return array<array-key, mixed>
     */
    private function extractExif(ImageInterface $image): array
    {
        $exif = $image->exif();

        return $exif instanceof CollectionInterface ? $exif->toArray() : [];
    }

    private function directory(): string
    {
        return dirname($this->media->path);
    }
}
