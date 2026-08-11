<?php

namespace App\Services\Payments\Results;

final readonly class SubscriptionResult
{
    public function __construct(
        public string $subscriptionId,
        public string $clientSecret,
    ) {}
}
