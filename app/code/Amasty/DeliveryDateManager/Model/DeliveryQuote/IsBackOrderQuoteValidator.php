<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryQuote;

use Amasty\DeliveryDateManager\Model\Validator\IsBackOrderInterface;
use Magento\Checkout\Model\Session;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySales\Model\StockResolver;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;

/**
 * Validate for backorders in the Quote
 */
class IsBackOrderQuoteValidator implements IsBackOrderInterface
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var StockResolver
     */
    private $stockResolver;

    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetStockItemDataInterface $getStockItemData,
        GetProductSalableQtyInterface $getProductSalableQty,
        Session $checkoutSession,
        StockResolver $stockResolver
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getStockItemData = $getStockItemData;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->checkoutSession = $checkoutSession;
        $this->stockResolver = $stockResolver;
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        $quote = $this->checkoutSession->getQuote();
        $websiteCode = $quote->getStore()->getWebsite()->getCode();
        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = $stock->getStockId();
        foreach ((array)$quote->getItems() as $item) {
            $stockItemConfiguration = $this->getStockItemConfiguration->execute($item->getSku(), $stockId);
            $backorders = $stockItemConfiguration->getBackorders();
            if ($backorders && $backorders !== StockItemConfigurationInterface::BACKORDERS_NO) {
                $stockItemData = $this->getStockItemData->execute($item->getSku(), $stockId);
                if ($stockItemData === null) {
                    continue;
                }

                $requestedQty = $item->getQty();

                $salableQty = $this->getProductSalableQty->execute($item->getSku(), $stockId);
                $backOrderQty = $requestedQty - $salableQty;
                $displayQty = $this->getDisplayQty($backOrderQty, $salableQty, $requestedQty);

                if ($displayQty > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get display quantity to show the number of quantity customer can backorder
     *
     * @param float $backOrderQty
     * @param float $salableQty
     * @param float $requestedQty
     * @return float
     */
    private function getDisplayQty(float $backOrderQty, float $salableQty, float $requestedQty): float
    {
        $displayQty = 0;
        if ($backOrderQty > 0 && $salableQty > 0) {
            $displayQty = $backOrderQty;
        } elseif ($requestedQty > $salableQty) {
            $displayQty = $requestedQty;
        }

        return $displayQty;
    }
}
