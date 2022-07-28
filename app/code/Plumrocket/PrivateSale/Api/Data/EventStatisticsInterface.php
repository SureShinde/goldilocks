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

interface EventStatisticsInterface
{
    const ENTITY_ID = 'entity_id';
    const EVENT_ID = 'event_id';
    const CUSTOMER_ID = 'customer_id';
    const ORDER_ID = 'order_id';
    const ITEM_ID = 'item_id';
    const CREATED_DATE = 'created_date';

    /**
     * @return int
     */
    public function getEntityId(): int;

    /**
     * @return int
     */
    public function getEventId(): int;

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @return int
     */
    public function getItemId(): int;

    /**
     * @return string
     */
    public function getCreatedDate(): string;

    /**
     * @param int $id
     * @return EventStatisticsInterface
     */
    public function setEventId(int $id): EventStatisticsInterface;

    /**
     * @param int $id
     * @return EventStatisticsInterface
     */
    public function setCustomerId(int $id): EventStatisticsInterface;

    /**
     * @param int $id
     * @return EventStatisticsInterface
     */
    public function setOrderId(int $id): EventStatisticsInterface;

    /**
     * @param int $id
     * @return EventStatisticsInterface
     */
    public function setItemId(int $id): EventStatisticsInterface;

    /**
     * @param string $date
     * @return EventStatisticsInterface
     */
    public function setCreatedDate(string $date): EventStatisticsInterface;
}
