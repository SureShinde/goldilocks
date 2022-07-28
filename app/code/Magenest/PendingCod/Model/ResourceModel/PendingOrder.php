<?php

namespace Magenest\PendingCod\Model\ResourceModel;

class PendingOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_isPkAutoIncrement = false;

    protected function _construct()
    {
        $this->_init('magenest_pending_cod_order', 'pending_cod_order_id');
    }
}
