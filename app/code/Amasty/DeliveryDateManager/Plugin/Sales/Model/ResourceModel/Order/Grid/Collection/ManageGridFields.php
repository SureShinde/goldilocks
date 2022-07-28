<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Plugin\Sales\Model\ResourceModel\Order\Grid\Collection;

use Amasty\DeliveryDateManager\Model\DeliveryOrder\OutputFormatter;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateOrder;
use Amasty\DeliveryDateManager\Model\TimeInterval\MinsToTimeConverter;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult as GridSearchResult;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;

/**
 * Add Data, Filters, Sorting functional for Order/Shipment/Invoice Adminhtml Grids for Delivery Date fields
 */
class ManageGridFields
{
    /**
     * key - grid column name
     * value - sql column name
     */
    public const DELIVERY_COLUMN = [
        'amasty_deliverydate_date' => 'amdeliverydate.date',
        'amasty_deliverydate_time_from' => 'amdeliverydate.time_from',
        'amasty_deliverydate_time_to' => 'amdeliverydate.time_to',
        'amasty_deliverydate_time_interval_id' => 'amdeliverydate.time_interval_id',
        'amasty_deliverydate_comment' => 'amdeliverydate.comment'
    ];

    /**
     * @var DeliveryDateOrder
     */
    private $deliverydateResource;

    /**
     * @var OutputFormatter
     */
    private $outputFormatter;

    /**
     * @var MinsToTimeConverter
     */
    private $minsToTimeConverter;

    public function __construct(
        DeliveryDateOrder $deliverydateResource,
        OutputFormatter $outputFormatter,
        MinsToTimeConverter $minsToTimeConverter
    ) {
        $this->deliverydateResource = $deliverydateResource;
        $this->outputFormatter = $outputFormatter;
        $this->minsToTimeConverter = $minsToTimeConverter;
    }

    /**
     * Join `amasty_deliverydate_deliverydate_order` table columns
     *
     * @param GridSearchResult $collection
     * @param Select|null $select
     *
     * @return Select|null
     */
    public function afterGetSelect(GridSearchResult $collection, ?Select $select): ?Select
    {
        if ($select && !array_key_exists('amdeliverydate', $select->getPart('from'))) {
            $orderIdKey = 'order_id';
            if ($collection instanceof OrderGridCollection) {
                $orderIdKey = 'entity_id';
            }
            $select->joinLeft(
                ['amdeliverydate' => $this->deliverydateResource->getMainTable()],
                'main_table.' . $orderIdKey . ' = amdeliverydate.order_id',
                self::DELIVERY_COLUMN
            );
        }

        return $select;
    }

    /**
     * Prepare items delivery date to format for Grid
     *
     * @param GridSearchResult $collection
     * @param DataObject $item
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddItem(GridSearchResult $collection, DataObject $item): array
    {
        if (!$item->getDataByKey('amasty_deliverydate_date')) {
            return [$item];
        }

        if ($item->getDataByKey('amasty_deliverydate_time_from') !== null
            && $item->getDataByKey('amasty_deliverydate_time_from') !== 0
        ) {
            $timeFrom = (int)$item->getDataByKey('amasty_deliverydate_time_from');
            $timeFrom = $this->minsToTimeConverter->execute($timeFrom);
            $item->setData('amasty_deliverydate_time_from', $timeFrom);
        } else {
            $item->setData('amasty_deliverydate_time_from', '--');
        }

        if ($item->getDataByKey('amasty_deliverydate_time_to') !== null
            && $item->getDataByKey('amasty_deliverydate_time_from') !== 0
        ) {
            $timeTo = (int)$item->getDataByKey('amasty_deliverydate_time_to');
            $timeTo = $this->minsToTimeConverter->execute($timeTo);
            $item->setData('amasty_deliverydate_time_to', $timeTo);
        } else {
            $item->setData('amasty_deliverydate_time_to', '--');
        }

        return [$item];
    }

    /**
     * Prepare fields condition and value for filter
     *
     * @param GridSearchResult $collection
     * @param string|array $field
     * @param null|string|array $condition
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddFieldToFilter(GridSearchResult $collection, $field, $condition = null): array
    {
        if (\is_array($field)) {
            foreach ($field as $key => $fieldItem) {
                $field[$key] = $this->mapField($fieldItem);
            }
        } elseif (\is_string($field)) {
            $field = $this->mapField($field);
        }

        return [$field, $condition];
    }

    private function mapField(string $field): string
    {
        return self::DELIVERY_COLUMN[$field] ?? $field;
    }
}
