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

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Retrieve array of product id with children product ids (if product has children)
 *
 * @since v5.0.0
 */
class GetUsedProductIds
{
    /**
     * @var \Plumrocket\PrivateSale\Model\Catalog\Product\GetChildrenProductIds
     */
    private $getChildrenProductIds;

    /**
     * GetUsedProducts constructor.
     *
     * @param \Plumrocket\PrivateSale\Model\Catalog\Product\GetChildrenProductIds $getChildrenProductIds
     */
    public function __construct(GetChildrenProductIds $getChildrenProductIds)
    {
        $this->getChildrenProductIds = $getChildrenProductIds;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return int[]|string[]
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(ProductInterface $product): array
    {
        $childrenIds = $this->getChildrenProductIds->execute($product);
        if ($childrenIds) {
            return array_unique(array_merge([(int) $product->getId()], $childrenIds));
        }

        return [(int) $product->getId()];
    }
}
