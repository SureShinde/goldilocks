<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryQuote;

class Guest implements \Amasty\DeliveryDateManager\Api\DeliveryGuestQuoteServiceInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    private $maskFactory;

    /**
     * @var Manager
     */
    private $deliveryQuoteManager;

    public function __construct(\Magento\Quote\Model\QuoteIdMaskFactory $maskFactory, Manager $deliveryQuoteManager)
    {
        $this->maskFactory = $maskFactory;
        $this->deliveryQuoteManager = $deliveryQuoteManager;
    }

    /**
     * @param string $cartId
     * @param int $quoteAddressId
     *
     * @return \Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterface
     */
    public function getFromQuoteAddressId(
        string $cartId,
        int $quoteAddressId
    ): \Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterface {
        return $this->deliveryQuoteManager->getFromQuoteAddressId(
            $this->convertMaskToQuoteId($cartId),
            $quoteAddressId
        );
    }

    /**
     * @param string $cartId
     * @param int $quoteAddressId
     * @param string|null $date
     * @param int|null $timeIntervalId
     * @param string|null $comment
     *
     * @return bool
     */
    public function validate(
        string $cartId,
        int $quoteAddressId,
        ?string $date,
        ?int $timeIntervalId = null,
        ?string $comment = null
    ): bool {
        return $this->deliveryQuoteManager->validate(
            $this->convertMaskToQuoteId($cartId),
            $quoteAddressId,
            $date,
            $timeIntervalId,
            $comment
        );
    }

    /**
     * Set delivery date information for address of current quote.
     *
     * @param string $cartId
     * @param int $quoteAddressId
     * @param string|null $date - in ISO format 'yyyy-mm-dd'
     * @param int|null $timeIntervalId
     * @param string|null $comment
     *
     * @return bool
     */
    public function saveForQuoteAddress(
        string $cartId,
        int $quoteAddressId,
        ?string $date,
        ?int $timeIntervalId,
        ?string $comment
    ): bool {
        return $this->deliveryQuoteManager->saveForQuoteAddress(
            $this->convertMaskToQuoteId($cartId),
            $quoteAddressId,
            $date,
            $timeIntervalId,
            $comment
        );
    }

    /**
     * @param string $mask
     *
     * @return int
     */
    private function convertMaskToQuoteId(string $mask): int
    {
        return (int)$this->maskFactory->create()->load($mask, 'masked_id')->getQuoteId();
    }
}
