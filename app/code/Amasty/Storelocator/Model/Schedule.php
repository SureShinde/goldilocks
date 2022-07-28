<?php

namespace Amasty\Storelocator\Model;

/**
 * Class Schedule
 */
class Schedule extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Storelocator\Model\ResourceModel\Schedule::class);
        $this->setIdFieldName('id');
    }
}
