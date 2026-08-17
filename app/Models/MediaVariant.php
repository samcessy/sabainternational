<?php

namespace App\Models;

use App\Enums\MediaVariantType;
use Database\Factories\MediaVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property MediaVariantType $variant_type
 */
#[Fillable(['media_id', 'variant_type', 'path', 'width', 'height'])]
class MediaVariant extends Model
{
    /** @use HasFactory<MediaVariantFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'variant_type' => MediaVariantType::class,
        ];
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
