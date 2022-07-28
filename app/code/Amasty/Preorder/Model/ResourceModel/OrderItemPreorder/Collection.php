<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\ResourceModel\OrderItemPreorder;

use Amasty\Preorder\Model\OrderItemPreorder as OrderItemPreorderModel;
use Amasty\Preorder\Model\ResourceModel\OrderItemPreorder as OrderItemPreorderResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(OrderItemPreorderModel::class, OrderItemPreorderResource::class);
    }
}
