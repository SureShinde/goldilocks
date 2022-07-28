<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\OrderLimit\Restricted;

use Amasty\DeliveryDateManager\Api\Data\RestrictedDateInterface;
use Amasty\DeliveryDateManager\Api\Data\RestrictedTimeIntervalInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Exceeded order limit day.
 * @api
 */
class RestrictedDate extends AbstractSimpleObject implements RestrictedDateInterface
{
    /**
     * @param RestrictedTimeIntervalInterface $timeInterval
     */
    public function addInterval(RestrictedTimeIntervalInterface $timeInterval): void
    {
        $intervals = $this->getIntervals() ?? [];
        array_push($intervals, $timeInterval);
        $this->setData(self::INTERVALS, $intervals);
    }

    /**
     * @return string
     */
    public function getDay(): string
    {
        return $this->_get(self::DAY);
    }

    /**
     * Get restricted time intervals.
     * null time intervals mean all day is restricted.
     * @return RestrictedTimeIntervalInterface[]|null
     */
    public function getIntervals(): ?array
    {
        return $this->_get(self::INTERVALS);
    }
}
