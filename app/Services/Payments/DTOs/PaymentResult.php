<?php

namespace App\Services\Payments\DTOs;

class PaymentResult
{
    public function __construct(
        public bool $success,
        public ?string $transactionId,
        public ?string $message = null,
        public array $data = []
    ) {}
}
