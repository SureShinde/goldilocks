<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\ResourceModel;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderPreorder extends AbstractDb
{
    protected function _construct()
    {
        $this->_setResource('sales');
        $this->_init(OrderInformationInterface::MAIN_TABLE, OrderInformationInterface::ID);
    }
}
