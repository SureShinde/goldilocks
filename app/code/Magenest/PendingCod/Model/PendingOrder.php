<?php

namespace Magenest\PendingCod\Model;

class PendingOrder extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Magenest\PendingCod\Model\ResourceModel\PendingOrder');
    }
}
