<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DateSchedule;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule as DateScheduleResource;
use Magento\Framework\Exception\CouldNotSaveException;

class Save
{
    /**
     * @var DateScheduleResource
     */
    private $dateScheduleResource;

    public function __construct(
        DateScheduleResource $dateScheduleResource
    ) {
        $this->dateScheduleResource = $dateScheduleResource;
    }

    /**
     * @param DateScheduleData $dateScheduleModel
     *
     * @return DateScheduleInterface
     * @throws CouldNotSaveException
     */
    public function execute(DateScheduleData $dateScheduleModel) :DateScheduleInterface
    {
        try {
            $this->dateScheduleResource->save($dateScheduleModel);
        } catch (\Exception $e) {
            if ($dateScheduleModel->getScheduleId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save schedule with ID %1. Error: %2',
                        [$dateScheduleModel->getScheduleId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new schedule. Error: %1', $e->getMessage()));
        }

        return $dateScheduleModel;
    }
}
