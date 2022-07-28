<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid\Collection\FilterAndSortingApplier;

use Amasty\DeliveryDateManager\Api\Data\ChannelConfigDataInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\Collection\FilterAndSortingApplierInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\ChannelConfig as ResourceChannelConfig;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ChannelConfig implements FilterAndSortingApplierInterface
{
    public const ALIAS = 'ch_conf';

    /**
     * @var array
     */
    private $applicableFields = [
        ChannelConfigDataInterface::MIN,
        ChannelConfigDataInterface::MAX,
        ChannelConfigDataInterface::IS_SAME_DAY_AVAILABLE,
        ChannelConfigDataInterface::SAME_DAY_CUTOFF,
        ChannelConfigDataInterface::ORDER_TIME,
        ChannelConfigDataInterface::BACKORDER_TIME
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
                [self::ALIAS => $collection->getTable(ResourceChannelConfig::MAIN_TABLE)],
                'main_table.config_id = ' . self::ALIAS . '.' . ChannelConfigDataInterface::ID,
                []
            );
        }
    }
}
