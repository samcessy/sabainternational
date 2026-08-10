<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Database\Factories\ImpactMetricValueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['metric_id', 'value', 'time_period', 'data_source', 'verification_status', 'last_updated_at'])]
class ImpactMetricValue extends Model
{
    /** @use HasFactory<ImpactMetricValueFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'verification_status' => VerificationStatus::class,
            'last_updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ImpactMetric, $this>
     */
    public function metric(): BelongsTo
    {
        return $this->belongsTo(ImpactMetric::class, 'metric_id');
    }
}
