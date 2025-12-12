<?php

namespace YourVendor\PosOrder;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use YourVendor\PosOrder\Exceptions\PosOrderException;

class PosOrderService
{
    protected $client;
    protected $apiUrl;
    protected $apiKey;
    protected $defaultTerminalId;
    protected $timeout;
    protected $logging;

    // Payment type constants
    const TYPE_CARD_PURCHASE = 'card_purchase';
    const TYPE_PAY_WITH_PHONE = 'pay_with_phone';
    const TYPE_USSD = 'ussd';

    public function __construct()
    {
        $this->apiUrl = config('pos-order.api_url');
        $this->apiKey = config('pos-order.api_key');
        $this->defaultTerminalId = config('pos-order.default_terminal_id');
        $this->timeout = config('pos-order.timeout', 60);
        $this->logging = config('pos-order.logging', false);

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Add API key to headers if configured
        if (!empty($this->apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'timeout' => $this->timeout,
            'headers' => $headers,
        ]);
    }

    /**
     * Create a POS order
     *
     * @param array $data Order data
     * @return array API response
     * @throws PosOrderException
     */
    public function createOrder(array $data)
    {
        // Validate required fields
        if (!isset($data['amount']) || empty($data['amount'])) {
            throw new PosOrderException('The amount field is required.');
        }

        if (!isset($data['type']) || empty($data['type'])) {
            throw new PosOrderException('The type field is required.');
        }

        // Validate payment type
        $validTypes = [self::TYPE_CARD_PURCHASE, self::TYPE_PAY_WITH_PHONE, self::TYPE_USSD];
        if (!in_array($data['type'], $validTypes)) {
            throw new PosOrderException('Invalid payment type. Must be one of: ' . implode(', ', $validTypes));
        }

        // Set default terminal ID if not provided
        if (!isset($data['terminal_id']) || empty($data['terminal_id'])) {
            if (empty($this->defaultTerminalId)) {
                throw new PosOrderException('Terminal ID is required. Set it in the request or configure a default terminal ID.');
            }
            $data['terminal_id'] = $this->defaultTerminalId;
        }

        // Set optional fields
        $data['order_number'] = $data['order_number'] ?? '';
        $data['callback_url'] = $data['callback_url'] ?? '';

        try {
            $this->log('Creating POS order', $data);

            $response = $this->client->post('/v1/pos-order', [
                'json' => $data,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            $this->log('POS order created', $result);

            return $result;
        } catch (GuzzleException $e) {
            $this->log('Error creating POS order', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            $response = $e->hasResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : null;
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;

            throw new PosOrderException(
                'Failed to create POS order: ' . $e->getMessage(),
                $e->getCode(),
                $response,
                $statusCode
            );
        }
    }

    /**
     * Retry a POS order
     *
     * @param string|int $orderId Order ID
     * @return array API response
     * @throws PosOrderException
     */
    public function retryOrder($orderId)
    {
        if (empty($orderId)) {
            throw new PosOrderException('Order ID is required.');
        }

        try {
            $this->log('Retrying POS order', ['order_id' => $orderId]);

            $response = $this->client->get("/v1/retry-pos-order/{$orderId}");

            $result = json_decode($response->getBody()->getContents(), true);

            $this->log('POS order retry initiated', $result);

            return $result;
        } catch (GuzzleException $e) {
            $this->log('Error retrying POS order', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            $response = $e->hasResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : null;
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;

            throw new PosOrderException(
                'Failed to retry POS order: ' . $e->getMessage(),
                $e->getCode(),
                $response,
                $statusCode
            );
        }
    }

    /**
     * Get POS order status
     *
     * @param string|int $orderId Order ID
     * @param string|null $terminalId Terminal ID (optional, uses default if not provided)
     * @return array API response
     * @throws PosOrderException
     */
    public function getOrderStatus($orderId, $terminalId = null)
    {
        if (empty($orderId)) {
            throw new PosOrderException('Order ID is required.');
        }

        // Use default terminal ID if not provided
        if (empty($terminalId)) {
            if (empty($this->defaultTerminalId)) {
                throw new PosOrderException('Terminal ID is required. Provide it or configure a default terminal ID.');
            }
            $terminalId = $this->defaultTerminalId;
        }

        try {
            $this->log('Checking POS order status', [
                'order_id' => $orderId,
                'terminal_id' => $terminalId,
            ]);

            $response = $this->client->get("/v1/pos-order-status/{$terminalId}/{$orderId}");

            $result = json_decode($response->getBody()->getContents(), true);

            $this->log('POS order status retrieved', $result);

            return $result;
        } catch (GuzzleException $e) {
            $this->log('Error checking POS order status', [
                'order_id' => $orderId,
                'terminal_id' => $terminalId,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            $response = $e->hasResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : null;
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;

            throw new PosOrderException(
                'Failed to get POS order status: ' . $e->getMessage(),
                $e->getCode(),
                $response,
                $statusCode
            );
        }
    }

    /**
     * Create a card purchase order
     *
     * @param float $amount Amount to charge
     * @param string|null $terminalId Terminal ID (optional)
     * @param string|null $orderNumber Order number (optional)
     * @param string|null $callbackUrl Callback URL (optional)
     * @return array API response
     * @throws PosOrderException
     */
    public function cardPurchase($amount, $terminalId = null, $orderNumber = null, $callbackUrl = null)
    {
        return $this->createOrder([
            'amount' => $amount,
            'type' => self::TYPE_CARD_PURCHASE,
            'terminal_id' => $terminalId ?? $this->defaultTerminalId,
            'order_number' => $orderNumber ?? '',
            'callback_url' => $callbackUrl ?? '',
        ]);
    }

    /**
     * Create a pay with phone order
     *
     * @param float $amount Amount to charge
     * @param string|null $terminalId Terminal ID (optional)
     * @param string|null $orderNumber Order number (optional)
     * @param string|null $callbackUrl Callback URL (optional)
     * @return array API response
     * @throws PosOrderException
     */
    public function payWithPhone($amount, $terminalId = null, $orderNumber = null, $callbackUrl = null)
    {
        return $this->createOrder([
            'amount' => $amount,
            'type' => self::TYPE_PAY_WITH_PHONE,
            'terminal_id' => $terminalId ?? $this->defaultTerminalId,
            'order_number' => $orderNumber ?? '',
            'callback_url' => $callbackUrl ?? '',
        ]);
    }

    /**
     * Create a USSD order
     *
     * @param float $amount Amount to charge
     * @param string|null $terminalId Terminal ID (optional)
     * @param string|null $orderNumber Order number (optional)
     * @param string|null $callbackUrl Callback URL (optional)
     * @return array API response
     * @throws PosOrderException
     */
    public function ussdPayment($amount, $terminalId = null, $orderNumber = null, $callbackUrl = null)
    {
        return $this->createOrder([
            'amount' => $amount,
            'type' => self::TYPE_USSD,
            'terminal_id' => $terminalId ?? $this->defaultTerminalId,
            'order_number' => $orderNumber ?? '',
            'callback_url' => $callbackUrl ?? '',
        ]);
    }

    /**
     * Generate a unique order number
     *
     * @param string $prefix Optional prefix for the order number
     * @return string Unique order number
     */
    public function generateOrderNumber($prefix = 'POS')
    {
        return $prefix . '_' . time() . '_' . uniqid();
    }

    /**
     * Get available payment types
     *
     * @return array Payment types
     */
    public function getPaymentTypes()
    {
        return config('pos-order.payment_types', [
            self::TYPE_CARD_PURCHASE => 'Card Purchase',
            self::TYPE_PAY_WITH_PHONE => 'Pay with Phone',
            self::TYPE_USSD => 'USSD',
        ]);
    }

    /**
     * Log messages if logging is enabled
     *
     * @param string $message Log message
     * @param array $context Additional context
     * @return void
     */
    protected function log($message, array $context = [])
    {
        if ($this->logging) {
            Log::info('[POS Order] ' . $message, $context);
        }
    }

    /**
     * Set a custom API key (useful for multi-tenant applications)
     *
     * @param string $apiKey API key
     * @return self
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if (!empty($apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'timeout' => $this->timeout,
            'headers' => $headers,
        ]);

        return $this;
    }

    /**
     * Set a custom terminal ID
     *
     * @param string $terminalId Terminal ID
     * @return self
     */
    public function setTerminalId($terminalId)
    {
        $this->defaultTerminalId = $terminalId;
        return $this;
    }

    /**
     * Get the current API key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Get the current terminal ID
     *
     * @return string
     */
    public function getTerminalId()
    {
        return $this->defaultTerminalId;
    }
}