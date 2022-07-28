<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\IntervalSet\Save;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface;
use Amasty\DeliveryDateManager\Model\TimeInterval\Get;
use Amasty\DeliveryDateManager\Model\TimeInterval\Save as SaveTimeInterval;
use Amasty\DeliveryDateManager\Model\TimeInterval\TimeIntervalDataModelFactory;
use Amasty\DeliveryDateManager\Model\TimeInterval\TimeToMinsConverter;

class TimeIntervalResolver
{
    /**
     * @var Get
     */
    private $timeIntervalGetter;

    /**
     * @var SaveTimeInterval
     */
    private $timeIntervalSaver;

    /**
     * @var TimeIntervalDataModelFactory
     */
    private $intervalDataModelFactory;

    /**
     * @var OrderLimitResolver
     */
    private $saveOrderLimitResolver;

    /**
     * @var TimeToMinsConverter
     */
    private $timeToMinsConverter;

    public function __construct(
        Get $timeIntervalGetter,
        SaveTimeInterval $timeIntervalSaver,
        TimeIntervalDataModelFactory $intervalDataModelFactory,
        OrderLimitResolver $saveOrderLimitResolver,
        TimeToMinsConverter $timeToMinsConverter
    ) {
        $this->timeIntervalGetter = $timeIntervalGetter;
        $this->timeIntervalSaver = $timeIntervalSaver;
        $this->intervalDataModelFactory = $intervalDataModelFactory;
        $this->saveOrderLimitResolver = $saveOrderLimitResolver;
        $this->timeToMinsConverter = $timeToMinsConverter;
    }

    /**
     * @param array $times
     * @return int[]
     */
    public function execute(array $times): array
    {
        $timeIds = [];
        foreach ($times as $timeData) {
            $limitId = $this->saveOrderLimitResolver->execute($timeData);
            $label = $timeData[TimeIntervalInterface::LABEL] ?? '';

            $timeInterval = $this->getTimeIntervalDataModel($timeData);
            $timeInterval->setLimitId($limitId);
            $timeInterval->setFrom($this->timeToMinsConverter->execute($timeData[TimeIntervalInterface::FROM]));
            $timeInterval->setTo($this->timeToMinsConverter->execute($timeData[TimeIntervalInterface::TO]));
            $timeInterval->setLabel((string)$label);
            $timeInterval->setPosition((int)($timeData[TimeIntervalInterface::POSITION]));
            $this->timeIntervalSaver->execute($timeInterval);
            $timeIds[] = $timeInterval->getIntervalId();
        }

        return $timeIds;
    }

    /**
     * @param array $timeData
     * @return TimeIntervalInterface
     */
    private function getTimeIntervalDataModel(array $timeData): TimeIntervalInterface
    {
        if (empty($timeData[TimeIntervalInterface::INTERVAL_ID])) {
            return $this->intervalDataModelFactory->create();
        }

        return $this->timeIntervalGetter->execute((int)$timeData[TimeIntervalInterface::INTERVAL_ID]);
    }
}
