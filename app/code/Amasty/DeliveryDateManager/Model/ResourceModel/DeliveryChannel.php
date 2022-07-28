<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel;

use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractDb\CompositeHandler;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class DeliveryChannel extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_deliverydate_delivery_channel';
    public const SCOPE_STORE_TABLE = 'amasty_deliverydate_delivery_channel_store';
    public const SCOPE_SHIPPING_METHOD_TABLE = 'amasty_deliverydate_delivery_channel_shipping_method';
    public const SCOPE_CUSTOMER_GROUP_TABLE = 'amasty_deliverydate_delivery_channel_group';

    public const SKIP_HANDLERS = 'skip_handlers';

    /**
     * @var CompositeHandler
     */
    private $dataHandler;

    public function __construct(
        Context $context,
        CompositeHandler $dataHandler,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->dataHandler = $dataHandler;
    }

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'channel_id');
    }

    /**
     * @return array
     */
    public function getAllShippingMethods(): array
    {
        $select = $this->getConnection()->select()
            ->from(
                $this->getTable(self::SCOPE_SHIPPING_METHOD_TABLE),
                ['shipping_method']
            )->distinct(true);

        return (array)$this->getConnection()->fetchCol($select);
    }

    protected function _afterSave(AbstractModel $object): self
    {
        if (!$object->getData(self::SKIP_HANDLERS)) {
            $this->dataHandler->afterSave($object);
        }

        return parent::_afterSave($object);
    }

    protected function _afterLoad(AbstractModel $object): self
    {
        $this->dataHandler->afterLoad($object);

        return parent::_afterLoad($object);
    }
}
