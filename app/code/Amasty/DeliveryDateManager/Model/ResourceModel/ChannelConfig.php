<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel;

class ChannelConfig extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const MAIN_TABLE = 'amasty_deliverydate_channel_configuration';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }
}
