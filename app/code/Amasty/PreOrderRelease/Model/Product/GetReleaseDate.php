<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\Product;

use Amasty\PreOrderRelease\Model\ConfigProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

class GetReleaseDate
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
     * @param ProductInterface|Product $product
     * @return string|null
     */
    public function execute(ProductInterface $product): ?string
    {
        return $product->getData($this->configProvider->getReleaseDateAttribute());
    }
}
