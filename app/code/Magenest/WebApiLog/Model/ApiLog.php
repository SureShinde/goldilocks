<?php

namespace Magenest\WebApiLog\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class ApiLog
 *
 * @package Magenest\WebApiLog\Model
 */
class ApiLog extends AbstractModel
{
    /**
     * @var string
     */
    const LOD_ID = 'id'; // We define the id field name

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Magenest\WebApiLog\Model\ResourceModel\ApiLog');
    }
}
