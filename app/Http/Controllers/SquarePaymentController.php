<?php

namespace App\Http\Controllers;

use App\PaymentGateways\SquareGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Square\Utils\WebhooksHelper;

class SquarePaymentController extends Controller
{
    private $client = null;
    // show create customer form with card form
    public function create()
    {
        return view('create-customer');
    }

    public function store(Request $request)
    {
        // Handle payment
        // Get all input and store in a file named as time().txt
        $data = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:customers,email',
            'nonce' => 'required',
        ]);

        $customerData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ];

        $responseFlags = [
            'customer' => false,
            'card' => false,
            'card_error' => '',
            'customer_error' => '',
        ];

        $cardNonce = $data['nonce'];

        $squareGateway = new SquareGateway();
        $customer = $squareGateway->createCustomer($customerData);

        if (is_array($customer)) {
            $responseFlags['customer_error'] = isset($customer['errors']) ? $customer['errors'] : '';
        } else if (null !== $customer->getCustomer()->getId()) {
            // Customer Created
            $responseFlags['customer'] = true;

            $customerId = $customer->getCustomer()->getId();
            $customerData['square_id'] = $customerId;
            // Create Card
            $card = $squareGateway->createCustomerCard($customerId, $cardNonce);

            if (is_array($card)) {
                $responseFlags['card_error'] = isset($card['errors']) ? $card['errors'] : '';
            } else if (null !== $card->getCard()->getId()) {
                // Card Created
                $responseFlags['card'] = true;
                $customerData['square_card_id'] = $card->getCard()->getId();
            }

            $responseFlags['card_error'] = $card;
        }

        // Store customer in database
        if ($responseFlags['customer'] && $responseFlags['card']) {
            // Store customer in database
            $customerLocal = \App\Models\Customer::create($customerData);
            return response()->json($customerLocal, 200);
        }

        return response()->json(['message' => 'Failed to create customer, try again....', $responseFlags], 400);
    }

    // Callback method for Square payment
    public function handleCallback(Request $request)
    {
        
        // replace http to https from route route('square.callback')
        $url = str_replace('http://', 'https://', route('square.callback'));
        
        $signature = $request->header('x-square-hmacsha256-signature');
        $body = $request->getContent();
        $notificationUrl = env('NOTIFICATION_URL', $url);
        $signatureKey = env('SQUARE_WEBHOOK_SECRET', 'wY4nJ14rynUkVouIj7r2PQ');
        
        if (!WebhooksHelper::isValidWebhookEventSignature($body, $signature, $signatureKey, $notificationUrl)) {
            Log::error('Invalid request', ['signature' => $signature, 'key' => $signatureKey,'url' => $notificationUrl]);
            return response()->json(['message' => 'Invalid request'], 403);
        }
        
        $data = $request->all();

        switch ($data['type']) {
            case 'invoice.updated':
            case 'invoice.payment_made':
                $this->validateInvoicePayment($data);
                break;
            case 'payment.updated':
                $this->validatePayment($data);
                break;
            default:
                break;
        }

        return response()->json(['message' => 'Success'], 200);
    }

    // Method to Validate Invoice Payment Status
    private function validateInvoicePayment($data)
    {
        // Validate Invoice Payment
        $id = $data['data']['id'];

        $dataObject = $data['data']['object']['invoice'];

        $status = $dataObject['status']; // DRAFT | UNPAID | PAID | CANCELED
        $subcriptionId = $dataObject['subscription_id'];
        $customerId = $dataObject['primary_recipient']['customer_id'];

        $amount = $dataObject['payment_requests'][0]['computed_amount_money']['amount'];
        $currency = $dataObject['payment_requests'][0]['computed_amount_money']['currency'];

        $usdAmount = $amount / 100; // Convert to USD

        $logArray = [
            'id' => $id,
            'status' => $status,
            'amount' => $usdAmount,
            'currency' => $currency,
            'customer_id' => $customerId,
            'subscription_id' => $subcriptionId,
            // 'object' => $dataObject,
        ];

        // Check if invoice is paid
        if ($status != 'PAID') {
            // Update local db invoice with pending or draft status
            // or
            // Do nothing, because Invoice payment event will be triggered soon
            $logFile = "logs/final_test/invoice_pending_" . $id . '.txt';
            file_put_contents($logFile, json_encode($logArray, JSON_PRETTY_PRINT));
            return;
        }

        $logFile = "logs/final_test/invoice_paid_" . $id . '.txt';
        file_put_contents($logFile, json_encode($logArray, JSON_PRETTY_PRINT));
        // Invoice is paid, update local db
        // or
        // Still do nothing, because payment event will be triggered soon
        // Note: avoid to do anything here, this may cause duplicate payment
        return;
    }

    // Method to Validate Payment Status
    private function validatePayment($data)
    {
        // Validate Payment
        $id = $data['data']['id'];

        $dataObject = $data['data']['object']['payment'];

        $status = $dataObject['status']; // COMPLETED | CANCELED | FAILED
        $customerId = $dataObject['customer_id'];
        $amount = $dataObject['amount_money']['amount'];
        $currency = $dataObject['amount_money']['currency'];

        $usdAmount = $amount / 100; // Convert to USD

        $logArray = [
            'id' => $id,
            'status' => $status,
            'amount' => $usdAmount,
            'currency' => $currency,
            'customer_id' => $customerId,
            // 'object' => $dataObject,
        ];

        // Check if payment is successful
        if ($status != 'COMPLETED') {
            // Update db and mark the payment in local db as failed or unpaid
            $logFile = "logs/final_test/payment_pending_" . $id . '.txt';
            file_put_contents($logFile, json_encode($logArray, JSON_PRETTY_PRINT));
            return;
        }

        $logFile = "logs/final_test/payment_success_" . $id . '.txt';
        file_put_contents($logFile, json_encode($logArray, JSON_PRETTY_PRINT));
        // Payment is successful, create subscription
        // Invoice is Paid, Payment Event is triggered, now update db with payment status
        return;
    }
}
