<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\OrderLimit;

use Amasty\DeliveryDateManager\Api\Data\ChannelSetResultsInterface;
use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Model\AbstractTypifiedModel;

/**
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit getResource()
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit\Collection getCollection()
 */
class LimitDataModel extends AbstractTypifiedModel implements OrderLimitInterface
{
    public const CACHE_TAG = 'amdeliv_limit';

    /**
     * @var string[]
     */
    protected $_cacheTag = [self::CACHE_TAG, ChannelSetResultsInterface::CACHE_TAG];

    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_deliverydate_orderlimit';

    protected function _construct()
    {
        $this->_init(\Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit::class);
    }

    /**
     * @return int
     */
    public function getLimitId(): int
    {
        return (int)$this->_getData(OrderLimitInterface::LIMIT_ID);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->_getData(OrderLimitInterface::NAME);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->setData(OrderLimitInterface::NAME, $name);
    }

    /**
     * @param int|null $limitId
     *
     * @return void
     */
    public function setLimitId(?int $limitId): void
    {
        $this->setData(OrderLimitInterface::LIMIT_ID, $limitId);
    }

    /**
     * @return int|null
     */
    public function getDayLimit(): ?int
    {
        $data = $this->_getData(OrderLimitInterface::DAY_LIMIT);
        if ($data === null || $data === '') {
            return null;
        }

        return (int)$data;
    }

    /**
     * @param int|null $dayLimit
     *
     * @return void
     */
    public function setDayLimit(?int $dayLimit): void
    {
        $this->setData(OrderLimitInterface::DAY_LIMIT, $dayLimit);
    }

    /**
     * @return int|null
     */
    public function getIntervalLimit(): ?int
    {
        $data = $this->_getData(OrderLimitInterface::INTERVAL_LIMIT);
        if ($data === null || $data === '') {
            return null;
        }

        return (int)$data;
    }

    /**
     * @param int|null $intervalLimit
     *
     * @return void
     */
    public function setIntervalLimit(?int $intervalLimit): void
    {
        $this->setData(OrderLimitInterface::INTERVAL_LIMIT, $intervalLimit);
    }
}
