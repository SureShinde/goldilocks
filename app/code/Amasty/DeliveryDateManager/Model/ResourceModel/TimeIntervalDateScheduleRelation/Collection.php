<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalDateScheduleRelation;

use Amasty\DeliveryDateManager\Model\Relations\TimeIntervalDateScheduleRelation\TimeIntervalDateScheduleData;
use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractTypifiedCollection;

/**
 * @method TimeIntervalDateScheduleData[] getItems()
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
            TimeIntervalDateScheduleData::class,
            \Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalDateScheduleRelation::class
        );
    }
}
