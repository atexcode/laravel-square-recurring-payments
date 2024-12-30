<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\TestCase;
use App\PaymentGateways\SquareGateway;

class SquareGatewayCustomerTest extends TestCase
{
    private static $customerId;
    private static $paymentId;
    private $squareGateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->squareGateway = new SquareGateway();
    }

    public function testCreateCustomer()
    {
        $params = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            // 'phone_number' => '+1234567890'
        ];

        $response = $this->squareGateway->createCustomer($params);

        $this->assertNotNull($response);
        $this->assertObjectHasProperty('customer', $response);
        $this->assertEquals('John', $response->getCustomer()->getGivenName());

        // Store customer ID for other tests
        self::$customerId = $response->getCustomer()->getId();
    }

    public function testUpdateCustomer()
    {
        $this->assertNotNull(self::$customerId, 'Customer ID is not set.');

        $params = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
        ];

        $response = $this->squareGateway->updateCustomer(self::$customerId, $params);

        $this->assertNotNull($response);
        $this->assertEquals('Jane', $response->getCustomer()->getGivenName());
    }

    public function testCreateCustomerCard(){
        // Create a customer card
        $params = [
            'customer_id' => self::$customerId,
            'card_nonce' => 'cnon:card-nonce-ok'
        ];

        $response = $this->squareGateway->createCustomerCard($params['customer_id'], $params['card_nonce']);
        
        $this->assertNotNull($response);
        $this->assertObjectHasProperty('card', $response);
        $this->assertEquals(true, $response->getCard()->getEnabled());
    }

    public function testDeleteCustomer()
    {
        $this->assertNotNull(self::$customerId, 'Customer ID is not set.');

        $response = $this->squareGateway->deleteCustomer(self::$customerId);

        $this->assertNotNull($response);
        $this->assertNull($response->getErrors(), 'Customer deletion failed with errors: ' . json_encode($response->getErrors()));

    }
}
