<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\OrderLimit\Restricted;

use Amasty\DeliveryDateManager\Api\Data\RestrictedDateInterface;
use Amasty\DeliveryDateManager\Api\Data\RestrictedDateInterfaceFactory;
use Amasty\DeliveryDateManager\Api\Data\RestrictedTimeIntervalInterface;
use Amasty\DeliveryDateManager\Api\Data\RestrictedTimeIntervalInterfaceFactory;
use Amasty\DeliveryDateManager\Model\ChannelSetProcessor;
use Amasty\DeliveryDateManager\Model\ChannelSetResults;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateOrder;

class RestrictedDateProvider
{
    /**
     * Local Registry for reduce the amount of requests
     *
     * @var array
     */
    private $dayCountersRegistry = [];

    /**
     * Local Registry for reduce the amount of requests
     *
     * @var array
     */
    private $channelSetRestrictionsRegistry = [];

    /**
     * @var DeliveryDateOrder
     */
    private $dateOrderResource;

    /**
     * @var ChannelSetProcessor
     */
    private $channelSetProcessor;

    /**
     * @var RestrictedDateInterfaceFactory
     */
    private $restrictedDateFactory;

    /**
     * @var RestrictedTimeIntervalInterfaceFactory
     */
    private $restrictedTimeFactory;

    public function __construct(
        DeliveryDateOrder $dateOrderResource,
        ChannelSetProcessor $channelSetProcessor,
        RestrictedDateInterfaceFactory $restrictedDateFactory,
        RestrictedTimeIntervalInterfaceFactory $restrictedTimeFactory
    ) {
        $this->dateOrderResource = $dateOrderResource;
        $this->channelSetProcessor = $channelSetProcessor;
        $this->restrictedDateFactory = $restrictedDateFactory;
        $this->restrictedTimeFactory = $restrictedTimeFactory;
    }

    /**
     * @param ChannelSetResults $channelSetResults
     *
     * @return RestrictedDateInterface[]
     */
    public function getRestrictedByChannelSet(ChannelSetResults $channelSetResults): array
    {
        $result = [];
        /** @var true|array $intervals */
        foreach ($this->getRestrictedArrayByChannelSet($channelSetResults) as $day => $intervals) {
            $timeIntervals = null;
            if (is_array($intervals)) {
                $timeIntervals = [];
                foreach ($intervals as $interval) {
                    $timeIntervals[] = $this->restrictedTimeFactory->create(
                        [
                            'data' => [
                                RestrictedTimeIntervalInterface::KEY_FROM => $interval['from'],
                                RestrictedTimeIntervalInterface::KEY_TO => $interval['to']
                            ]
                        ]
                    );
                }
            }

            $result[] = $this->restrictedDateFactory->create(
                [
                    'data' => [
                        RestrictedDateInterface::DAY => $day,
                        RestrictedDateInterface::INTERVALS => $timeIntervals
                    ]
                ]
            );
        }

        return $result;
    }

    /**
     * Is order limit exceeded for date
     *
     * @param ChannelSetResults $channelSetResults
     * @param string $date
     *
     * @return bool
     */
    public function isDateRestricted(ChannelSetResults $channelSetResults, string $date)
    {
        $this->channelSetProcessor->setChannelSetResult($channelSetResults);
        $limit = $this->channelSetProcessor->getLimitByDate($date);

        if ($limit && ($limit->getDayLimit() === 0)) {
            return true;
        }
        if (!$limit || !$limit->getDayLimit()) {
            return false;
        }
        $counterId = $this->channelSetProcessor->getCounterId();
        $dayCounters = $this->dateOrderResource->loadCountersForDate($counterId, $date);

        if (empty($dayCounters)) {
            return false;
        }

        $dayCounter = 0;
        foreach ($dayCounters as $interval) {
            $dayCounter += (int)$interval['time_counter'];
        }

        return $dayCounter >= $limit->getDayLimit();
    }

