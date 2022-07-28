<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel;

use Amasty\DeliveryDateManager\Api\Data\TimeIntervalInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TimeInterval extends AbstractDb
{
    /**
     * Tables name constant
     */
    public const MAIN_TABLE = 'amasty_deliverydate_time_interval';
    public const LABEL_TABLE = 'amasty_deliverydate_time_interval_label';
    public const CHANNEL_RELATION_TABLE = 'amasty_deliverydate_time_interval_delivery_channel';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, TimeIntervalInterface::INTERVAL_ID);
    }

    /**
     * @param int[] $intervalIds
     * @param int[] $storeIds
     *
     * @return array array(<interval_id> => array(array('store_id' => <store_id>, 'label' => <label>), ...), ...)
     */
    public function loadLabelsData(array $intervalIds, array $storeIds): array
    {
        $labelTable = $this->getTable(self::LABEL_TABLE);
        $select = $this->getConnection()->select()
            ->from($labelTable, ['interval_id', 'store_id', 'label'])
            ->where('interval_id IN (?)', $intervalIds)
            ->where('store_id IN (?)', $storeIds)
            ->order('store_id DESC');

        return $this->getConnection()->fetchAll($select, [], \Zend_Db::FETCH_GROUP|\Zend_Db::FETCH_ASSOC);
    }

    /**
     * @param int $intervalId
     * @param int $storeId
     *
     * @return string|null
     */
    public function getLabel(int $intervalId, int $storeId): ?string
    {
        $storeIds = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        if ($storeId) {
            $storeIds[] = $storeId;
        }

        $labelTable = $this->getTable(self::LABEL_TABLE);
        $select = $this->getConnection()->select()
            ->from($labelTable, ['label'])
            ->where('interval_id = ?', $intervalId)
            ->where('store_id IN (?)', $storeIds)
            ->order('store_id DESC')
            ->limit(1);

        return (string)$this->getConnection()->fetchOne($select);
    }
}
