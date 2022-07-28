<?php

namespace Magenest\AbandonedCart\Model\ResourceModel\ABTestCampaign;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_idFieldName = 'id';

    protected $isPrepareSelect = false;

    public function _construct()
    {
        $this->_init('Magenest\AbandonedCart\Model\ABTestCampaign', 'Magenest\AbandonedCart\Model\ResourceModel\ABTestCampaign');
    }
}
