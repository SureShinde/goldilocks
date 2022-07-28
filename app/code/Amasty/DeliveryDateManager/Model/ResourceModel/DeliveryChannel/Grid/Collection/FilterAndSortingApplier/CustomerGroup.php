<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection\FilterAndSortingApplier;

use Amasty\DeliveryDateManager\Model\ResourceModel\Collection\FilterAndSortingApplierInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class CustomerGroup implements FilterAndSortingApplierInterface
{
    public const ALIAS = 'group';

    /**
     * @var array
     */
    private $fieldsMap = [
        'customer_group_id' => self::ALIAS . '.group_id'
    ];

    public function __construct(
        array $fieldsMap = []
    ) {
        $this->fieldsMap = array_merge($this->fieldsMap, $fieldsMap);
    }

    /**
     * @param AbstractCollection $collection
     * @param string $field
     * @param array|string|null $condition
     */
    public function applyFilter(AbstractCollection $collection, string $field, $condition = null): void
    {
        $mappedField = $this->fieldsMap[$field];

        $this->joinTable($collection);
        $collection
            ->addFilter($field, $condition, 'public')
            ->addFilterToMap($field, $mappedField);
    }

    /**
     * @param AbstractCollection $collection
     * @param string $field
     * @param string $direction
     */
    public function applySorting(AbstractCollection $collection, string $field, string $direction): void
    {
        $mappedField = $this->fieldsMap[$field];

        $this->joinTable($collection);
        $collection->getSelect()->order($mappedField . ' ' . $direction);
    }

    /**
     * @param string $field
     * @return bool
     */
    public function canApply(string $field): bool
    {
        $applicableFields = array_keys($this->fieldsMap);
        $mappedField = $this->fieldsMap[$field] ?? null;

        return in_array($field, $applicableFields) && $mappedField;
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
                [self::ALIAS => $collection->getTable(DeliveryChannel::SCOPE_CUSTOMER_GROUP_TABLE)],
                'main_table.channel_id = ' . self::ALIAS . '.channel_id',
                []
            );
        }
    }
}
