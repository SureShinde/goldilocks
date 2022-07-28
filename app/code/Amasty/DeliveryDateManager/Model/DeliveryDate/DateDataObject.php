<?php

namespace Amasty\DeliveryDateManager\Model\DeliveryDate;

use Amasty\DeliveryDateManager\Model\ChannelSetResults;

class DateDataObject extends \Magento\Framework\DataObject
{
    /**
     * Set date as string in format yyyy-MM-dd
     *
     * @param string $value
     */
    public function setDate(string $value)
    {
        $this->setData('date', $value);
    }

    /**
     * @param int $value
     */
    public function setTimestamp(int $value)
    {
        $this->setData('timestamp', $value);
    }

    /**
     * @param string|int $value
     */
    public function setYear($value)
    {
        $this->setData('year', $value);
    }

    /**
     * @param string|int $value
     */
    public function setMonth($value)
    {
        $this->setData('month', $value);
    }

    /**
     * @param string|int $value
     */
    public function setDay($value)
    {
        $this->setData('day', $value);
    }

    /**
     * @param \Zend_Date $value
     */
    public function setObject($value)
    {
        $this->setData('object', $value);
    }

    public function setChannelSet(ChannelSetResults $channelSet)
    {
        $this->setData('channel_set', $channelSet);
    }

    /**
     * Get date as string in format yyyy-MM-dd
     *
     * @return string
     */
    public function getDate()
    {
        return $this->getData('date');
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->getData('timestamp');
    }

    /**
     * @return string|int
     */
    public function getYear()
    {
        return $this->getData('year');
    }

    /**
     * @return string|int
     */
    public function getMonth()
    {
        return $this->getData('month');
    }

    /**
     * @return string|int
     */
    public function getDay()
    {
        return $this->getData('day');
    }

    /**
     * @return \Zend_Date
     */
    public function getObject()
    {
        return $this->getData('object');
    }

    public function getChannelSet(): ChannelSetResults
    {
        return $this->getData('channel_set');
    }
}
