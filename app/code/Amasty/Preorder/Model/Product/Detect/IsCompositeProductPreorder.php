<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Detect;

use Amasty\Preorder\Model\ConfigProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\StoreManagerInterface;

class IsCompositeProductPreorder implements IsProductPreorderInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var IsProductPreorderInterface[]
     */
    private $pool;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        array $pool = []
    ) {
        $this->configProvider = $configProvider;
        $this->pool = $pool;
        $this->storeManager = $storeManager;
    }

    public function execute(ProductInterface $product, float $requiredQty = 1): bool
    {
        $websiteId = (int) $this->storeManager->getStore($product->getStoreId())->getWebsiteId();
        if (!$this->configProvider->isDiscoverCompositeOptions($websiteId)) {
            // We never know what options customer will select
            return false;
        }

        $typeId = $product->getTypeId();

        return isset($this->pool[$typeId]) ? $this->pool[$typeId]->execute($product) : false;
    }
}
