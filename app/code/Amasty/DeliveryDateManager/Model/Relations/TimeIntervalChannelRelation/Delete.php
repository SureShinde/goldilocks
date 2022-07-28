<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Relations\TimeIntervalChannelRelation;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalChannelRelationInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalChannelRelation;

class Delete
{
    /**
     * @var TimeIntervalChannelData
     */
    private $resourceModel;

    public function __construct(TimeIntervalChannelRelation $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    public function deleteByRelation(TimeIntervalChannelRelationInterface $relation): void
    {
        $this->resourceModel->delete($relation);
    }
}
