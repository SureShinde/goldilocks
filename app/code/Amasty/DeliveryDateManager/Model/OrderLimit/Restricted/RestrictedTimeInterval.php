<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\OrderLimit\Restricted;

use Amasty\DeliveryDateManager\Api\Data\RestrictedTimeIntervalInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Exceeded order limit time interval.
 * From To in minutes starts at 00:00
 * e.g. 00:00 = 0; 2:00 = 120;
 * @api
 */
class RestrictedTimeInterval extends AbstractSimpleObject implements RestrictedTimeIntervalInterface
{
    /**
     * Return Minutes
     * @return int
     */
    public function getFrom(): int
    {
        return $this->_get(self::KEY_FROM);
    }

    /**
     * Return Minutes
     * @return int
     */
    public function getTo(): int
    {
        return $this->_get(self::KEY_TO);
    }
}
