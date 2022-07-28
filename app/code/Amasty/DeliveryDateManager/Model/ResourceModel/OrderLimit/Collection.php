<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit;

use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractTypifiedCollection;

/**
 * @method \Amasty\DeliveryDateManager\Model\OrderLimit\LimitDataModel[] getItems()
 * @method \Amasty\DeliveryDateManager\Model\OrderLimit\LimitDataModel[] getItemById()
 */
class Collection extends AbstractTypifiedCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'limit_id';

    protected function _construct()
    {
        $this->_init(
            \Amasty\DeliveryDateManager\Model\OrderLimit\LimitDataModel::class,
            \Amasty\DeliveryDateManager\Model\ResourceModel\OrderLimit::class
        );
    }
}
