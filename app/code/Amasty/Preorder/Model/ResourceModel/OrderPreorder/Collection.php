<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\ResourceModel\OrderPreorder;

use Amasty\Preorder\Model\OrderPreorder as OrderPreorderModel;
use Amasty\Preorder\Model\ResourceModel\OrderPreorder as OrderPreorderResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(OrderPreorderModel::class, OrderPreorderResource::class);
    }
}
