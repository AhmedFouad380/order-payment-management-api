<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function index()
    {
        $orders = $this->orderService->getOrders(auth()->user());
        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder(auth()->user(), $request->validated());
        return (new OrderResource($order))
                ->response()
                ->setStatusCode(201);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return new OrderResource($order->load(['items', 'payments']));
    }

    public function update(\Illuminate\Http\Request $request, Order $order)
    {
        // Simple update for status (Mocking Admin action)
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled'
        ]);

        $order->update(['status' => $validated['status']]);

        return new OrderResource($order);
    }

    public function destroy(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) { // Simple check
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Logic check: "Orders cannot be deleted if they have associated payments."
        // DB restrictOnDelete handles hard delete, but for SoftDelete we check manually or let DB handle it?
        // SoftDelete allows deletion even with relations if those relations don't block logic.
        // Task says "Orders cannot be deleted if they have associated payments."
        // So checking exists() is better.
        if ($order->payments()->exists()) {
             return response()->json(['error' => 'Cannot delete order with associated payments.'], 400);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully.']);
    }
}
