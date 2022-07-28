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
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\Catalog;

use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Util class for working with category to product table
 *
 * @since 5.0.0
 */
class CatalogCategoryProduct
{
    /**
     * @var IndexScopeResolver
     */
    private $tableResolver;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver $tableResolver
     * @param \Magento\Framework\Indexer\DimensionFactory                 $dimensionFactory
     * @param \Magento\Store\Model\StoreManagerInterface                  $storeManager
     * @param \Magento\Framework\App\ResourceConnection                   $resourceConnection
     */
    public function __construct(
        IndexScopeResolver $tableResolver,
        DimensionFactory $dimensionFactory,
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection
    ) {
        $this->tableResolver = $tableResolver;
        $this->dimensionFactory = $dimensionFactory;
        $this->storeManager = $storeManager;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getTableName(int $storeId = null): string
    {
        if (! $storeId) {
            $storeId = (int) $this->storeManager->getDefaultStoreView()->getId();
        }

        $dimension = $this->dimensionFactory->create(Store::ENTITY, (string) $storeId);
        return $this->tableResolver->resolve(
            AbstractAction::MAIN_INDEX_TABLE,
            [$dimension]
        );
    }

    /**
     * @param array    $columns
     * @param int|null $storeId
     * @return \Magento\Framework\DB\Select
     */
    public function getSelect(array $columns, int $storeId = null): Select
    {
        if (null === $storeId) {
            $storeId = (int) $this->storeManager->getDefaultStoreView()->getId();
        }

        $connection = $this->resourceConnection->getConnection();
        $categoryProductTable = $this->getTableName($storeId);
        $storeTable = $this->resourceConnection->getTableName(Store::ENTITY);
        $storeGroupTable = $this->resourceConnection->getTableName(Group::ENTITY);

        return $connection->select()
             ->from(['cat_index' => $categoryProductTable], $columns)
             ->joinInner(['store' => $storeTable], $connection->quoteInto('store.store_id = ?', $storeId), [])
             ->joinInner(
                 ['store_group' => $storeGroupTable],
                 'store.group_id = store_group.group_id AND cat_index.category_id != store_group.root_category_id',
                 []
             );
    }
}
