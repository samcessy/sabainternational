<?php

namespace App\Services\Payments\Results;

final readonly class PaymentIntentResult
{
    public function __construct(
        public string $paymentIntentId,
        public string $clientSecret,
    ) {}
}
