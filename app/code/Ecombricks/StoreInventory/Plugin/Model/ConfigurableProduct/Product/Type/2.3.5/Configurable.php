<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\ConfigurableProduct\Product\Type;

if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.5', '>=')) :

/**
 * Configurable product type plugin
 */
class Configurable
{

    /**
     * Stock configuration
     *
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;

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
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\InventorySalesApi\Api\IsProductSalableInterface $isProductSalable
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\InventorySalesApi\Api\IsProductSalableInterface $isProductSalable,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
    )
    {
        $this->stockConfiguration = $stockConfiguration;
        $this->isProductSalable = $isProductSalable;
        $this->getStockIdByStore = $getStockIdByStore;
    }

    /**
     * After get used products
     *
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject
     * @param array $products
     * @return array
     */
    public function afterGetUsedProducts(\Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject, array $products): array
    {
        $stockId = $this->getStockIdByStore->execute();
        foreach ($products as $key => $product) {
            if ($this->isProductSalable->execute($product->getSku(), $stockId)) {
                continue;
            }
            $product->setIsSalable(0);
            if (!$this->stockConfiguration->isShowOutOfStock()) {
                unset($products[$key]);
            }
        }
        return $products;
    }

}

endif;
