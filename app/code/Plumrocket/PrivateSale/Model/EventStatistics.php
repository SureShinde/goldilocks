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

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model;

use Magento\Framework\Model\AbstractModel;
use Plumrocket\PrivateSale\Api\Data\EventStatisticsInterface;

/**
 * @since 5.0.0
 */
class EventStatistics extends AbstractModel implements EventStatisticsInterface
{
    const EVENT_TYPE = 'event';
    const HOMEPAGE_TYPE = 'homepage';

    protected $_idFieldName = 'entity_id';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\EventStatistics::class);
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return (int) $this->getData(self::ENTITY_ID);
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return (int) $this->getData(self::EVENT_ID);
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return (int) $this->getData(self::ORDER_ID);
    }

    /**
     * @return int
     */
    public function getItemId(): int
    {
        return (int) $this->getData(self::ITEM_ID);
    }

    /**
     * @return string
     */
    public function getCreatedDate(): string
    {
        return (string) $this->getData(self::CREATED_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setEventId(int $id): EventStatisticsInterface
    {
        $this->setData(self::EVENT_ID, $id);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $id): EventStatisticsInterface
    {
        $this->setData(self::CUSTOMER_ID, $id);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOrderId(int $id): EventStatisticsInterface
    {
        $this->setData(self::ORDER_ID, $id);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setItemId(int $id): EventStatisticsInterface
    {
        $this->setData(self::ITEM_ID, $id);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCreatedDate(string $date): EventStatisticsInterface
    {
        $this->setData(self::CREATED_DATE, $date);
        return $this;
    }
}
