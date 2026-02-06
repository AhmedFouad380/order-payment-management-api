<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            // Calculate totals
            $items = $data['items'];
            $totalAmount = 0;

            // Prepare Order data
            $order = $user->orders()->create([
                'status' => 'pending', // uses Enum cast default? No, string in DB, cast in Model. Enum value needed.
                'currency' => $data['currency'] ?? 'USD',
                'customer_details' => $data['customer_details'] ?? null,
                'total_amount' => 0, // placeholder, updated below
            ]);

            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;

                $order->items()->create([
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $order->update(['total_amount' => $totalAmount]);

            return $order->load('items');
        });
    }

    public function getOrders(User $user)
    {
        return $user->orders()->with('items')->latest()->paginate(10);
    }
}
