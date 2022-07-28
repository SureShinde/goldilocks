<?php
declare(strict_types=1);

namespace Magenest\CustomDeliveryTime\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class CheckoutConfigTimeProvider
 * @package Magenest\CustomDeliveryTime\Model
 */
class CheckoutConfigTimeProvider implements ConfigProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CheckoutConfigTimeProvider constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return \array[][]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig(): array
    {
        $quote = $this->checkoutSession->getQuote();
        $storeID = $this->storeManager->getStore()->getId();
        $subTotal = $quote->getData('subtotal');
        $limitSubtotal = $this->getLimitSubtotal($storeID) ?: 0;
        $minimumDeliveryTime = $this->getMinimumDeliveryTime($storeID) ?: 0;
        return [
            'deliveryconfig' => [
                'deliverytime' => [
                    'limitSubtotal' => $limitSubtotal,
                    'minimumDeliveryTime' => $minimumDeliveryTime,
                    'subTotal' => $subTotal
                ]
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getLimitSubtotal($storeID)
    {
        return $this->scopeConfig->getValue('magenest_bulk_order/general/bulk_order_limit_subtotal',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeID);
    }

    /**
     * @return mixed
     */
    public function getMinimumDeliveryTime($storeID)
    {
        return $this->scopeConfig->getValue('magenest_bulk_order/general/bulk_order_minimum_delivery_time',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeID);
    }
}
