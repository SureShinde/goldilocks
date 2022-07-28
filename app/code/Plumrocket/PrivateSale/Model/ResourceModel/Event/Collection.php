<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\ResourceModel\Event;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Eav\Model\EntityFactory;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory as EntityCollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Helper\Config as ConfigHelper;
use Plumrocket\PrivateSale\Model\Config\Source\EventStatus;
use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Plumrocket\PrivateSale\Model\CurrentDateTime;
use Plumrocket\PrivateSale\Model\ResourceModel\Event;
use Psr\Log\LoggerInterface;

/**
 * @since 5.0.0
 * @method EventInterface[]|\Plumrocket\PrivateSale\Model\Event[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var CurrentDateTime
     */
    protected $currentDateTime;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * Current store id
     *
     * @var int|null
     */
    private $storeId;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * Collection constructor.
     *
     * @param EntityCollectionFactory $entityCollectionFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param EntityFactory $eavEntityFactory
     * @param Helper $resourceHelper
     * @param UniversalFactory $universalFactory
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CurrentDateTime $currentDateTime
     * @param ConfigHelper $configHelper
     * @param AdapterInterface|null $connection
     */
    public function __construct(
        EntityCollectionFactory $entityCollectionFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        ResourceConnection $resource,
        EntityFactory $eavEntityFactory,
        Helper $resourceHelper,
        UniversalFactory $universalFactory,
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        CurrentDateTime $currentDateTime,
        ConfigHelper $configHelper,
        AdapterInterface $connection = null
    ) {
        parent::__construct(
            $entityCollectionFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $connection
        );
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->currentDateTime = $currentDateTime;
        $this->configHelper = $configHelper;
    }

    /**
     * Add active events conditions
     */
    public function addActiveFilters()
    {
        $currentDate = $this->currentDateTime->getCurrentGmtDate();
        $this->addAttributeToSelect(['event_from', 'event_to', 'enable'], 'left');

        $this->addAttributeToFilter([
            ['attribute' => 'event_from', 'lt' => $currentDate],
            ['attribute' => 'event_from', 'is' => new \Zend_Db_Expr('null')]
        ], 'left');

        $this->addAttributeToFilter([
            ['attribute' => 'event_to', 'gt' => $currentDate],
            ['attribute' => 'event_to', 'is' => new \Zend_Db_Expr('null')]
        ], 'left');

        $this->addAttributeToFilter('enable', 1, 'left');

        return $this;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addUnactiveFilters()
    {
        $currentDate = $this->currentDateTime->getCurrentGmtDate();
        $this->addAttributeToSelect(['event_from', 'event_to', 'enable'], 'left');
        $this->addAttributeToFilter('enable', 1, 'left');
        $this->addAttributeToFilter([
            ['attribute' => 'event_from', 'gt' => $currentDate],
            ['attribute' => 'event_to', 'lt' => $currentDate]
        ], 'left');

        return $this;
    }

    /**
     * @return \Magento\Framework\Data\Collection\AbstractDb|Collection
     */
    public function addPrivateEventFilter()
    {
        return $this->addAttributeToFilter('is_event_private', 1, 'left');
    }

    /**
     * Add product ids to event
     * @return $this
     */
    public function addProductIdsToCollection()
    {
        foreach ($this->getItems() as $event) {
            if ((int) $event->getData('event_type') === EventType::CATEGORY && $event->getData('category_event')) {
                try {
                    $category = $this->categoryRepository->get($event->getData('category_event'));
                    $productIds = $category->getProductCollection()->getAllIds();
                    $event->setData('product_ids', $productIds);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    continue;
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addStatusToCollection()
    {
        $currentDateTime = $this->currentDateTime->getCurrentGmtDate();

        $expression = <<<SQL
        (CASE
            WHEN {{enable}} = 0 THEN %s
            WHEN {{event_from}} IS NOT NULL AND {{event_from}} > "%s" AND DATE_SUB({{event_from}}, INTERVAL %d DAY) < "%s" THEN %s
            WHEN {{event_from}} IS NOT NULL AND {{event_from}} > "%s" THEN %s
            WHEN {{event_to}} IS NOT NULL AND {{event_to}} < "%s" THEN %s
            WHEN {{event_to}} IS NOT NULL AND DATE_ADD("%s", INTERVAL %d DAY) > {{event_to}} THEN %s
            ELSE %s
        END)
SQL;
        $expression = sprintf(
            $expression,
            EventStatus::DISABLED,
            $currentDateTime,
            $this->configHelper->getComingSoonDays(),
            $currentDateTime,
            EventStatus::COMING_SOON,
            $currentDateTime,
            EventStatus::UPCOMING,
            $currentDateTime,
            EventStatus::ENDED,
            $currentDateTime,
            $this->configHelper->getEndingSoonDays(),
            EventStatus::ENDING_SOON,
            EventStatus::ACTIVE
        );

        $this->addExpressionAttributeToSelect('status', $expression, ['event_from', 'event_to', 'enable']);

        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addSimpleStatusToCollection()
    {
        $currentDateTime = $this->currentDateTime->getCurrentGmtDate();

        $expression = <<<SQL
        (CASE
            WHEN {{enable}} = 0 THEN %s
            WHEN {{event_from}} IS NOT NULL AND {{event_from}} > "%s" THEN %s
            WHEN {{event_to}} IS NOT NULL AND {{event_to}} < "%s" THEN %s
            ELSE %s
        END)
SQL;
        $expression = sprintf(
            $expression,
            EventStatus::DISABLED,
            $currentDateTime,
            EventStatus::UPCOMING,
            $currentDateTime,
            EventStatus::ENDED,
            EventStatus::ACTIVE
        );

        $this->addExpressionAttributeToSelect('status', $expression, ['event_from', 'event_to', 'enable']);

        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setStoreId($id)
    {
        $this->storeId = $id ? (int) $id : null;
        return $this;
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(
            \Plumrocket\PrivateSale\Model\Event::class,
            Event::class
        );
    }

    /**
     * @inheritDoc
     */
    protected function _joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias)
    {
        if (isset($this->_joinAttributes[$fieldCode]['store_id'])) {
            $storeId = $this->_joinAttributes[$fieldCode]['store_id'];
        } else {
            $storeId = $this->getStoreId();
        }

        $connection = $this->getConnection();

        if ($storeId != Store::DEFAULT_STORE_ID && $attribute->getScope() !== EavAttributeInterface::SCOPE_STORE_TEXT) {
            /**
             * Add joining default value for not default store
             * if value for store is null - we use default value
             */
            $defCondition = '(' . implode(') AND (', $condition) . ')';
            $defAlias = $tableAlias . '_default';
            $defAlias = $this->getConnection()->getTableName($defAlias);
            $defFieldAlias = str_replace($tableAlias, $defAlias, $fieldAlias);
            $tableAlias = $this->getConnection()->getTableName($tableAlias);
            $defCondition = str_replace($tableAlias, $defAlias, $defCondition);
            $defCondition .= $connection->quoteInto(
                " AND " . $connection->quoteColumnAs("{$defAlias}.store_id", null) . " = ?",
                Store::DEFAULT_STORE_ID
            );

            $this->getSelect()->{$method}(
                [$defAlias => $attribute->getBackend()->getTable()],
                $defCondition,
                []
            );

            $method = 'joinLeft';
            $fieldAlias = $this->getConnection()->getCheckSql(
                "{$tableAlias}.value_id > 0",
                $fieldAlias,
                $defFieldAlias
            );

            $this->_joinAttributes[$fieldCode]['condition_alias'] = $fieldAlias;
            $this->_joinAttributes[$fieldCode]['attribute'] = $attribute;
        } else {
            $storeId = Store::DEFAULT_STORE_ID;
        }

        $condition[] = $connection->quoteInto(
            $connection->quoteColumnAs("{$tableAlias}.store_id", null) . ' = ?',
            $storeId
        );

        return parent::_joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias);
    }

    /**
     * @inheritDoc
     */
    protected function beforeAddLoadedItem(DataObject $item)
    {
        $this->getResource()->unserializeFields($item);
        return parent::beforeAddLoadedItem($item);
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param string $table
     * @param string $type
     * @return \Magento\Framework\DB\Select
     */
    protected function _addLoadAttributesSelectValues($select, $table, $type)
    {
        $storeId = $this->getStoreId();

        if ($storeId) {
            $connection = $this->getConnection();
            $valueExpr = $connection->getCheckSql('t_s.value_id IS NULL', 't_d.value', 't_s.value');

            $select->columns(
                ['default_value' => 't_d.value', 'store_value' => 't_s.value', 'value' => $valueExpr]
            );
        } else {
            $select = parent::_addLoadAttributesSelectValues($select, $table, $type);
        }

        return $select;
    }

    /**
     * @inheritDoc
     */
    protected function _getLoadAttributesSelect($table, $attributeIds = [])
    {
        if (empty($attributeIds)) {
            $attributeIds = $this->_selectAttributes;
        }

        $entity = $this->getEntity();
        $linkField = $entity->getLinkField();
        $storeId = $this->getStoreId();
        $connection = $this->getConnection();

        $select = $this->getConnection()->select()
            ->from(
                ['e' => $entity->getEntityTable()],
                ['entity_id']
            )
            ->join(
                ['t_d' => $table],
                "e.{$linkField} = t_d.{$linkField}",
                ['t_d.attribute_id']
            )->where(
                " e.entity_id IN (?)",
                array_keys($this->_itemsById)
            )->where(
                't_d.attribute_id IN (?)',
                $attributeIds
            );

        if ($storeId) {
            $joinCondition = [
                't_s.attribute_id = t_d.attribute_id',
                "t_s.{$linkField} = t_d.{$linkField}",
                $connection->quoteInto('t_s.store_id = ?', $storeId),
            ];

            $select->joinLeft(
                ['t_s' => $table],
                implode(' AND ', $joinCondition),
                []
            )->where(
                't_d.store_id = ?',
                $connection->getIfNullSql('t_s.store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            );
        } else {
            $select->where(
                'store_id = ?',
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            );
        }

        if ($entity->getEntityTable() == Entity::DEFAULT_ENTITY_TABLE && $entity->getTypeId()) {
            $select->where(
                't_d.entity_type_id =?',
                $entity->getTypeId()
            );
        }

        return $select;
    }

    /**
     * Return current store id
     *
     * @return int
     */
    private function getStoreId()
    {
        if (null === $this->storeId) {
            $this->storeId = $this->storeManager->getStore()->getId();
        }
        return $this->storeId;
    }
}
