<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderLimit extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_deliverydate_order_limit';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'limit_id');
    }
}
