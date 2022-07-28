<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api;

/**
 * @api
 */
interface DeliveryQuoteServiceInterface
{
    /**
     * @param int $cartId
     * @param int $quoteAddressId
     *
     * @return \Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFromQuoteAddressId(
        int $cartId,
        int $quoteAddressId
    ): \Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterface;

    /**
     * @param int $cartId
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
        int $cartId,
        int $quoteAddressId,
        ?string $date,
        ?int $timeIntervalId = null,
        ?string $comment = null
    ): bool;

    /**
     * Set delivery date information for address of current quote.
     *
     * @param int $cartId
     * @param int $quoteAddressId
     * @param string|null $date - in ISO format 'yyyy-mm-dd'
     * @param int|null $timeIntervalId
     * @param string|null $comment
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveForQuoteAddress(
        int $cartId,
        int $quoteAddressId,
        ?string $date,
        ?int $timeIntervalId,
        ?string $comment
    ): bool;
}
