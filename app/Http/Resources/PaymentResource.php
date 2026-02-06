<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'transaction_id' => $this->transaction_id,
            'gateway_key' => $this->gateway_key,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'response_data' => $this->response_data,
            'created_at' => $this->created_at->toIso8601String(),
            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
