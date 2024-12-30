# Laravel Square Recurring Payments

This repository provides an integration with Square's API for handling recurring payments using Laravel. It offers functionality for managing customers, creating subscriptions, processing payments, and handling callbacks.

## Features

- **Create and manage customers**
- **Create customer cards** for recurring payments
- **Create and manage subscriptions**
- **Process one-time payments**
- **Cancel subscriptions and payments**
- **Verify payments**

## Requirements

- Laravel 11.x or above
- PHP 8.2 or above
- Square API credentials (Access Token, Location ID, Plan Variation ID)

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/yourusername/laravel-square-recurring-payments.git
   cd laravel-square-recurring-payments
   composer install
    ```
## Create .env
```
SQUARE_ACCESS_TOKEN=your_token
SQUARE_ENVIRONMENT=sandbox # production
# Sqaure Default Values
SQUARE_LOCATION_ID=your_app_location_id
SQUARE_CURRENCY=USD
SQUARE_PLAN_VARIATION_ID=your_plan_variation_id
SQUARE_WEBHOOK_SECRET=your_webhook_secret_key
NOTIFICATION_URL=your_registered_webhook_url #i.e route('square.callback') or example https://8d03-39-45-98-253.ngrok-free.app/api/square/callback
```

## Test
```
php artisan test
```

Check Unit Test files and SquarePaymentController for better understanding!
