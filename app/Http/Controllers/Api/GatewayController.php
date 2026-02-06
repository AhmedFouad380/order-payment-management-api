<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGatewayRequest;
use App\Http\Resources\PaymentGatewayResource;
use App\Models\PaymentGateway;
use Illuminate\Http\JsonResponse;

class GatewayController extends Controller
{
    public function index()
    {
        return PaymentGatewayResource::collection(PaymentGateway::all());
    }

    public function store(StoreGatewayRequest $request): JsonResponse
    {
        $gateway = PaymentGateway::create($request->validated());
        return (new PaymentGatewayResource($gateway))
                ->response()
                ->setStatusCode(201);
    }
}
