<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api;

/**
 * @api
 */
interface DeliveryGuestQuoteServiceInterface
{
    /**
     * @param string $cartId
     * @param int $quoteAddressId
     *
     * @return \Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterface
     */
    public function getFromQuoteAddressId(
        string $cartId,
        int $quoteAddressId
    ): \Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterface;

    /**
     * @param string $cartId
     * @param int $quoteAddressId
     * @param string|null $date
     * @param int|null $timeIntervalId
     * @param string|null $comment
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function validate(
        string $cartId,
        int $quoteAddressId,
        ?string $date,
        ?int $timeIntervalId = null,
        ?string $comment = null
    ): bool;

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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveForQuoteAddress(
        string $cartId,
        int $quoteAddressId,
        ?string $date,
        ?int $timeIntervalId,
        ?string $comment
    ): bool;
}
