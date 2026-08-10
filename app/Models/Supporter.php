<?php

namespace App\Models;

use Database\Factories\SupporterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'stripe_customer_id', 'communication_preferences'])]
#[Hidden(['stripe_customer_id'])]
class Supporter extends Model
{
    /** @use HasFactory<SupporterFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'communication_preferences' => 'array',
        ];
    }

    /**
     * @return HasMany<Donation, $this>
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }
}
