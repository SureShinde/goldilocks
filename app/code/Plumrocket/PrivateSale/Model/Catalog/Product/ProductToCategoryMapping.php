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

namespace Plumrocket\PrivateSale\Model\Catalog\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\PrivateSale\Model\Catalog\CatalogCategoryProduct;

/**
 * @since v5.0.0
 */
class ProductToCategoryMapping
{
    /**
     * @var array[]
     */
    protected $cache = [];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\CatalogCategoryProduct
     */
    private $catalogCategoryProduct;

    /**
     * @param ResourceConnection                         $resourceConnection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CatalogCategoryProduct                     $catalogCategoryProduct
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        CatalogCategoryProduct $catalogCategoryProduct
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->catalogCategoryProduct = $catalogCategoryProduct;
    }

    /**
     * Retrieve array of category ids product belongs to
     *
     * @param int      $productId
     * @param int|null $storeId
     * @return int[]
     */
    public function getForProduct(int $productId, int $storeId = null): array
    {
        if (null === $storeId) {
            $storeId = (int) $this->storeManager->getStore()->getId();
        }

        if (! isset($this->cache[$storeId][$productId])) {
            $rows = $this->findInDb([$productId], $storeId);
            foreach ($rows as $row) {
                $this->addToCache($storeId, $productId, (int) $row['category_id']);
            }
        }

        return $this->cache[$storeId][$productId] ?? [];
    }

    /**
     * Retrieve array of category ids for each product
     *
     * @param array    $productIds
     * @param int|null $storeId
     * @return array
     */
    public function getForProducts(array $productIds, int $storeId = null): array
    {
        if (null === $storeId) {
            $storeId = (int) $this->storeManager->getStore()->getId();
        }

        $rows = $this->findInDb($productIds, $storeId);

        foreach ($rows as $row) {
            $this->addToCache($storeId, (int) $row['product_id'], (int) $row['category_id']);
        }

        return $this->cache[$storeId] ?? [];
    }

    /**
     * @param int $storeId
     * @param int $productId
     * @param int $categoryId
     */
    private function addToCache(int $storeId, int $productId, int $categoryId)
    {
        if (! isset($this->cache[$storeId])) {
            $this->cache[$storeId] = [];
        }

        if (! isset($this->cache[$storeId][$productId])) {
            $this->cache[$storeId][$productId] = [];
        }

        $this->cache[$storeId][$productId][] = $categoryId;
    }

    /**
     * @param int $storeId
     * @param array $productIds
     * @return array
     */
    private function findInDb(array $productIds, int $storeId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('catalog_category_product');

        $select = $connection->select()
                   ->from(['cat_index' => $tableName], ['category_id', 'product_id'])
                   ->where('product_id IN (?)', $productIds);

        return $connection->fetchAll($select);
    }
}
