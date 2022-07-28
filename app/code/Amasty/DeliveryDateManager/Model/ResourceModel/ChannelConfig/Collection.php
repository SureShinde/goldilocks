<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig;

use Amasty\DeliveryDateManager\Model\ChannelConfig\ConfigData;
use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractTypifiedCollection;
use Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig;

/**
 * @method ConfigData[] getItems()
 */
class Collection extends AbstractTypifiedCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(ConfigData::class, ChannelConfig::class);
    }
}
