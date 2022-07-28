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

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * Retrieve array of Children product ids
 *
 * @since v5.0.0
 */
class GetChildrenProductIds
{
    /**
     * Save results for repeated using
     *
     * @var array
     */
    private $localCache = [];

    /**
     * @var \Magento\ConfigurableProduct\Api\LinkManagementInterface
     */
    private $configurableLinkManager;

    /**
     * @var \Magento\Bundle\Api\ProductLinkManagementInterface
     */
    private $bundleLinkManagement;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * GetUsedProducts constructor.
     *
     * @param \Magento\ConfigurableProduct\Api\LinkManagementInterface $configurableLinkManager
     * @param \Magento\Bundle\Api\ProductLinkManagementInterface       $bundleLinkManagement
     * @param \Magento\Catalog\Model\ResourceModel\Product             $productResource
     */
    public function __construct(
        LinkManagementInterface $configurableLinkManager,
        ProductLinkManagementInterface $bundleLinkManagement,
        Product $productResource
    ) {
        $this->configurableLinkManager = $configurableLinkManager;
        $this->bundleLinkManagement = $bundleLinkManagement;
        $this->productResource = $productResource;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return int[]|string[]
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(ProductInterface $product): array
    {
        if (! isset($this->localCache[$product->getId()])) {
            $childrenIds = [];

            switch ($product->getTypeId()) {
                case Configurable::TYPE_CODE:
                    $childrenIds = array_map(
                        static function (ProductInterface $product) {
                            return (int) $product->getId();
                        },
                        $this->configurableLinkManager->getChildren($product->getSku())
                    );
                    break;

                case Grouped::TYPE_CODE:
                    $childrenIds = array_map(
                        static function (ProductInterface $product) {
                            return (int) $product->getId();
                        },
                        $product->getTypeInstance()->getAssociatedProducts($product)
                    );
                    break;

                case Bundle::TYPE_CODE:
                    $links = $this->bundleLinkManagement->getChildren($product->getSku());

                    $productSkus = array_map(
                        static function (LinkInterface $link) {
                            return $link->getSku();
                        },
                        $links
                    );

                    $childrenIds = $this->productResource->getProductsIdsBySkus($productSkus);
                    break;
            }

            $this->localCache[$product->getId()] = $childrenIds;
        }

        return $this->localCache[$product->getId()];
    }
}
