<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DateSchedule;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;

class Validator
{
    /**
     * @var Type
     */
    private $scheduleType;

    public function __construct(Type $scheduleType)
    {
        $this->scheduleType = $scheduleType;
    }

    /**
     * Is Unix timestamp in Date Schedule range
     *
     * @param int $timestamp
     * @param DateScheduleInterface $dateSchedule
     *
     * @return bool
     */
    public function isDateInDateSchedule(int $timestamp, DateScheduleInterface $dateSchedule): bool
    {
        $input = $this->scheduleType->convertToComparable($dateSchedule->getType(), $timestamp);
        $from = $this->scheduleType->convertToComparable($dateSchedule->getType(), strtotime($dateSchedule->getFrom()));
        $to = $this->scheduleType->convertToComparable($dateSchedule->getType(), strtotime($dateSchedule->getTo()));

        // Situation when range start is "end" of the week, but range end is the "start" of the week
        // For example: from Friday to Monday
        if (($from > $to) && ($dateSchedule->getType() !== Type::STRICT)) {
            return ($from <= $input) || ($to >= $input);
        }

        return ($from <= $input) && ($to >= $input);
    }
}
