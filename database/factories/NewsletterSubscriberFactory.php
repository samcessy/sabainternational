<?php

namespace Database\Factories;

use App\Enums\SubscriberStatus;
use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsletterSubscriber>
 */
class NewsletterSubscriberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'consent_timestamp' => now(),
            'consent_ip' => fake()->ipv4(),
            'status' => SubscriberStatus::Subscribed,
        ];
    }
}
