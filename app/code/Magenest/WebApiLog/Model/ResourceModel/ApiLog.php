<?php

namespace Magenest\WebApiLog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ApiLog
 *
 * @package Magenest\WebApiLog\Model\ResourceModel
 */
class ApiLog extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        // Table Name and Primary Key column
        $this->_init('magenest_api_log', 'id');
    }
}
