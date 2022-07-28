<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation;

use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractTypifiedCollection;

/**
 * @method \Amasty\DeliveryDateManager\Model\Relations\DateScheduleChannelRelation\DateScheduleChannelRelationData[] getItems()
 */
class Collection extends AbstractTypifiedCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'relation_id';

    protected function _construct()
    {
        $this->_init(
            \Amasty\DeliveryDateManager\Model\Relations\DateScheduleChannelRelation\DateScheduleChannelRelationData::class,
            \Amasty\DeliveryDateManager\Model\ResourceModel\DateScheduleChannelRelation::class
        );
    }
}
