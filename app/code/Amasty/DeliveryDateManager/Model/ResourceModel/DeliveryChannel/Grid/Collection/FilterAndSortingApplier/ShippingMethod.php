<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection\FilterAndSortingApplier;

use Amasty\DeliveryDateManager\Model\ResourceModel\Collection\FilterAndSortingApplierInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ShippingMethod implements FilterAndSortingApplierInterface
{
    public const ALIAS = 'ship';

    /**
     * @var array
     */
    private $applicableFields = [
        'shipping_method'
    ];

    public function __construct(
        array $applicableFields = []
    ) {
        $this->applicableFields = array_merge($this->applicableFields, $applicableFields);
    }

    /**
     * @param AbstractCollection $collection
     * @param string $field
     * @param array|string|null $condition
     */
    public function applyFilter(AbstractCollection $collection, string $field, $condition = null): void
    {
        $this->joinTable($collection);
        $collection
            ->addFilter($field, $condition, 'public')
            ->addFilterToMap($field, self::ALIAS . '.' . $field);
    }

    /**
     * @param AbstractCollection $collection
     * @param string $field
     * @param string $direction
     */
    public function applySorting(AbstractCollection $collection, string $field, string $direction): void
    {
        $this->joinTable($collection);
        $collection->getSelect()->order(self::ALIAS . '.' . $field . ' ' . $direction);
    }

    /**
     * @param string $field
     * @return bool
     */
    public function canApply(string $field): bool
    {
        return in_array($field, $this->applicableFields);
    }

    /**
     * @param AbstractCollection $collection
     * @return void
     */
    private function joinTable(AbstractCollection $collection): void
    {
        $fromPart = (array)$collection->getSelect()->getPart(Select::FROM);

        if (!array_key_exists(self::ALIAS, $fromPart)) {
            $collection->getSelect()->joinLeft(
                [self::ALIAS => $collection->getTable(DeliveryChannel::SCOPE_SHIPPING_METHOD_TABLE)],
                'main_table.channel_id = ' . self::ALIAS . '.channel_id',
                []
            );
        }
    }
}
