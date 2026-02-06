<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function index()
    {
        // View all payments
        $payments = \App\Models\Payment::with('order')->latest()->paginate(10);
        return PaymentResource::collection($payments);
    }

    public function show(\App\Models\Payment $payment)
    {
        return new PaymentResource($payment->load('order'));
    }

    public function store(StorePaymentRequest $request, Order $order): JsonResponse
    {
        // Check authorization
        if ($order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $payment = $this->paymentService->processPayment($order, $request->gateway_key);
            
            if ($payment->status === \App\Enums\PaymentStatus::FAILED) {
                return response()->json([
                    'error' => 'Payment failed', 
                    'data' => new PaymentResource($payment)
                ], 422);
            }

            return (new PaymentResource($payment))
                    ->response()
                    ->setStatusCode(201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
