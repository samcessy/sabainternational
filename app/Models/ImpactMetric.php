<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Database\Factories\ImpactMetricFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['program_id', 'name', 'unit'])]
class ImpactMetric extends Model
{
    /** @use HasFactory<ImpactMetricFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Program, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * @return HasMany<ImpactMetricValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(ImpactMetricValue::class, 'metric_id');
    }

    /**
     * The latest verified value, or null if none exists — callers should
     * fall back to a qualitative statement rather than rendering nothing or
     * an unverified number. See saba.md §6.3, docs/content-model.md §2.8.
     */
    public function latestVerifiedValue(): ?ImpactMetricValue
    {
        return $this->values()
            ->where('verification_status', VerificationStatus::Verified)
            ->latest('last_updated_at')
            ->first();
    }
}
