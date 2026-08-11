<?php

namespace App\Models;

use App\Enums\DonationFrequency;
use App\Enums\DonationStatus;
use Database\Factories\DonationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property DonationFrequency $frequency
 * @property DonationStatus $status
 */
#[Fillable([
    'supporter_id', 'campaign_id', 'program_id', 'amount_cents', 'currency',
    'frequency', 'anonymous', 'status', 'stripe_subscription_id',
])]
class Donation extends Model
{
    /** @use HasFactory<DonationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'frequency' => DonationFrequency::class,
            'anonymous' => 'boolean',
            'status' => DonationStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Supporter, $this>
     */
    public function supporter(): BelongsTo
    {
        return $this->belongsTo(Supporter::class);
    }

    /**
     * @return BelongsTo<Campaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * @return BelongsTo<Program, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * @return HasMany<DonationTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(DonationTransaction::class);
    }
}
