<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\InventorySales\Model\ResourceModel\IsStockItemSalableCondition\BackordersCondition;

use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Product\Constants;
//phpcs:ignore Generic.Files.LineLength.TooLong
use Magento\InventorySales\Model\ResourceModel\IsStockItemSalableCondition\BackordersCondition as NativeBackordersCondition;

class ModifyCondition
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param NativeBackordersCondition $backordersCondition
     * @param string $condition
     * @return string
     *
     * @see \Magento\InventorySales\Model\ResourceModel\IsStockItemSalableCondition\IsStockItemSalableConditionChain
     * Used in reindex inventory_stock_%d tables.
     */
    public function afterExecute(NativeBackordersCondition $backordersCondition, string $condition): string
    {
        if ($this->configProvider->isEnabled()) {
            $condition = '(' . $condition . ') OR (legacy_stock_item.backorders = '
                . Constants::BACKORDERS_PREORDER_OPTION;
            if (!$this->configProvider->isAllowEmpty()) {
                $condition .= ' AND SUM(IF(source_item.status = 0, 0, quantity)) > 0';
            } elseif ($this->configProvider->isDisableForPositiveQty()) {
                $condition .= ' AND SUM(IF(source_item.status = 0, 0, quantity)) <= 0';
            }
            $condition .= ')';
        }

        return $condition;
    }
}
