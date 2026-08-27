<?php

namespace App\Models;

use App\Enums\ImageConsentStatus;
use App\Enums\MediaVariantType;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property ImageConsentStatus|null $consent_status
 */
#[Fillable([
    'filename', 'path', 'alt_text', 'caption', 'photographer', 'copyright_license',
    'consent_status', 'program_id', 'story_id', 'focal_point_x', 'focal_point_y', 'exif_data',
])]
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'consent_status' => ImageConsentStatus::class,
            'focal_point_x' => 'float',
            'focal_point_y' => 'float',
            'exif_data' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Program, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * @return BelongsTo<Story, $this>
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * @return HasMany<MediaVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }

    /**
     * Null until GenerateMediaVariants has run - a freshly uploaded image
     * has a Media row but no variants yet, since that job is queued, not
     * inline with the upload request.
     */
    public function thumbnailUrl(): ?string
    {
        $thumbnail = $this->variants->firstWhere('variant_type', MediaVariantType::Thumbnail);

        return $thumbnail ? Storage::disk('public')->url($thumbnail->path) : null;
    }

    /**
     * The original uploaded file - used for non-image downloads (e.g. a
     * Document's PDF) where a resized variant doesn't apply.
     */
    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * There's no stored mime_type column, so this is inferred from the
     * saved file's extension - the same extension whitelist
     * StoreMediaRequest validates against. Used post-upload (e.g. by
     * UpdateMediaRequest) where there's no fresh UploadedFile to read a
     * MIME type from directly.
     */
    public function isImage(): bool
    {
        $extension = strtolower(pathinfo($this->path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
    }
}
