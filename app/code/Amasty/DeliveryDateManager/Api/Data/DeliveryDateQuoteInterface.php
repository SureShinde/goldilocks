<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Api\Data;

interface DeliveryDateQuoteInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const DELIVERY_QUOTE_ID = 'delivery_quote_id';
    public const QUOTE_ID = 'quote_id';
    public const QUOTE_ADDRESS_ID = 'quote_address_id';
    public const DATE = 'date';
    public const COMMENT = 'comment';
    public const TIME_INTERVAL_ID = 'time_interval_id';

    /**
     * @return int
     */
    public function getDeliveryQuoteId(): int;

    /**
     * @param int $deliveryQuoteId
     *
     * @return void
     */
    public function setDeliveryQuoteId(int $deliveryQuoteId): void;

    /**
     * @return int
     */
    public function getQuoteId(): int;

    /**
     * @param int $quoteId
     *
     * @return void
     */
    public function setQuoteId(int $quoteId): void;

    /**
     * @return int
     */
    public function getQuoteAddressId(): int;

    /**
     * @param int $quoteAddressId
     *
     * @return void
     */
    public function setQuoteAddressId(int $quoteAddressId): void;

    /**
     * Date string in ISO format
     *
     * @return string|null
     */
    public function getDate(): ?string;

    /**
     * @param string|null $date
     *
     * @return void
     */
    public function setDate(?string $date): void;

    /**
     * @return string|null
     */
    public function getComment(): ?string;

    /**
     * @param string|null $comment
     *
     * @return void
     */
    public function setComment(?string $comment): void;

    /**
     * @return int|null
     */
    public function getTimeIntervalId(): ?int;

    /**
     * @param int|null $timeIntervalId
     *
     * @return void
     */
    public function setTimeIntervalId(?int $timeIntervalId): void;
}
