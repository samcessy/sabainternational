<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property ContentStatus $status
 * @property Carbon|null $published_at
 */
#[Fillable([
    'title', 'slug', 'body', 'seo_title', 'seo_description', 'og_image',
    'status', 'author_id', 'published_at',
])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
