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

/**
 * @since v5.0.0
 */
class GetCategoryIds
{
    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\Product\ProductToCategoryMapping
     */
    private $productToCategoryMapping;

    /**
     * @param \Plumrocket\PrivateSale\Model\Catalog\Product\ProductToCategoryMapping $productToCategoryMapping
     */
    public function __construct(
        ProductToCategoryMapping $productToCategoryMapping
    ) {
        $this->productToCategoryMapping = $productToCategoryMapping;
    }

    /**
     * @param array $productIds
     * @param int   $storeId
     * @return array
     */
    public function execute(array $productIds, int $storeId = null): array
    {
        $mapping = $this->productToCategoryMapping->getForProducts($productIds, $storeId);
        if (! $mapping) {
            return [];
        }

        return array_unique(array_merge(...$mapping));
    }
}
