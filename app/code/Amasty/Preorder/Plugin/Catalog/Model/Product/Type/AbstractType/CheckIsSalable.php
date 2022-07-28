<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Catalog\Model\Product\Type\AbstractType;

use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Product\Constants;
use Amasty\Preorder\Model\Product\GetPreorderInformation;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\CatalogInventory\Model\StockRegistry;

class CheckIsSalable
{
    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    public function __construct(
        StockRegistry $stockRegistry,
        ConfigProvider $configProvider,
        GetPreorderInformation $getPreorderInformation
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->configProvider = $configProvider;
        $this->getPreorderInformation = $getPreorderInformation;
    }

    public function afterIsSalable(AbstractType $subject, bool $salable, Product $product): bool
    {
        if ($this->isProductShouldBeOutOfStock($product)) {
            $salable = false;
        }

        return $salable;
    }

    /**
     * If product has  Backorders set to Amasty BACKORDERS_PREORDER_OPTION , but dont satisfy qty condition ,
     * that mean product should be out of stock
     * @param Product $product
     * @return bool
     */
    private function isProductShouldBeOutOfStock(Product $product): bool
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());

        return $stockItem->getBackorders() == Constants::BACKORDERS_PREORDER_OPTION
            && !$this->configProvider->isAllowEmpty()
            && !$product->isComposite()
            && !$this->getPreorderInformation->execute($product)->isPreorder();
    }
}
