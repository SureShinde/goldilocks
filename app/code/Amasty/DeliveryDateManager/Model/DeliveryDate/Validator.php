<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryDate;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Api\Data\DeliveryDateQuoteInterface;
use Amasty\DeliveryDateManager\Model\ChannelSetProcessor;
use Amasty\DeliveryDateManager\Model\ChannelSetResults;
use Amasty\DeliveryDateManager\Model\ConfigProvider;
use Amasty\DeliveryDateManager\Model\DeliveryQuote\IsBackOrderQuoteValidator;
use Amasty\DeliveryDateManager\Model\OrderLimit\Restricted\RestrictedDateProvider;
use Amasty\DeliveryDateManager\Model\TimeInterval\MinsToTimeConverter;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;

/**
 * Class Validator for validation delivery
 */
class Validator
{
    // 24 h. * 60 min. * 60 sec. = 86400 sec
    public const SEC_IN_DAY = 86400;

    /**
     * @var int
     */
    private $todayTimestamp;

    /**
     * @var \Magento\Framework\Phrase[]
     */
    private $errorMessages = [];

    /**
     * @var DateDataObject
     */
    private $currentDeliveryDate;

    /**
     * @var DateTime
     */
    private $dateLib;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ChannelSetProcessor
     */
    private $channelSetProcessor;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var RestrictedDateProvider
     */
    private $restrictedDateProvider;

    /**
     * @var IsBackOrderQuoteValidator
     */
    private $isQuoteBackorder;

    /**
     * @var MinsToTimeConverter
     */
    private $minsToTimeConverter;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    public function __construct(
        DateTime $dateLib,
        DateDataObject $dateDate,
        ConfigProvider $configProvider,
        ChannelSetProcessor $channelSetProcessor,
        ValidationResultFactory $validationResultFactory,
        RestrictedDateProvider $restrictedDateProvider,
        IsBackOrderQuoteValidator $isQuoteBackorder,
        MinsToTimeConverter $minsToTimeConverter,
        TimezoneInterface $timezoneInterface
    ) {
        $this->dateLib = $dateLib;
        $this->currentDeliveryDate = $dateDate;
        $this->configProvider = $configProvider;
        $this->channelSetProcessor = $channelSetProcessor;
        $this->validationResultFactory = $validationResultFactory;
        $this->restrictedDateProvider = $restrictedDateProvider;
        $this->isQuoteBackorder = $isQuoteBackorder;
        $this->minsToTimeConverter = $minsToTimeConverter;
        $this->timezoneInterface = $timezoneInterface;
    }

    /**
     * @param DeliveryDateQuoteInterface|DeliveryDateOrderInterface $deliveryObject
     * @param int $storeId
     * @return void
     * @throws InputException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateForRequired($deliveryObject, int $storeId): void
    {
        $date = $deliveryObject->getDate();
        $intervalId = $deliveryObject->getTimeIntervalId();
        $comment = $deliveryObject->getComment();

        if (!$date
            && $this->configProvider->isDateRequired($storeId)) {
            throw new InputException(__('Delivery Date is required.'));
        }

        if ($date
            && !$intervalId
            && $this->configProvider->isTimeEnabled($storeId)
            && $this->configProvider->isTimeRequired($storeId)
        ) {
            throw new InputException(__('Delivery Time is required.'));
        }

        if (!$comment
            && $this->configProvider->isCommentEnabled($storeId)
            && $this->configProvider->isCommentRequired($storeId)
        ) {
            throw new InputException(__('Delivery Comment is required.'));
        }
    }

    /**
     * Validate Delivery Date
     *
     * @param ChannelSetResults $channelSet
     * @param string|null $deliveryDate date in mysql format YYYY-mm-dd
     *
     * @return ValidationResult
     */
    public function validate(ChannelSetResults $channelSet, ?string $deliveryDate): ValidationResult
    {
        $this->errorMessages = [];
        if ($deliveryDate) {
            $this->setCurrentDeliveryDate($deliveryDate, $channelSet);
            switch (true) {
                case $this->restrictDateLessToday():
                case $this->restrictToday():
                case $this->restrictByQuota():
                case $this->minDays():
                case $this->maxDays():
                case $this->restrictBySchedule():
                    break;
            }
        }

        return $this->validationResultFactory->create(['errors' => $this->errorMessages]);
    }

