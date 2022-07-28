<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\CatalogInventory\Model\StockStateProvider;

use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Product\Constants;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockStateProvider;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\FormatInterface;

class CheckQuoteItemQty
{
    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        FormatInterface $localeFormat,
        ConfigProvider $configProvider
    ) {
        $this->localeFormat = $localeFormat;
        $this->configProvider = $configProvider;
    }

    /**
     * @param StockStateProvider $subject
     * @param DataObject $result
     * @param StockItemInterface $stockItem
     * @param string|float|int|null $qty
     * @param string|float|int|null $summaryQty
     * @return DataObject
     */
    public function afterCheckQuoteItemQty(
        StockStateProvider $subject,
        DataObject $result,
        StockItemInterface $stockItem,
        $qty,
        $summaryQty
    ): DataObject {
        $qty = max($this->getNumber($qty), $this->getNumber($summaryQty));

        if ($stockItem->getBackorders() == Constants::BACKORDERS_PREORDER_OPTION
            && $stockItem->getQty() > 0
            && $qty > $stockItem->getQty()
        ) {
            if (!$this->configProvider->isAllowEmpty()) {
                $message = $this->getResultMessage(
                    $this->configProvider->getBelowZeroMessage(),
                    $stockItem->getProductName(),
                    (float) $stockItem->getQty()
                );
                $result->setMessage($message);
            } elseif ($this->configProvider->isDisableForPositiveQty()) {
                $message = $this->getResultMessage(
                    $this->configProvider->getCartMessage(),
                    $stockItem->getProductName(),
                    (float) $result->getItemBackorders()
                );
                $result->setMessage($message);
            }
        }

        return $result;
    }

    /**
     * @param string|float|int|null $qty
     * @return float|null
     */
    private function getNumber($qty): ?float
    {
        if (!is_numeric($qty)) {
            return $this->localeFormat->getNumber($qty);
        }

        return $qty;
    }

    private function getResultMessage(string $message, string $productName, float $qty): string
    {
        return sprintf(
            $message,
            $productName,
            $qty
        );
    }
}
