<?php

namespace App\PaymentGateways;

use Square\Exceptions\ApiException;
use Square\Models\CreateCustomerRequest;
use Square\Models\UpdateCustomerRequest;
use Square\Models\CreatePaymentRequest;
use Square\Models\CreateSubscriptionRequest;
use Square\SquareClientBuilder;
use Square\Authentication\BearerAuthCredentialsBuilder;
use Square\Models\Money;
use Square\Models\CreateCustomerCardRequest;

class SquareGateway
{
    private $client;
    private $config = [
        'access_token' => 'EAAAl7A5yrjgadi1IS_GTNr_-BvYmLRS3kkgGVMmFg7vUjVi2sxpj7xqFAnMLtmy',
        'environment' => 'sandbox', //production
        'location_id' => 'L12VC933ACRZP',
        'currency' => 'USD',
        'plan_variation_id' => 'TGMOV2Y7DX2WROD4G5GUSNWV',
    ];

    public function __construct()
    {
        $this->client = SquareClientBuilder::init()
        ->bearerAuthCredentials(
            BearerAuthCredentialsBuilder::init(
                env('SQUARE_ACCESS_TOKEN', $this->config['access_token'])
            )
        )
        ->environment(env('SQUARE_ENVIRONMENT', $this->config['environment'])) //Environment::PRODUCTION
        ->build();
    }

    public function createCustomer(array $params)
    {
        $request = new CreateCustomerRequest;
        $request->setGivenName($params['first_name'] ?? null);
        $request->setFamilyName($params['last_name'] ?? null);
        $request->setEmailAddress($params['email'] ?? null);
        $request->setPhoneNumber($params['phone'] ?? null);

        try {
            $response = $this->client->getCustomersApi()->createCustomer($request);
            return $response->getResult();
        } catch (ApiException $e) {
            return $e->getMessage();
        }
    }

    public function updateCustomer(string $customerId, array $params)
    {
        $request = new UpdateCustomerRequest;
        $request->setGivenName($params['first_name'] ?? null);
        $request->setFamilyName($params['last_name'] ?? null);
        $request->setEmailAddress($params['email'] ?? null);
        $request->setPhoneNumber($params['phone'] ?? null);

        try {
            $response = $this->client->getCustomersApi()->updateCustomer($customerId, $request);
            return $response->getResult();
        } catch (ApiException $e) {
            return $e->getMessage();
        }
    }

    public function deleteCustomer(string $customerId)
    {
        try {
            $response = $this->client->getCustomersApi()->deleteCustomer($customerId);
            return $response->getResult();
        } catch (ApiException $e) {
            return $e->getMessage();
        }
    }

    // method to create customer card iwht Square API
    public function createCustomerCard(string $customerId, string $cardNonce)
    {
        // create customer card with Square API
        $request = new CreateCustomerCardRequest($cardNonce);

        try {
            $response = $this->client->getCustomersApi()->createCustomerCard($customerId, $request);
            return $response->getResult();
        } catch (ApiException $e) {
            return $e->getMessage();
        }

    }

    public function createPayment(array $params)
    {
        $amount = (int) ($params['amount'] * 100); // Convert to cents
        $money = new Money();
        $money->setAmount($amount);
        $money->setCurrency(env('SQUARE_CURRENCY', $this->config['currency']));

        $request = new CreatePaymentRequest(
            $params['source_id'],
            uniqid()
        );
        $request->setAmountMoney($money);

        try {
            $response = $this->client->getPaymentsApi()->createPayment($request);
            return $response->getResult();
        } catch (ApiException $e) {
            return $e->getMessage();
        }
    }


    public function cancelPayment(string $paymentId)
    {
        try {
            $response = $this->client->getPaymentsApi()->cancelPayment($paymentId);
            return $response->getResult();
        } catch (ApiException $e) {
            return $e->getMessage();
        }
    }

    public function createSubscription(array $params)
    {
        $request = new CreateSubscriptionRequest(
            env('SQUARE_LOCATION_ID', $this->config['location_id']),
            // $params['plan_id'],
            $params['customer_id'],
        );

        $request->setPlanVariationId(env('SQUARE_PLAN_VARIATION_ID', $this->config['plan_variation_id']));

        // set start date to today to charge immediately
        $startDate = now()->toDateString();
        $request->setStartDate($startDate);

        // Specify the customer card ID
        if (!empty($params['card_id'])) {
            $request->setCardId($params['card_id']); // Use the default card
        }

        try {
            $response = $this->client->getSubscriptionsApi()->createSubscription($request);
            return $response->getResult();
        } catch (ApiException $e) {
            return $e->getMessage();
        }
    }

    public function cancelSubscription(string $subscriptionId)
    {
        try {
            $response = $this->client->getSubscriptionsApi()->cancelSubscription($subscriptionId);
            return $response->getResult();
        } catch (ApiException $e) {
            return $e->getMessage();
        }
    }

    public function verifyPayment(string $paymentId)
    {
        try {
            $response = $this->client->getPaymentsApi()->getPayment($paymentId);
            return $response->getResult();
        } catch (ApiException $e) {
            return $e->getMessage();
        }
    }


    public function handleCallback(array $callbackData)
    {
        return $callbackData;
    }

}
