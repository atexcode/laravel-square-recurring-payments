<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\TestCase;
use App\PaymentGateways\SquareGateway;
use Square\Models\CreatePaymentRequest;
use Square\Models\Money;

class SquareGatewaySubscriptionTest extends TestCase
{
    private static $paymentId;
    private $squareGateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->squareGateway = new SquareGateway();
    }

    public function testCreateSubsciption()
    {
        $params = [
            'customer_id' => '1D6QVQAXM9M1WCR4A7T7QP73S8',
            'card_id' => 'ccof:CA4SEAOPm5wdWZrTfz3R6zJ9Lc8oAg',
            'amount' => 13.00,
        ];

        $response = $this->squareGateway->createSubscription($params);

        $this->assertNotNull($response);
        // dd($response);
        $this->assertObjectHasProperty('subscription', $response);
        // $this->assertEquals($params['amount'], $response->getSubscription()->getAmountMoney()->getAmount());
        // $this->assertEquals($params['currency'], $response->getSubscription()->getAmountMoney()->getCurrency());
        $this->assertEquals('ACTIVE', $response->getSubscription()->getStatus());
        
        // Store payment ID for other tests
        self::$paymentId = $response->getSubscription()->getId();
        // dd($response->getSubscription()->getId());
    }

    public function testCancelSubscription()
    {
        // dd(self::$paymentId);
        $this->assertNotNull(self::$paymentId, 'Payment ID is not set.');

        $response = $this->squareGateway->cancelSubscription(self::$paymentId);

        $this->assertNotNull($response);
        // dd($response);
        // $this->assertObjectHasProperty('errors', $response);
        // $this->assertNull($response->getErrors(), 'Payment cancellation failed with errors: ' . json_encode($response->getErrors()));
        // dd($response);
        // $this->assertEquals('CANCELED', $response->getPayment()->getStatus());
    }

    // Other tests remain the same
}
