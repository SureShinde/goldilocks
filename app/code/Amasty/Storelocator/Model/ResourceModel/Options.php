<?php

namespace Amasty\Storelocator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Options extends AbstractDb
{
    public const TABLE_NAME = 'amasty_amlocator_attribute_option';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'value_id');
    }
}
