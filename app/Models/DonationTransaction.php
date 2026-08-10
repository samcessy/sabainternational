<?php

namespace App\Models;

use App\Enums\PaymentGateway;
use App\Enums\TransactionStatus;
use Database\Factories\DonationTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['donation_id', 'gateway', 'gateway_reference', 'status', 'receipt_sent_at'])]
class DonationTransaction extends Model
{
    /** @use HasFactory<DonationTransactionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'gateway' => PaymentGateway::class,
            'status' => TransactionStatus::class,
            'receipt_sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Donation, $this>
     */
    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }
}