    /**
     * Is order limit exceeded for time range
     *
     * @param ChannelSetResults $channelSetResults
     * @param string $date
     * @param int $timeFrom
     * @param int $timeTo
     *
     * @return bool
     */
    public function isTimeRangeRestricted(
        ChannelSetResults $channelSetResults,
        string $date,
        int $timeFrom,
        int $timeTo
    ): bool {
        $this->channelSetProcessor->setChannelSetResult($channelSetResults);
        $limit = $this->channelSetProcessor->getLimitByTimeRange(
            $date,
            $timeFrom,
            $timeTo
        );

        if ($limit && ($limit->getIntervalLimit() === 0)) {
            return true;
        }

        if (!$limit || !$limit->getIntervalLimit()) {
            return false;
        }
        $counterId = $this->channelSetProcessor->getCounterId();
        $dayCounters = $this->dateOrderResource->loadCountersForDate($counterId, $date);

        if (empty($dayCounters)) {
            return false;
        }

        foreach ($dayCounters as $interval) {
            if ($interval['time_from'] == $timeFrom
                && $interval['time_to'] == $timeTo
                && $interval['time_counter'] >= $limit->getIntervalLimit()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return restricted by order limit dates and time intervals.
     * Return array where key is a day in MySQL format yyyy-mm-dd.
     *     Value can be bool true it is mean that whole day is exceed the limit.
     *     Or an arrays with exceeded time range.
     *
     * @param ChannelSetResults $channelSetResults
     *
     * @return array array('1970-01-01' => true|array(array('from' => 100, 'to' => 110), ...))
     */
    public function getRestrictedArrayByChannelSet(ChannelSetResults $channelSetResults): array
    {
        if (!$channelSetResults->getLimitSearchResult()->getTotalCount()) {
            return [];
        }

        $key = $this->getCacheKeyByChannelSet($channelSetResults);

        if (isset($this->channelSetRestrictionsRegistry[$key])) {
            return $this->channelSetRestrictionsRegistry[$key];
        }

        $this->channelSetProcessor->setChannelSetResult($channelSetResults);
        $counterId = $this->channelSetProcessor->getCounterId();

        $restrictedDates = $this->getRestrictedDays($counterId, $channelSetResults);

        if ($channelSetResults->getTimeIntervalItems()->getTotalCount()) {
            $timeCounters = $this->dateOrderResource->getTimeCounter($counterId, array_keys($restrictedDates));
            foreach ($timeCounters as $timeCounter) {
                $day = $timeCounter['date'];

                $limit = $this->channelSetProcessor->getLimitByTimeRange(
                    $day,
                    (int)$timeCounter['time_from'],
                    (int)$timeCounter['time_to']
                );

                if ($limit && $timeCounter['time_counter'] >= $limit->getIntervalLimit()) {
                    $restrictedDates[$day][] = [
                        'from' => (int)$timeCounter['time_from'],
                        'to' => (int)$timeCounter['time_to']
                    ];
                }
            }
        }

        $this->channelSetRestrictionsRegistry[$key] = $restrictedDates;

        return $restrictedDates;
    }

    /**
     * @param int $counterId
     * @param ChannelSetResults $channelSetResults
     *
     * @return array
     */
    private function getRestrictedDays(int $counterId, ChannelSetResults $channelSetResults): array
    {
        $lowestDayLimit = $this->getLowestDayLimit($channelSetResults);
        if ($lowestDayLimit === null) {
            return [];
        }
        $dayCounters = $this->getDayCounters($counterId, $lowestDayLimit);
        $restrictedDates = [];

        foreach ($dayCounters as $dateCounter) {
            $day = $dateCounter['date'];
            $limit = $this->channelSetProcessor->getLimitByDate($day);
            if ($limit !== null && $dateCounter['day_counter'] >= $limit->getDayLimit()) {
                $restrictedDates[$day] = true;
            }
        }

        return $restrictedDates;
    }

    private function getDayCounters(int $counterId, int $lowestDayLimit)
    {
        $key = $counterId . '|' . $lowestDayLimit;
        if (!isset($this->dayCountersRegistry[$key])) {
            $this->dayCountersRegistry[$key] = $this->dateOrderResource->getDayCounters($counterId, $lowestDayLimit);
        }

        return $this->dayCountersRegistry[$key];
    }

    /**
     * @param ChannelSetResults $channelSetResults
     *
     * @return int|null
     */
    private function getLowestDayLimit(ChannelSetResults $channelSetResults): ?int
    {
        $lowestDayLimit = null;
        foreach ($channelSetResults->getLimitSearchResult()->getItems() as $orderLimit) {
            $dayLimit = $orderLimit->getDayLimit();
            if (!$dayLimit && ($dayLimit !== 0)) {
                continue;
            }
            if (!isset($lowestDayLimit)) {
                $lowestDayLimit = $orderLimit->getDayLimit();
                continue;
            }
            if ($dayLimit < $lowestDayLimit) {
                $lowestDayLimit = $orderLimit->getDayLimit();
            }
        }

        return $lowestDayLimit;
    }

    private function getCacheKeyByChannelSet(ChannelSetResults $channelSetResults): string
    {
        return implode('|', $channelSetResults->getDeliveryChannel()->getIds());
    }
}
