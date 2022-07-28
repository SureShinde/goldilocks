<?php

namespace Amasty\StorePickupWithLocator\Api\Data;

interface QuoteInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const ID = 'id';
    public const QUOTE_ID = 'quote_id';
    public const STORE_ID = 'store_id';
    public const ADDRESS_ID = 'address_id';
    public const DATE = 'date';
    public const TIME_FROM = 'time_from';
    public const TIME_TO = 'time_to';
    public const IS_CURBSIDE_PICKUP = 'is_curbside_pickup';
    public const CURBSIDE_PICKUP_COMMENT = 'curbside_pickup_comment';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $entityId
     * @return QuoteInterface
     */
    public function setId($entityId);

    /**
     * @return int|null
     */
    public function getQuoteId();

    /**
     * @param int|null $quoteId
     * @return QuoteInterface
     */
    public function setQuoteId($quoteId);

    /**
     * @return int|null
     */
    public function getStoreId();

    /**
     * @param int|null $storeId
     * @return QuoteInterface
     */
    public function setStoreId($storeId);

    /**
     * @return int|null
     */
    public function getAddressId();

    /**
     * @param int|null $addressId
     * @return QuoteInterface
     */
    public function setAddressId($addressId);

    /**
     * @return string|null
     */
    public function getDate();

    /**
     * @param string|null $date
     * @return QuoteInterface
     */
    public function setDate($date);

    /**
     * @return int|null
     */
    public function getTimeFrom();

    /**
     * @param int|null $time
     * @return QuoteInterface
     */
    public function setTimeFrom($time);

    /**
     * @return int|null
     */
    public function getTimeTo();

    /**
     * @param int|null $time
     * @return QuoteInterface
     */
    public function setTimeTo($time);

    /**
     * @return bool
     */
    public function getIsCurbsidePickup();

    /**
     * @param bool $isCurbsidePickup
     * @return $this
     */
    public function setIsCurbsidePickup(bool $isCurbsidePickup);

    /**
     * @return string
     */
    public function getCurbsidePickupComment();

    /**
     * @param string $comment
     * @return $this
     */
    public function setCurbsidePickupComment(string $comment);
}
