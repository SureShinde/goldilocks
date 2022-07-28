<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Model\DeliveryChannel\DeliveryChannelData;
use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractTypifiedCollection;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel;

/**
 * @method \Amasty\DeliveryDateManager\Model\DeliveryChannel\DeliveryChannelData[] getItems()
 */
class Collection extends AbstractTypifiedCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = DeliveryChannelInterface::CHANNEL_ID;

    protected function _construct()
    {
        $this->_init(
            DeliveryChannelData::class,
            DeliveryChannel::class
        );
    }
}
