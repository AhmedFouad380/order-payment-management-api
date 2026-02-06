# Order & Payment Management API

This is a Laravel-based API for managing orders and payments, designed with **Clean Code** principles and **Extensibility** in mind using the Strategy Pattern.

## Features
- **Extensible Payment System**: Easily add new gateways without changing core logic.
- **RESTful API**: Standard CRUD endpoints.
- **JWT Authentication**: Secure stateless authentication.
- **Database Tracking**: Detailed logs of orders, items, and payment transactions.

## Setup Instructions

1.  **Clone & Install**
    ```bash
    git clone <repo_url>
    cd task
    composer install
    ```

2.  **Environment**
    ```bash
    cp .env.example .env
    php artisan key:generate
    php artisan jwt:secret
    ```

3.  **Database**
    Configure your database in `.env`, then run:
    ```bash
    php artisan migrate
    ```

4.  **Serve**
    ```bash
    php artisan serve
    ```

## Extensibility Guide (Adding Payment Gateways)
This system adheres to the **Open/Closed Principle**. To add a new Payment Gateway (e.g., Apple Pay):

1.  **Create Strategy Class**: Create a class in `app/Services/Payments/Gateways/` that implements `App\Services\Payments\PaymentGatewayInterface`.
    ```php
    class ApplePayGateway implements PaymentGatewayInterface {
        public function charge(float $amount, array $config): PaymentResult {
             // Logic here
        }
    }
    ```

2.  **Register in Database**: Add a row to the `payment_gateways` table via SQL or the API (`POST /api/gateways`).
    ```json
    {
        "name": "Apple Pay",
        "key": "apple_pay",
        "class": "App\\Services\\Payments\\Gateways\\ApplePayGateway",
        "is_active": true
    }
    ```
    *No other code changes are required.*

## Documentation
- Import `postman_collection.json` into Postman.
- Run `php artisan test` to see the test suite in action.

## Testing
Run the comprehensive test suite including Unit tests for the Strategy logic and Feature tests for the API flow:
```bash
php artisan test
```
