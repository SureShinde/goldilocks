<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\CatalogInventory\Model\StockStateProvider;

use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Product\Constants;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockStateProvider;

class VerifyStock
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function afterVerifyStock(
        StockStateProvider $subject,
        bool $result,
        StockItemInterface $stockItem
    ) : bool {
        if ($result === false
            && $stockItem->getQty() <= $stockItem->getMinQty()
            && $stockItem->getBackorders() == Constants::BACKORDERS_PREORDER_OPTION
        ) {
            $result = $this->configProvider->isAllowEmpty();
        }

        return $result;
    }
}
