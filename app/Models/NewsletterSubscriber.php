<?php

namespace App\Models;

use App\Enums\SubscriberStatus;
use Database\Factories\NewsletterSubscriberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['email', 'consent_timestamp', 'consent_ip', 'frequency_preference', 'status', 'unsubscribed_at'])]
class NewsletterSubscriber extends Model
{
    /** @use HasFactory<NewsletterSubscriberFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'consent_timestamp' => 'datetime',
            'status' => SubscriberStatus::class,
            'unsubscribed_at' => 'datetime',
        ];
    }
}
