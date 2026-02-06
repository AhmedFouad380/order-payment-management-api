# Implementation Plan: Order & Payment Management API

## Goal Description
Build a scalable Order and Payment Management API using Laravel.
The system will focus on:
1.  **Public API**: RESTful, JWT-secured endpoints for client applications (Mobile/Web) to create orders and process payments.
2.  **Extensibility**: A Strategy Pattern implementation to allow easy addition of new payment gateways.

## User Review Required
> [!NOTE]
> **Pure API Approach**: As requested, we are proceeding without Filament. All management features (viewing orders, payments) will be implemented as standard API endpoints.

## Proposed Architecture

### 1. Database Schema
Refined to support extensibility and detailed tracking as per requirements.

- **`users`**
    - Standard Laravel authentication columns (`id`, `name`, `email`, `password`).

- **`payment_gateways`** (Configuration)
    - `id`
    - `name` (string): e.g., "PayPal", "Stripe".
    - `key` (string, unique): e.g., "paypal_express", "stripe_cc". Used by frontend to select gateway.
    - `class` (string): Full namespace of the Strategy implementation (e.g., `App\Services\Payments\Gateways\PaypalGateway`).
    - `config` (json, encrypted): key/secret pairs. Allows runtime configuration.
    - `is_active` (boolean).

- **`orders`**
    - `id`
    - `user_id` (FK to users).
    - `status` (enum): `pending`, `confirmed`, `cancelled`.
    - `total_amount` (decimal, 10, 2).
    - `currency` (string, default 'USD').
    - `customer_details` (json, nullable): Snapshot of shipping/contact info if different from user profile.
    - `timestamps`, `softDeletes`.

- **`order_items`**
    - `id`, `order_id` (FK).
    - `product_name` (string).
    - `quantity` (integer).
    - `unit_price` (decimal).
    - `subtotal` (decimal): Calculated as `quantity * unit_price`.

- **`payments`**
    - `id`
    - `order_id` (FK to orders).
    - `gateway_key` (string): Links to `payment_gateways.key`.
    - `transaction_id` (string, nullable): External ID from the provider.
    - `amount` (decimal): Amount paid in this transaction.
    - `status` (enum): `pending`, `successful`, `failed`.
    - `response_data` (json, nullable): Full gateway response for debugging/audit.

### 2. Strategy Pattern (The "Extensibility" Core)
We will create a specific `PaymentGateway` interface.
```php
interface PaymentGateway {
    public function charge(float $amount, array $details): PaymentResult;
    public function refund(string $transactionId): PaymentResult;
}
```
- **Context Class**: `PaymentProcessor` which selects the correct gateway class.
- **Implementations**:
    - `PaypalGateway`
    - `StripeGateway` (Mock)
    - `CreditCardGateway` (Mock)

### 3. API Layer
Located in `routes/api.php` and `app/Http/Controllers/Api`.
- **Auth**: `AuthController` (Login, Register).
- **Orders**: `OrderController` (CRUD: index, store, show, update, destroy).
- **Payments**: `PaymentController` (process, index, show).
- **Gateways**: `GatewayController` (index, store - for admin config via API).

## Component Breakdown

### Core & Database
#### [NEW] [create_gateways_table](file:///database/migrations/xxxx_create_gateways_table.php)
#### [NEW] [create_orders_table](file:///database/migrations/xxxx_create_orders_table.php)
#### [NEW] [create_payments_table](file:///database/migrations/xxxx_create_payments_table.php)

### Logic (Strategy)
#### [NEW] [PaymentGateway.php](file:///app/Services/Payments/PaymentGateway.php)
#### [NEW] [PaypalGateway.php](file:///app/Services/Payments/Gateways/PaypalGateway.php)
#### [NEW] [CreditCardGateway.php](file:///app/Services/Payments/Gateways/CreditCardGateway.php)
#### [NEW] [PaymentContext.php](file:///app/Services/Payments/PaymentContext.php)

### API
#### [NEW] [Api/OrderController.php](file:///app/Http/Controllers/Api/OrderController.php)
#### [NEW] [Api/PaymentController.php](file:///app/Http/Controllers/Api/PaymentController.php)
#### [NEW] [Api/AuthController.php](file:///app/Http/Controllers/Api/AuthController.php)
#### [NEW] [Api/GatewayController.php](file:///app/Http/Controllers/Api/GatewayController.php)

## Verification Plan

### Automated Tests
- **Unit Tests**: Test the `PaymentContext` switches strategies correctly.
- **Feature Tests**: 
    - Test `POST /api/orders` creates an order.
    - Test `POST /api/payments` charges the order using the mock gateway.
    - Test JWT prevents unauthorized access.

### Manual Verification
1.  **API**: Use Postman to register, login (get Token), create order, and pay.
