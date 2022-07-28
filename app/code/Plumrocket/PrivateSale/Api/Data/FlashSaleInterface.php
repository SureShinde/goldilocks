<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\PrivateSale\Api\Data;

interface FlashSaleInterface
{
    const PRODUCT_ID = 'product_id';
    const EVENT_ID = 'event_id';
    const DISCOUNT = 'discount_amount_percent';
    const SALE_PRICE = 'sale_price';
    const QTY_LIMIT = 'flash_sale_qty_limit';

    /**
     * @return int
     */
    public function getProductId(): int;

    /**
     * @return int
     */
    public function getEventId(): int;

    /**
     * @return float|null
     */
    public function getDiscount();

    /**
     * @return float|null
     */
    public function getSalePrice();

    /**
     * @return int
     */
    public function getQtyLimit(): int;

    /**
     * @param int $id
     * @return FlashSaleInterface
     */
    public function setProductId(int $id): FlashSaleInterface;

    /**
     * @param int $id
     * @return FlashSaleInterface
     */
    public function setEventId(int $id): FlashSaleInterface;

    /**
     * @param $discount
     * @return FlashSaleInterface
     */
    public function setDiscount($discount): FlashSaleInterface;

    /**
     * @param $salePrice
     * @return FlashSaleInterface
     */
    public function setSalePrice($salePrice): FlashSaleInterface;

    /**
     * @param int $qtyLimit
     * @return FlashSaleInterface
     */
    public function setQtyLimit(int $qtyLimit): FlashSaleInterface;
}
