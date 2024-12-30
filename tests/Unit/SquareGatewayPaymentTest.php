<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\TestCase;
use App\PaymentGateways\SquareGateway;
use Square\Models\CreatePaymentRequest;
use Square\Models\Money;

class SquareGatewayPaymentTest extends TestCase
{
    private static $paymentId;
    private $squareGateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->squareGateway = new SquareGateway();
    }

    public function testCreatePayment()
    {
        $params = [
            'source_id' => 'cnon:card-nonce-ok',
            'amount' => 10.00,
            'currency' => 'USD'
        ];

        $response = $this->squareGateway->createPayment($params);

        $this->assertNotNull($response);
        $this->assertObjectHasProperty('payment', $response);
        $this->assertEquals(1000, $response->getPayment()->getAmountMoney()->getAmount());
        $this->assertEquals('USD', $response->getPayment()->getAmountMoney()->getCurrency());
        $this->assertEquals('COMPLETED', $response->getPayment()->getStatus());

        // Store payment ID for other tests
        self::$paymentId = $response->getPayment()->getId();

        // $money = new Money();
        // $money->setAmount((int)($params['amount'] * 100)); // Convert amount to cents
        // $money->setCurrency($params['currency'] ?? 'USD'); // Default to USD

        // $request = new CreatePaymentRequest(
        //     $params['source_id'], // e.g., nonce from Square payment form
        //     uniqid(),             // idempotency key
        //     $money
        // );


        // $response = $this->squareGateway->getPaymentsApi()->createPayment($request);
        // $this->assertNotNull($response);
        // dd($response);
    }

    public function testCancelPayment()
    {
        $this->assertNotNull(self::$paymentId, 'Payment ID is not set.');

        $response = $this->squareGateway->cancelPayment(self::$paymentId);

        $this->assertNotNull($response);
        // $this->assertObjectHasProperty('errors', $response);
        // $this->assertNull($response->getErrors(), 'Payment cancellation failed with errors: ' . json_encode($response->getErrors()));
        // dd($response);
        // $this->assertEquals('CANCELED', $response->getPayment()->getStatus());
    }

    // Other tests remain the same
}
