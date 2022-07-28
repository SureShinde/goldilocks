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

namespace Plumrocket\PrivateSale\Model\Catalog\Category;

use Magento\Framework\App\ResourceConnection;
use Plumrocket\PrivateSale\Model\Catalog\CatalogCategoryProduct;

/**
 * @since v5.0.0
 */
class GetProductIds
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\CatalogCategoryProduct
     */
    private $catalogCategoryProduct;

    /**
     * @var array[]
     */
    private $cache = [];

    /**
     * @param \Magento\Framework\App\ResourceConnection                    $resourceConnection
     * @param \Plumrocket\PrivateSale\Model\Catalog\CatalogCategoryProduct $catalogCategoryProduct
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CatalogCategoryProduct $catalogCategoryProduct
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->catalogCategoryProduct = $catalogCategoryProduct;
    }

    /**
     * @param array $categories
     * @param int   $storeId
     * @return array
     */
    public function execute(array $categories, int $storeId = null): array
    {
        $key = implode('-', $categories) . '||' . $storeId;

        if (! isset($this->cache[$key])) {
            $select = $this->catalogCategoryProduct->getSelect(['product_id'], $storeId);
            $select->where('category_id IN (?)', $categories);

            $this->cache[$key] = array_unique($this->resourceConnection->getConnection()->fetchCol($select));
        }

        return $this->cache[$key];
    }
}
