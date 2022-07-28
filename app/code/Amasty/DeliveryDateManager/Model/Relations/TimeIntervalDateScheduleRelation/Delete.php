<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\TimeIntervalDateScheduleRelation;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalDateScheduleRelationInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalDateScheduleRelation;

class Delete
{
    /**
     * @var TimeIntervalDateScheduleRelation
     */
    private $resourceModel;

    public function __construct(TimeIntervalDateScheduleRelation $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    public function deleteByRelation(TimeIntervalDateScheduleRelationInterface $relation): void
    {
        $this->resourceModel->delete($relation);
    }
}
