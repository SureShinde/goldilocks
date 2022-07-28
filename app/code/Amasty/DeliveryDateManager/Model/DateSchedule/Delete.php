<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DateSchedule;

use Amasty\DeliveryDateManager\Model\ResourceModel\DateSchedule as DateScheduleResource;
use Magento\Framework\Exception\CouldNotDeleteException;

class Delete
{
    /**
     * @var DateScheduleResource
     */
    private $resourceModel;

    public function __construct(DateScheduleResource $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param DateScheduleData $scheduleDataModel
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function execute(DateScheduleData $scheduleDataModel): bool
    {
        try {
            $this->resourceModel->delete($scheduleDataModel);
        } catch (\Exception $e) {
            if ($scheduleDataModel->getScheduleId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove date schedule with ID %1. Error: %2',
                        [$scheduleDataModel->getScheduleId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove date schedule. Error: %1', $e->getMessage()));
        }

        return true;
    }
}
