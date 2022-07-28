<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Grid;

use Amasty\DeliveryDateManager\Model\ResourceModel\Collection\Processor as CollectionProcessor;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Collection as DeliveryChannelCollection;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Psr\Log\LoggerInterface;

class Collection extends DeliveryChannelCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    private $aggregations;

    /**
     * @var CollectionProcessor
     */
    private $collectionProcessor;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        CollectionProcessor $collectionProcessor,
        $resourceModel,
        $model = Document::class,
        $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->collectionProcessor = $collectionProcessor;
        $this->_init($model, $resourceModel);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * @param ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * @param array|string $field
     * @param string|array|null $condition
     * @return $this|Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (is_string($field) && $this->collectionProcessor->applyFilter($this, $field, $condition)) {
            return $this;
        }

        return parent::addFieldToFilter('main_table.' . $field, $condition);
    }

    /**
     * @param string $field
     * @param string $direction
     * @return $this|Collection
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($this->collectionProcessor->applySorting($this, $field, $direction)) {
            return $this;
        }

        return parent::setOrder($field, $direction);
    }

    /**
     * @param string $field
     * @param string $direction
     * @return $this|Collection
     */
    public function addOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($this->collectionProcessor->applySorting($this, $field, $direction)) {
            return $this;
        }

        return parent::addOrder($field, $direction);
    }

    /**
     * Attach additional related data to collection
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->collectionProcessor->attachData($this);

        return parent::_afterLoad();
    }
}
