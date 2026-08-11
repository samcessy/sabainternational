<?php

namespace App\Services\Payments\Results;

final readonly class RefundResult
{
    public function __construct(
        public string $refundId,
        public string $status,
    ) {}
}
