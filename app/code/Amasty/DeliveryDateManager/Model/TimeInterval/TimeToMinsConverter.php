<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval;

class TimeToMinsConverter
{
    public const FIRST_UNIX_DAY = '01 Jan 1970 GMT ';

    /**
     * @param string $time
     * @return int
     */
    public function execute(string $time): int
    {
        return (int)strtotime(self::FIRST_UNIX_DAY . $time) / 60;
    }
}
