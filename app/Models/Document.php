<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\DocumentType;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title', 'document_type', 'year', 'summary', 'file_media_id',
    'cover_image_media_id', 'status', 'published_at',
])]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'file_media_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_image_media_id');
    }
}
