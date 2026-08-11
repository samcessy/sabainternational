<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\ProgramCategory;
use App\Enums\ProgramRelationshipType;
use App\Enums\SensitiveContentClassification;
use Database\Factories\ProgramFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property ProgramCategory $category
 * @property ProgramRelationshipType $relationship_type
 * @property ContentStatus $status
 * @property Carbon|null $published_at
 */
#[Fillable([
    'name', 'legal_name', 'slug', 'category', 'relationship_type', 'external_url',
    'founded_year', 'location', 'short_description', 'overview', 'what_happens_here',
    'sensitive_content_classification', 'seo_title', 'seo_description', 'og_image',
    'status', 'published_at',
])]
class Program extends Model
{
    /** @use HasFactory<ProgramFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'category' => ProgramCategory::class,
            'relationship_type' => ProgramRelationshipType::class,
            'sensitive_content_classification' => SensitiveContentClassification::class,
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Story, $this>
     */
    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    /**
     * @return HasMany<Media, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * @return HasMany<ImpactMetric, $this>
     */
    public function impactMetrics(): HasMany
    {
        return $this->hasMany(ImpactMetric::class);
    }

    /**
     * @return HasMany<Donation, $this>
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }
}
