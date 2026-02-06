<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGatewayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'key' => 'required|string|unique:payment_gateways,key',
            'class' => 'required|string', // Could add validation to check if class exists using custom rule
            'config' => 'nullable|array',
            'is_active' => 'boolean',
        ];
    }
}
