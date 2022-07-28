<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateOrder;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractTypifiedCollection;

/**
 * @method \Amasty\DeliveryDateManager\Model\DeliveryOrder\DeliveryOrderData getItems()
 */
class Collection extends AbstractTypifiedCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = DeliveryDateOrderInterface::DELIVERYDATE_ID;

    protected function _construct()
    {
        $this->_init(
            \Amasty\DeliveryDateManager\Model\DeliveryOrder\DeliveryOrderData::class,
            \Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateOrder::class
        );
    }
}
