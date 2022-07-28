<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\CatalogInventoryGraphQl\Resolver;

/**
 * Only x left in stock resolver plugin
 */
class OnlyXLeftInStockResolver
{

    /**
     * Scope configuration
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Stock registry
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Get threshold qty
     *
     * @return float
     */
    protected function getThresholdQty(): float
    {
        return (float) $this->scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_STOCK_THRESHOLD_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get only x left qty
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return float|null
     */
    protected function getOnlyXLeftQty(\Magento\Catalog\Api\Data\ProductInterface $product): ?float
    {
        $thresholdQty = $this->getThresholdQty();
        if ($thresholdQty === 0) {
            return null;
        }
        $productId = $product->getId();
        $stockItem = $this->stockRegistry->getStockItem($productId);
        $stockStatus = $this->stockRegistry->getStockStatus($productId, $product->getStore()->getId());
        $qty = $stockStatus->getQty();
        $qtyLeft = $qty - $stockItem->getMinQty();
        if ($qty > 0 && $qtyLeft <= $thresholdQty) {
            return (float) $qtyLeft;
        }
        return null;
    }

    /**
     * Around resolve
     *
     * @param \Magento\CatalogInventoryGraphQl\Model\Resolver\OnlyXLeftInStockResolver $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws \Exception
     * @return mixed|\Magento\Framework\GraphQl\Query\Resolver\Value
     */
    public function aroundResolve(
        \Magento\CatalogInventoryGraphQl\Model\Resolver\OnlyXLeftInStockResolver $subject,
        \Closure $proceed,
        \Magento\Framework\GraphQl\Config\Element\Field $field,
        $context,
        \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info,
        array $value = null,
        array $args = null
    )
    {
        if (
            !array_key_exists('model', $value) ||
            !$value['model'] instanceof \Magento\Catalog\Api\Data\ProductInterface
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(__('"model" value should be specified'));
        }
        return $this->getOnlyXLeftQty($value['model']);
    }


}
