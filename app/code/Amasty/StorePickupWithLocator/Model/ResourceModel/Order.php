<?php

namespace Amasty\StorePickupWithLocator\Model\ResourceModel;

use Amasty\StorePickupWithLocator\Api\Data\OrderInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Order extends AbstractDb
{
    /**
     * Table Date Time
     */
    public const TABLE = 'amasty_storepickup_order';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(self::TABLE, OrderInterface::ID);
    }
}
