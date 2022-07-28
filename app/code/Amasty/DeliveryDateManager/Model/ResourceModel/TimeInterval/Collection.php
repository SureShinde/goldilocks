<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval;

use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractTypifiedCollection;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set as ResourceModel;
use Magento\Framework\DB\Select;

/**
 * @method \Amasty\DeliveryDateManager\Model\TimeInterval\TimeIntervalDataModel[] getItems()
 * @method \Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval getResource()
 */
class Collection extends AbstractTypifiedCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'interval_id';

    protected function _construct()
    {
        $this->_init(
            \Amasty\DeliveryDateManager\Model\TimeInterval\TimeIntervalDataModel::class,
            \Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval::class
        );
    }

    /**
     * Join relations table for filter
     */
    public function joinRelations(): void
    {
        $select = $this->getSelect();
        $fromPart = $select->getPart(Select::FROM);

        if (!isset($fromPart['srel'])) {
            $select->joinLeft(
                ['srel' => $this->getTable(ResourceModel::TIME_SET_RELATION_TABLE)],
                'main_table.interval_id=srel.relation_id',
                []
            );
        }
    }

    /**
     * Join interval set table for filter
     */
    public function joinIntervalSetTable(): void
    {
        $select = $this->getSelect();
        $fromPart = $select->getPart(Select::FROM);

        if (!isset($fromPart['timeset']) && isset($fromPart['srel'])) {
            $select->joinLeft(
                ['timeset' => $this->getTable(Set::MAIN_TABLE)],
                'srel.set_id=timeset.id',
                ['timeset.id']
            );
        }
    }

    /**
     * @param int $storeId
     */
    public function addLabelsToItems(int $storeId): void
    {
        $timeIds = $this->getAllIds();
        $labels = $this->getResource()->loadLabelsData($timeIds, [0, $storeId]);

        foreach ($labels as $intervalId => $timeIntervalLabels) {
            $labelData = reset($timeIntervalLabels);
            if (isset($labelData['label'])) {
                $this->getItemById($intervalId)->setLabel($labelData['label']);
            }
        }
    }
}
