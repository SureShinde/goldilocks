<?php

namespace Magenest\WebApiLog\Model\ResourceModel\ApiLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Magenest\WebApiLog\Model\ResourceModel\ApiLog
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            'Magenest\WebApiLog\Model\ApiLog',
            'Magenest\WebApiLog\Model\ResourceModel\ApiLog'
        );
    }
}
