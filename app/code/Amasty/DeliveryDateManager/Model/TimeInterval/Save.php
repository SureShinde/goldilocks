<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval as TimeIntervalResource;
use Magento\Framework\Exception\CouldNotSaveException;

class Save
{
    /**
     * @var TimeIntervalResource
     */
    private $timeIntervalResource;

    public function __construct(
        TimeIntervalResource $timeIntervalResource
    ) {
        $this->timeIntervalResource = $timeIntervalResource;
    }

    /**
     * @param TimeIntervalDataModel $timeIntervalModel
     *
     * @return TimeIntervalInterface
     * @throws CouldNotSaveException
     */
    public function execute(TimeIntervalDataModel $timeIntervalModel) :TimeIntervalInterface
    {
        try {
            $this->timeIntervalResource->save($timeIntervalModel);
        } catch (\Exception $e) {
            if ($timeIntervalModel->getIntervalId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save Time Interval with ID %1. Error: %2',
                        [$timeIntervalModel->getIntervalId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new Time Interval. Error: %1', $e->getMessage()));
        }

        return $timeIntervalModel;
    }
}
