<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\DateScheduleChannelRelation;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleChannelRelationInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation;

class Delete
{
    /**
     * @var DateScheduleChannelRelation
     */
    private $resourceModel;

    public function __construct(DateScheduleChannelRelation $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param DateScheduleChannelRelationInterface $relation
     */
    public function deleteByRelation(DateScheduleChannelRelationInterface $relation): void
    {
        $this->resourceModel->delete($relation);
    }
}
