<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Helper\CatalogInventory;

if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.5', '>=')) :

/**
 * Stock helper plugin
 */
class Stock
{

    /**
     * Configurable product type
     *
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableProductType;

    /**
     * Is product salable
     *
     * @var \Magento\InventorySalesApi\Api\IsProductSalableInterface
     */
    protected $isProductSalable;

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Constructor
     *
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProductType
     * @param \Magento\InventorySalesApi\Api\IsProductSalableInterface $isProductSalable
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProductType,
        \Magento\InventorySalesApi\Api\IsProductSalableInterface $isProductSalable,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        $this->configurableProductType = $configurableProductType;
        $this->isProductSalable = $isProductSalable;
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * Before assign status to product
     *
     * @param \Magento\CatalogInventory\Helper\Stock $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param int|null $status
     * @return array
     */
    public function beforeAssignStatusToProduct(
        \Magento\CatalogInventory\Helper\Stock $subject,
        \Magento\Catalog\Model\Product $product,
        $status = null
    ): array
    {
        if ($product->getTypeId() !== \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return [$product, $status];
        }
        $stockId = $this->getStockIdByStore->execute();
        $options = $this->configurableProductType->getConfigurableOptions($product);
        $status = 0;
        foreach ($options as $attribute) {
            foreach ($attribute as $option) {
                if ($this->isProductSalable->execute($option['sku'], $stockId)) {
                    $status = 1;
                    break;
                }
            }
        }
        return [$product, $status];
    }

}

endif;
