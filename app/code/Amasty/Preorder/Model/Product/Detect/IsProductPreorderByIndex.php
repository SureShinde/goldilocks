<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Detect;

use Amasty\Preorder\Model\ResourceModel\Product\PreorderIndex\IsItemExist;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\StoreManagerInterface;

class IsProductPreorderByIndex implements IsProductPreorderInterface
{
    /**
     * @var IsItemExist
     */
    private $isItemExist;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(IsItemExist $isItemExist, StoreManagerInterface $storeManager)
    {
        $this->isItemExist = $isItemExist;
        $this->storeManager = $storeManager;
    }

    public function execute(ProductInterface $product, float $requiredQty = 1): bool
    {
        $websiteId = (int) $this->storeManager->getStore($product->getStoreId())->getWebsiteId();
        return $this->isItemExist->execute((int) $product->getId(), $websiteId);
    }
}
