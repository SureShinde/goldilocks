<?php

namespace Amasty\DeliveryDateManager\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DeliveryDateQuote extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_deliverydate_deliverydate_quote';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'delivery_quote_id');
    }
}
