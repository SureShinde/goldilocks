<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ChannelConfig;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface;
use Amasty\DeliveryDateManager\Api\Data\ChannelSetResultsInterface;
use Amasty\DeliveryDateManager\Model\AbstractTypifiedModel;

/**
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig getResource()
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig\Collection getCollection()
 */
class ConfigData extends AbstractTypifiedModel implements ChannelConfigDataInterface
{
    public const CACHE_TAG = 'amdeliv_conf';

    /**
     * @var string[]
     */
    protected $_cacheTag = [self::CACHE_TAG, ChannelSetResultsInterface::CACHE_TAG];

    protected function _construct()
    {
        $this->_init(\Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig::class);
    }

    public function getId(): ?int
    {
        $data = parent::getId();
        if ($data !== null && $data !== '') {
            return (int)$data;
        }

        return null;
    }

    /**
     * @param int|null $configId
     *
     * @return void
     */
    public function setConfigId(?int $configId): void
    {
        $this->setData(ChannelConfigDataInterface::ID, $configId);
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->setData(ChannelConfigDataInterface::NAME, $name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->getDataByKey(ChannelConfigDataInterface::NAME);
    }

    public function getMin(): ?int
    {
        $data = $this->getDataByKey(ChannelConfigDataInterface::MIN);
        if ($data !== null && $data !== '') {
            return (int)$data;
        }

        return null;
    }

    /**
     * @param int|null $min
     */
    public function setMin(?int $min): void
    {
        $this->setData(ChannelConfigDataInterface::MIN, $min);
    }

    /**
     * @return int|null
     */
    public function getMax(): ?int
    {
        $data = $this->getDataByKey(ChannelConfigDataInterface::MAX);
        if ($data !== null && $data !== '') {
            return (int)$data;
        }

        return null;
    }

    /**
     * @param int|null $max
     */
    public function setMax(?int $max): void
    {
        $this->setData(ChannelConfigDataInterface::MAX, $max);
    }

    /**
     * @return bool|null
     */
    public function getIsSameDayAvailable(): ?bool
    {
        $data = $this->getDataByKey(ChannelConfigDataInterface::IS_SAME_DAY_AVAILABLE);
        if ($data !== null) {
            return (bool)$data;
        }

        return null;
    }

    /**
     * @param bool|null $isAvailable
     */
    public function setSameDayAvailable(?bool $isAvailable): void
    {
        $this->setData(ChannelConfigDataInterface::IS_SAME_DAY_AVAILABLE, $isAvailable);
    }

    public function getSameDayCutoff(): ?int
    {
        $data = $this->getDataByKey(ChannelConfigDataInterface::SAME_DAY_CUTOFF);
        if ($data !== null && $data !== '') {
            return (int)$data;
        }

        return null;
    }

    /**
     * @param int|null $sameDayCutoff
     */
    public function setSameDayCutoff(?int $sameDayCutoff): void
    {
        $this->setData(ChannelConfigDataInterface::SAME_DAY_CUTOFF, $sameDayCutoff);
    }

    public function getOrderTime(): ?int
    {
        $data = $this->getDataByKey(ChannelConfigDataInterface::ORDER_TIME);
        if ($data !== null && $data !== '') {
            return (int)$data;
        }

        return null;
    }

    /**
     * @param int|null $orderTime
     */
    public function setOrderTime(?int $orderTime): void
    {
        $this->setData(ChannelConfigDataInterface::ORDER_TIME, $orderTime);
    }

    /**
     * @return int|null
     */
    public function getBackorderTime(): ?int
    {
        $data = $this->getDataByKey(ChannelConfigDataInterface::BACKORDER_TIME);
        if ($data !== null && $data !== '') {
            return (int)$data;
        }

        return null;
    }

    /**
     * @param int|null $backorderTime
     */
    public function setBackorderTime(?int $backorderTime): void
    {
        $this->setData(ChannelConfigDataInterface::BACKORDER_TIME, $backorderTime);
    }
}
