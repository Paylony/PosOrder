<?php

namespace YourVendor\PosOrder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array createOrder(array $data)
 * @method static array retryOrder(string|int $orderId)
 * @method static array getOrderStatus(string|int $orderId, string|null $terminalId = null)
 * @method static array cardPurchase(float $amount, string|null $terminalId = null, string|null $orderNumber = null, string|null $callbackUrl = null)
 * @method static array payWithPhone(float $amount, string|null $terminalId = null, string|null $orderNumber = null, string|null $callbackUrl = null)
 * @method static array ussdPayment(float $amount, string|null $terminalId = null, string|null $orderNumber = null, string|null $callbackUrl = null)
 * @method static string generateOrderNumber(string $prefix = 'POS')
 * @method static array getPaymentTypes()
 * @method static self setApiKey(string $apiKey)
 * @method static self setTerminalId(string $terminalId)
 * @method static string getApiKey()
 * @method static string getTerminalId()
 *
 * @see \YourVendor\PosOrder\PosOrderService
 */
class PosOrder extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'pos-order';
    }
}