    /**
     * @return bool
     */
    private function isSameDayDeliveryAllowed(): bool
    {
        $config = $this->currentDeliveryDate->getChannelSet()->getChannelConfig();
        if ($config->getMin() != 0
            || !$config->getIsSameDayAvailable()
        ) {
            return false;
        }

        if ($cutoffTime = $config->getSameDayCutoff()) {
            $currentTime = (int)$this->dateLib->date('H') * 60 + (int)$this->dateLib->date('i');
            if ($cutoffTime < $currentTime) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ChannelSetResults $channelSet
     * @param string $deliveryDate
     * @param int $timeFrom
     * @param int $timeTo
     *
     * @return ValidationResult
     */
    public function validateTimeInterval(
        ChannelSetResults $channelSet,
        string $deliveryDate,
        int $timeFrom,
        int $timeTo
    ): ValidationResult {
        $this->errorMessages = [];
        $this->setCurrentDeliveryDate($deliveryDate, $channelSet);

        $todayTimestamp = $this->dateLib->timestamp();
        $formattedTime = $this->minsToTimeConverter->toSystemTime($timeFrom);
        $gmtDateTime = new \DateTime(
            $deliveryDate . ' ' . $formattedTime . ':59',
            new \DateTimeZone($this->timezoneInterface->getConfigTimezone())
        );
        $gmtDateTime->setTimezone(new \DateTimeZone($this->timezoneInterface->getDefaultTimezone()));
        $timestampDelivery = $gmtDateTime->getTimestamp();
        if (($timestampDelivery <= $todayTimestamp)) {
            $this->errorMessages[] = __('Time interval is not available.');
        }

        if ($deliveryDate === $this->dateLib->date('Y-m-d')) {
            $configChannel = $channelSet->getChannelConfig();

            if (!$this->isSameDayDeliveryAllowed()) {
                $this->errorMessages[] = __('Time interval is not available.');
            }
            $timeValidationTo = $configChannel->getOrderTime();

            if ($configChannel->getBackorderTime() !== null && $this->isQuoteBackorder->execute()) {
                $timeValidationTo = $configChannel->getBackorderTime();
            }
            if ($timeValidationTo !== null) {
                $timeValidationTo += date('H') * 60 + date('i');
                if ($timeFrom < $timeValidationTo) {
                    $this->errorMessages[] = __('Time interval is not available.');
                }
            }
        }

        $interval = $this->channelSetProcessor->getTimeIntervalByRange($deliveryDate, $timeFrom, $timeTo);

        if (!$interval) {
            $this->errorMessages[] = __('Time interval is not available.');
            return $this->validationResultFactory->create(['errors' => $this->errorMessages]);
        }

        $isRestricted = $this->restrictedDateProvider->isTimeRangeRestricted(
            $this->currentDeliveryDate->getChannelSet(),
            $this->currentDeliveryDate->getDate(),
            $timeFrom,
            $timeTo
        );

        if ($isRestricted) {
            $this->errorMessages[] = __('Delivery Date is not available due Order Limit of the day.');
        }

        return $this->validationResultFactory->create(['errors' => $this->errorMessages]);
    }

    /**
     * @param string $deliveryDate
     * @param ChannelSetResults $channelSet
     */
    private function setCurrentDeliveryDate(string $deliveryDate, ChannelSetResults $channelSet): void
    {
        $this->todayTimestamp = $this->dateLib->timestamp(date('j F Y'));
        $this->channelSetProcessor->setChannelSetResult($channelSet);

        $this->currentDeliveryDate->setChannelSet($channelSet);
        $this->currentDeliveryDate->setDate($deliveryDate);
        $timestamp = $this->dateLib->timestamp($deliveryDate);
        $this->currentDeliveryDate->setObject(new \Zend_Date($timestamp, \Zend_Date::TIMESTAMP));
        $this->currentDeliveryDate->setTimestamp($timestamp);
        $this->currentDeliveryDate->setYear($this->dateLib->date('Y', $timestamp));
        $this->currentDeliveryDate->setMonth($this->dateLib->date('n', $timestamp));
        $this->currentDeliveryDate->setDay($this->dateLib->date('d', $timestamp));
    }

    /**
     * @return bool
     */
    private function restrictDateLessToday(): bool
    {
        if ($this->dateLib->date('Ymd', $this->currentDeliveryDate->getTimestamp()) < $this->dateLib->date('Ymd')) {
            $this->errorMessages[] = __('Delivery Date cannot be in the past.');

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function restrictToday(): bool
    {
        if ($this->dateLib->date('Ymd', $this->currentDeliveryDate->getTimestamp()) === $this->dateLib->date('Ymd')
            && !$this->isSameDayDeliveryAllowed()
        ) {
            $this->errorMessages[] = __('Current date is not available for delivery.');

            return true;
        }

        return false;
    }

    /**
     * Is need to restrict day by Quota
     * Is limit for shipping quota of day is not exceeded
     *
     * @return bool
     */
    private function restrictByQuota(): bool
    {
        $isRestricted = $this->restrictedDateProvider->isDateRestricted(
            $this->currentDeliveryDate->getChannelSet(),
            $this->currentDeliveryDate->getDate()
        );

        if ($isRestricted) {
            $this->errorMessages[] = __('Delivery Date is not available due Order Limit of the day.');

            return true;
        }

        return false;
    }

    /**
     * Validate Minimal Delivery Interval
     *
     * @return bool
     */
    private function minDays()
    {
        $config = $this->currentDeliveryDate->getChannelSet()->getChannelConfig();
        $minDay = $config->getMin();

        if ($minDay === null) {
            return false;
        }

        $minDayTimestamp = $this->todayTimestamp + $minDay * self::SEC_IN_DAY;

        if ($this->configProvider->isOnlyWorkdays()) {
            $minDayTimestamp = $this->fixMinMax($minDayTimestamp);
        }

        return $this->currentDeliveryDate->getTimestamp() < $minDayTimestamp;
    }

    /**
     * Validate Maximal Delivery Interval
     *
     * @return bool
     */
    private function maxDays()
    {
        $config = $this->currentDeliveryDate->getChannelSet()->getChannelConfig();
        $maxDay = $config->getMax();
        if ($maxDay === null || $maxDay <= 0) {
            return false;
        }

        $maxDayTimestamp = $this->todayTimestamp + $maxDay * self::SEC_IN_DAY;

        if ($this->configProvider->isOnlyWorkdays()) {
            $maxDayTimestamp = $this->fixMinMax($maxDayTimestamp);
        }

        return $this->currentDeliveryDate->getTimestamp() > $maxDayTimestamp;
    }

    /**
     * @return bool
     */
    private function restrictBySchedule(): bool
    {
        $dateSchedule = $this->channelSetProcessor->getDateScheduleByDate($this->currentDeliveryDate->getDate());
        if (!$dateSchedule) {
            $this->errorMessages[] = __('Date is not available for delivery.');

            return true;
        }

        if (!$dateSchedule->getIsAvailable()) {
            $this->errorMessages[] = __('Date is not available for delivery.');

            return true;
        }

        return false;
    }

    /**
     * @param int $thresholdDayTimestamp
     * @return int
     */
    private function fixMinMax(int $thresholdDayTimestamp): int
    {
        $datesBeforeDelivery = $this->getDatePeriod(
            $this->dateLib->date('Y-m-d', $this->isSameDayDeliveryAllowed() ? null : '+1 days'),
            $this->currentDeliveryDate->getDate()
        );
        // go through every day before the delivery day
        foreach ($datesBeforeDelivery as $dateBeforeDelivery) {
            $dateSchedule = $this->channelSetProcessor->getDateScheduleByDate($dateBeforeDelivery->format('Y-m-d'));
            if (!$dateSchedule || $dateSchedule->getIsAvailable()) {
                continue;
            }

            if ($dateBeforeDelivery->getTimestamp() <= $thresholdDayTimestamp) {
                $thresholdDayTimestamp += self::SEC_IN_DAY;
            }
        }

        return $thresholdDayTimestamp;
    }

    /**
     * @param string $dateFrom
     * @param string $dateTo
     * @return \DatePeriod
     */
    private function getDatePeriod(string $dateFrom, string $dateTo): \DatePeriod
    {
        return new \DatePeriod(new \DateTime($dateFrom), new \DateInterval('P1D'), new \DateTime($dateTo));
    }
}
