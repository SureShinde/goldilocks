<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Api\Data\OrderLimitInterface;
use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface;
use Amasty\DeliveryDateManager\Model\DateSchedule\Validator;
use Amasty\DeliveryDateManager\Model\OrderLimit\Get;

/**
 * Delivery Date Channel algorithms.
 * Time Interval > Date Schedule > Delivery Channel
 *
 * @api
 */
class ChannelSetProcessor
{
    /**
     * Default counter ID.
     */
    public const DEFAULT_COUNTER = 0;

    /**
     * @var DateSchedule\Validator
     */
    private $validator;

    /**
     * @var ChannelSetResults
     */
    private $channelSetResult;

    /**
     * @var OrderLimit\Get
     */
    private $getOrderLimit;

    public function __construct(
        Validator $validator,
        Get $getOrderLimit
    ) {
        $this->validator = $validator;
        $this->getOrderLimit = $getOrderLimit;
    }

    /**
     * @param ChannelSetResults $channelSetResult
     */
    public function setChannelSetResult(ChannelSetResults $channelSetResult): void
    {
        $this->channelSetResult = $channelSetResult;
    }

    /**
     * Return Order Limit for time range.
     * If time interval doesn't have limit, then get by date schedule, then by delivery channel.
     *
     * @param string $date e.g.'1970-01-01'
     * @param int $timeFrom
     * @param int $timeTo
     *
     * @return OrderLimitInterface|null
     */
    public function getLimitByTimeRange(string $date, int $timeFrom, int $timeTo): ?OrderLimitInterface
    {
        $timeInterval = $this->getTimeIntervalByRange($date, $timeFrom, $timeTo);

        if ($timeInterval && $timeInterval->getLimitId()) {
            return $this->getOrderLimit->execute($timeInterval->getLimitId());
        }

        return $this->getLimitByDate($date);
    }

    /**
     * @param string $date e.g.'1970-01-01'
     * @param int $timeFrom
     * @param int $timeTo
     *
     * @return TimeIntervalInterface|null
     */
    public function getTimeIntervalByRange(string $date, int $timeFrom, int $timeTo): ?TimeIntervalInterface
    {
        $dateSchedule = $this->getDateScheduleByDate($date);
        if ($dateSchedule === null || !$dateSchedule->getIsAvailable()) {
            return null;
        }

        $timeIds = [];
        foreach ($this->channelSetResult->getTimeDateLinks()->getItems() as $timeDateRelation) {
            if ($timeDateRelation->getDateScheduleId() === $dateSchedule->getScheduleId()) {
                $timeIds[] = $timeDateRelation->getTimeIntervalId();
            }
        }
        if ($byDate = $this->getTimeIntervalByIds($timeIds, $timeFrom, $timeTo)) {
            return $byDate;
        }

        return $this->getTimeIntervalFromChannelByRange($timeFrom, $timeTo);
    }

    /**
     * Returns Date Schedule entity by input date.
     *   Schedule sort order:
     *     1. By Priority of the channel
     *     2. By Schedule availability. Exclude schedules processing first
     *     3. By Schedule day type. "Strict day" processing first, "day of the week" - last.
     *
     * @param string $date e.g.'1970-01-01'
     *
     * @return DateScheduleInterface|null
     */
    public function getDateScheduleByDate(string $date): ?DateScheduleInterface
    {
        $timestamp = strtotime($date);
        foreach ($this->channelSetResult->getDeliveryChannel()->getItems() as $deliveryChannel) {
            $schedules = $this->getDateSchedulesByChannelId($deliveryChannel->getChannelId());
            foreach ($schedules as $dateSchedule) {
                if ($this->validator->isDateInDateSchedule($timestamp, $dateSchedule)) {
                    return $dateSchedule;
                }
            }
        }

        return null;
    }

    /**
     * @param int $channelId
     *
     * @return array|/Generator
     */
    private function getDateSchedulesByChannelId(int $channelId)
    {
        $dateScheduleIds = [];
        foreach ($this->channelSetResult->getDateChannelLinks()->getItems() as $relation) {
            if ($relation->getDeliveryChannelId() === $channelId) {
                $dateScheduleIds[] = $relation->getDateScheduleId();
            }
        }
        if (empty($dateScheduleIds)) {
            return [];
        }

        foreach ($this->channelSetResult->getDateScheduleItems()->getItems() as $dateSchedule) {
            if (\in_array($dateSchedule->getScheduleId(), $dateScheduleIds, true)) {
                yield $dateSchedule;
            }
        }

        return [];
    }

    /**
     * @param array $timeIds
     * @param int $timeFrom
     * @param int $timeTo
     *
     * @return TimeIntervalInterface|null
     */
    private function getTimeIntervalByIds(array $timeIds, int $timeFrom, int $timeTo): ?TimeIntervalInterface
    {
        foreach ($this->channelSetResult->getTimeIntervalItems()->getItems() as $timeInterval) {
            if (in_array($timeInterval->getIntervalId(), $timeIds)
                && $timeInterval->getFrom() === $timeFrom
                && $timeInterval->getTo() === $timeTo
            ) {
                return $timeInterval;
            }
        }

        return null;
    }

    /**
     * Returns Time Interval entity but only from Delivery Channel
     *
     * @param int $timeFrom
     * @param int $timeTo
     *
     * @return TimeIntervalInterface|null
     */
    public function getTimeIntervalFromChannelByRange(int $timeFrom, int $timeTo): ?TimeIntervalInterface
    {
        foreach ($this->channelSetResult->getDeliveryChannel()->getItems() as $channel) {
            $timeIds = [];
            foreach ($this->channelSetResult->getTimeChannelLinks()->getItems() as $timeChannelRelation) {
                if ($timeChannelRelation->getDeliveryChannelId() === $channel->getChannelId()) {
                    $timeIds[] = $timeChannelRelation->getTimeIntervalId();
                }
            }
            if ($byChannel = $this->getTimeIntervalByIds($timeIds, $timeFrom, $timeTo)) {
                return $byChannel;
            }
        }

        return null;
    }

    /**
     * Get order limit by date.
     * If date schedule doesn't have assigned limit then returned by channel.
     *
     * @param string $date
     *
     * @return OrderLimitInterface|null
     */
    public function getLimitByDate(string $date): ?OrderLimitInterface
    {
        $dateSchedule = $this->getDateScheduleByDate($date);
        if ($dateSchedule === null || !$dateSchedule->getIsAvailable()) {
            return null;
        }

        if ($limitId = $dateSchedule->getLimitId()) {
            return $this->getOrderLimit->execute($limitId);
        }

        return $this->getChannelLimit();
    }

    /**
     * Reference point for order limit.
     *
     * @return int
     */
    public function getCounterId(): int
    {
        $counterId = self::DEFAULT_COUNTER;
        foreach ($this->channelSetResult->getDeliveryChannel()->getItems() as $deliveryChannel) {
            if ($deliveryChannel->getHasOrderCounter()) {
                $counterId = $deliveryChannel->getChannelId();
                break;
            }
        }

        return $counterId;
    }

    /**
     * The lowest priority limit but most global - Limit by delivery channel.
     *
     * @return OrderLimitInterface|null
     */
    public function getChannelLimit(): ?OrderLimitInterface
    {
        foreach ($this->channelSetResult->getDeliveryChannel()->getItems() as $deliveryChannel) {
            if ($limitId = $deliveryChannel->getLimitId()) {
                return $this->getOrderLimit->execute($limitId);
            }
        }

        return null;
    }
}
