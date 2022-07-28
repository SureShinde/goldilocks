<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DateSchedule\Type;

use Amasty\DeliveryDateManager\Model\DateSchedule\Type;

/**
 * Date conversion to output according to schedule type
 */
class ConvertToStorable
{
    /**
     * @param int $type
     * @param string $date
     * @return string
     */
    public function execute(int $type, string $date): string
    {
        switch ($type) {
            case Type::DAY_OF_YEAR:
                $date = new \DateTime($date, new \DateTimeZone('UTC'));
                $date = '1970-' . $date->format('m-d');
                break;
            case Type::DAY_OF_MONTH:
                $date = '1970-01-' . $date;
                break;
            case Type::DAY_OF_WEEK:
                $date = date('Y-m-d', strtotime('next ' . $date));
                break;
        }

        return $date;
    }
}
