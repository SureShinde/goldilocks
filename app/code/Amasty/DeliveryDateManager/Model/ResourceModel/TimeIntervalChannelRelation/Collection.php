<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalChannelRelation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method \Amasty\DeliveryDateManager\Model\Relations\TimeIntervalChannelRelation\TimeIntervalChannelData[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'relation_id';

    protected function _construct()
    {
        $this->_init(
            \Amasty\DeliveryDateManager\Model\Relations\TimeIntervalChannelRelation\TimeIntervalChannelData::class,
            \Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalChannelRelation::class
        );
    }
}
