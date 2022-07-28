<?php

declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Plugin\Sales\Model;

use Amasty\StorePickupWithLocator\Api\OrderRepositoryInterface;
use Amasty\StorePickupWithLocator\Api\QuoteRepositoryInterface;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\StorePickupWithLocator\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Add store pickup information to shipping description
 * TODO: The implementation can be changed to introduction block,
 * for pdf you can look at \Magento\Sales\Model\Order\Pdf\AbstractPdf
 */
class AddShippingDescription
{
    /**
     * @var OrderRepositoryInterface
     */
    private $pickupOrderRepository;

    /**
     * @var QuoteRepositoryInterface
     */
    private $pickupQuoteRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var array
     */
    private $pickupOrders = [];

    public function __construct(
        OrderRepositoryInterface $pickupOrderRepository,
        QuoteRepositoryInterface $pickupQuoteRepository,
        ConfigProvider $configProvider
    ) {
        $this->pickupOrderRepository = $pickupOrderRepository;
        $this->pickupQuoteRepository = $pickupQuoteRepository;
        $this->configProvider = $configProvider;
    }

    /**
     * @param Order $subject
     * @param string|null $result
     * @return string|null
     */
    public function afterGetShippingDescription(Order $subject, ?string $result): ?string
    {
        if (!$this->configProvider->isStorePickupEnabled()) {
            return $result;
        }

        $curbsideInfo = '';
        $curbsideLabel = '';
        $orderId = $subject->getId();

        if (!$orderId) {
            $this->pickupOrders[$orderId] = $this->getPickupQuote((int)$subject->getQuoteId());
        }

        if (empty($this->pickupOrders[$orderId])) {
            $this->pickupOrders[$orderId] = $this->pickupOrderRepository->getByOrderId($orderId);
        }

        $pickupOrder = $this->pickupOrders[$orderId];
        if ($pickupOrder->getIsCurbsidePickup()) {
            $curbsideLabel = ",\r\n" . $this->configProvider->getCurbsideCheckboxLabel();
        }

        if ($comment = $pickupOrder->getCurbsidePickupComment()) {
            $curbsideInfo = ",\r\n" . __('Pickup Details') . ":\r\n" . $comment;
        }

        return $result . $curbsideLabel . $curbsideInfo;
    }

    /**
     * For curbside pickup description in email when order_id is null
     * @since magento 2.4.4
     *
     * @param int $quoteId
     * @return Quote
     */
    private function getPickupQuote(int $quoteId): Quote
    {
        return $this->pickupQuoteRepository->getByQuoteId($quoteId);
    }
}
