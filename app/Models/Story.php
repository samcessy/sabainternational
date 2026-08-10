<?php

namespace App\Models;

use App\Enums\ApprovalStage;
use App\Enums\ConsentStatus;
use App\Enums\ContentStatus;
use App\Enums\ImageConsentStatus;
use App\Enums\SensitiveContentClassification;
use App\Enums\StoryType;
use Database\Factories\StoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

/**
 * @property ContentStatus $status
 * @property ConsentStatus|null $consent_status
 */
#[Fillable([
    'title', 'slug', 'excerpt', 'body', 'featured_image_media_id', 'author_id',
    'program_id', 'story_type', 'location', 'consent_status', 'image_consent',
    'guardian_consent', 'anonymity_requested', 'sensitive_content_classification',
    'approval_stage', 'attribution', 'seo_title', 'seo_description', 'og_image',
    'status', 'featured', 'published_at',
])]
class Story extends Model
{
    /** @use HasFactory<StoryFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'story_type' => StoryType::class,
            'consent_status' => ConsentStatus::class,
            'image_consent' => ImageConsentStatus::class,
            'anonymity_requested' => 'boolean',
            'sensitive_content_classification' => SensitiveContentClassification::class,
            'approval_stage' => ApprovalStage::class,
            'status' => ContentStatus::class,
            'featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_media_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<Program, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * @return HasMany<Media, $this>
     */
    public function gallery(): HasMany
    {
        return $this->hasMany(Media::class, 'story_id');
    }

    /**
     * @return BelongsToMany<StoryTag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(StoryTag::class, 'story_tag');
    }

    /**
     * Blocks publishing a story with unresolved consent — the CMS must make
     * responsible publishing easier than irresponsible publishing (saba.md
     * §35.8, §7.3). See docs/content-model.md §2.4.
     */
    protected static function booted(): void
    {
        static::saving(function (Story $story) {
            if ($story->status === ContentStatus::Published && $story->consent_status === null) {
                throw new RuntimeException('A story cannot be published without a recorded consent status.');
            }
        });
    }
}
