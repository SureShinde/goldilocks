<?php

namespace Ecombricks\StoreInventory\Plugin\Bundle\Model\Product\Type;

use Closure;
use Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockByStoreInterface;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\InventoryBundleProduct\Model\GetBundleProductStockStatus;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Adapt 'Is Salable' for bundle product for multi stock environment plugin.
 */
class AdaptIsSalablePlugin
{

    /**
     * @var GetBundleProductStockStatus
     */
    private $getBundleProductStockStatus;

    /**
     * @var DefaultStockProviderInterface
     */
    private GetStockByStoreInterface $getStockByStore;

    /**
     * @param IsProductSalableInterface $isProductSalable
     * @param StoreManagerInterface $storeManager
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param GetBundleProductStockStatus $getBundleProductStockStatus
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        GetBundleProductStockStatus $getBundleProductStockStatus,
        GetStockByStoreInterface    $getStockByStore
    ) {
        $this->getBundleProductStockStatus = $getBundleProductStockStatus;
        $this->getStockByStore = $getStockByStore;
    }

    /**
     * Verify, is product salable in multi stock environment.
     *
     * @param Type $subject
     * @param Closure $proceed
     * @param Product $product
     * @return bool
     */
    public function aroundIsSalable(Type $subject, Closure $proceed, Product $product): bool
    {
        if ($product->hasData('all_items_salable')) {
            return $product->getData('all_items_salable');
        }
        $stock = $this->getStockByStore->execute();
        $options = $subject->getOptionsCollection($product);
        $isSalable = $this->getBundleProductStockStatus->execute($product, $options->getItems(), $stock->getStockId());
        $product->setData('all_items_salable', $isSalable);

        return $isSalable;
    }
}
