<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval;

use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval as TimeIntervalResource;
use Magento\Framework\Exception\CouldNotDeleteException;

class Delete
{
    /**
     * @var TimeIntervalResource
     */
    private $timeIntervalResource;

    public function __construct(TimeIntervalResource $timeIntervalResource)
    {
        $this->timeIntervalResource = $timeIntervalResource;
    }

    /**
     * @param TimeIntervalDataModel $timeIntervalModel
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function execute(TimeIntervalDataModel $timeIntervalModel): bool
    {
        try {
            $this->timeIntervalResource->delete($timeIntervalModel);
        } catch (\Exception $e) {
            if ($timeIntervalModel->getIntervalId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove time interval with ID %1. Error: %2',
                        [$timeIntervalModel->getIntervalId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove time interval. Error: %1', $e->getMessage()));
        }

        return true;
    }
}
