<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\TimeInterval\Set;

use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set as TimeIntervalSetResource;
use Magento\Framework\Exception\CouldNotSaveException;

class Save
{
    /**
     * @var TimeIntervalSetResource
     */
    private $timeIntervalSetResource;

    /**
     * @var ProcessRelationsSave
     */
    private $processRelationsSave;

    /**
     * @var ProcessRelatedTimesDelete
     */
    private $processRelatedTimesDeleter;

    public function __construct(
        TimeIntervalSetResource $timeIntervalSetResource,
        ProcessRelationsSave $processRelationsSave,
        ProcessRelatedTimesDelete $processRelatedTimesDeleter
    ) {
        $this->timeIntervalSetResource = $timeIntervalSetResource;
        $this->processRelationsSave = $processRelationsSave;
        $this->processRelatedTimesDeleter = $processRelatedTimesDeleter;
    }

    /**
     * @param DataModel $timeIntervalSetModel
     *
     * @return DataModel
     * @throws CouldNotSaveException
     */
    public function execute(DataModel $timeIntervalSetModel) :DataModel
    {
        try {
            $this->timeIntervalSetResource->save($timeIntervalSetModel);
            $this->processRelatedTimesDeleter->processDelete(
                $timeIntervalSetModel->getTimeIds(),
                (array)$timeIntervalSetModel->getOrigData(DataModel::KEY_TIME_IDS)
            );
            $this->processRelationsSave->processSave($timeIntervalSetModel);
        } catch (\Exception $e) {
            if ($timeIntervalSetModel->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save Time Interval Set with ID %1. Error: %2',
                        [$timeIntervalSetModel->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new Time Interval Set. Error: %1', $e->getMessage()));
        }

        return $timeIntervalSetModel;
    }
}
