<?php

namespace Magenest\PendingCod\Model\ResourceModel\PendingOrder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'pending_cod_order_id';

    protected function _construct()
    {
        $this->_init('Magenest\PendingCod\Model\PendingOrder', 'Magenest\PendingCod\Model\ResourceModel\PendingOrder');
    }
}
