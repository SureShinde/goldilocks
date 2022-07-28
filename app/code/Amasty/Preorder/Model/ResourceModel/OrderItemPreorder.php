<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\ResourceModel;

use Amasty\Preorder\Api\Data\OrderItemInformationInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderItemPreorder extends AbstractDb
{
    protected function _construct()
    {
        $this->_setResource('sales');
        $this->_init(OrderItemInformationInterface::MAIN_TABLE, OrderItemInformationInterface::ID);
    }
}
