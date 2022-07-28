<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryOrder;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval as TimeIntervalInterfaceResource;
use Amasty\DeliveryDateManager\Model\TimeInterval\Get as TimeIntervalGetter;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Order Delivery information output format
 */
class OutputFormatter
{
    /**
     * @var TimeIntervalInterfaceResource
     */
    private $timeResource;

    /**
     * @var TimeIntervalGetter
     */
    private $timeIntervalGetter;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        TimeIntervalInterfaceResource $timeResource,
        TimeIntervalGetter $timeIntervalGetter,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezone
    ) {
        $this->timeResource = $timeResource;
        $this->timeIntervalGetter = $timeIntervalGetter;
        $this->storeManager = $storeManager;
        $this->timezone = $timezone;
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryOrder
     *
     * @return string
     */
    public function getFormattedDateFromDeliveryOrder(DeliveryDateOrderInterface $deliveryOrder): string
    {
        if (!$deliveryOrder->getDate()) {
            return '';
        }

        return $this->formatDate($deliveryOrder->getDate());
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryOrder
     *
     * @return string
     */
    public function getTimeLabelFromDeliveryOrder(DeliveryDateOrderInterface $deliveryOrder): string
    {
        if ($deliveryOrder->getTimeFrom() === null || $deliveryOrder->getTimeTo() === null) {
            return '';
        }

        $intervalId = $deliveryOrder->getTimeIntervalId();
        $formattedTime = $this->localRangeTimeFormat($deliveryOrder->getTimeFrom(), $deliveryOrder->getTimeTo());

        if ($intervalId) {
            $label = $this->timeResource->getLabel($intervalId, (int)$this->storeManager->getStore()->getId());

            if ($label) {
                return $formattedTime . ' ' . $label;
            }

            try {
                $timeInterval = $this->timeIntervalGetter->execute($intervalId);
                if ($timeInterval->getLabel()) {
                    return $formattedTime . ' ' . $timeInterval->getLabel();
                }
            } catch (\Exception $e) {
               // just do nothing in that case.
            }
        }

        return $formattedTime;
    }

    /**
     * @param DeliveryDateOrderInterface $deliveryDateOrder
     * @return DeliveryDateOrderInterface
     */
    public function formatOutputTimes(DeliveryDateOrderInterface $deliveryDateOrder): DeliveryDateOrderInterface
    {
        if ($deliveryDateOrder->getTimeFrom() !== null) {
            $deliveryDateOrder->setData(
                DeliveryDateOrderInterface::TIME_FROM . '_formatted',
                $this->localTimeFormat((int)$deliveryDateOrder->getTimeFrom())
            );
        }
        if ($deliveryDateOrder->getTimeTo() !== null) {
            $deliveryDateOrder->setData(
                DeliveryDateOrderInterface::TIME_TO . '_formatted',
                $this->localTimeFormat((int)$deliveryDateOrder->getTimeTo())
            );
        }

        return $deliveryDateOrder;
    }

    /**
     * Get time string range in local time format.
     *
     * @param int $timeFrom minutes from start of the day
     * @param int $timeTo minutes from start of the day
     *
     * @return string
     */
    public function localRangeTimeFormat(int $timeFrom, int $timeTo): string
    {
        return $this->localTimeFormat($timeFrom) . ' - ' . $this->localTimeFormat($timeTo);
    }

    /**
     * @param int $time minutes from start of the day
     * @return string
     */
    public function localTimeFormat(int $time)
    {
        $time = mktime(0, $time);

        return $this->formatTimeToLocale($time);
    }

    /**
     * @param string $date
     * @return string
     */
    public function formatDate(string $date): string
    {
        return $this->timezone->formatDateTime(
            $date,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            null,
            // we do not need to change date according to timezone, because date is already in store timezone
            'UTC'
        );
    }

    /**
     * @param int $timestamp
     * @return string
     */
    private function formatTimeToLocale(int $timestamp): string
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($timestamp);

        return $this->timezone->formatDateTime(
            $dateTime,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            null,
            'UTC'
        );
    }

    /**
     * Prepare comment text to html output.
     * Replace New Line with html tag <br />
     *
     * @param DeliveryDateOrderInterface $deliveryOrder
     *
     * @return string
     */
    public function getComment(DeliveryDateOrderInterface $deliveryOrder): string
    {
        if ($deliveryOrder->getComment()) {
            return nl2br($deliveryOrder->getComment());
        }

        return '';
    }
}
