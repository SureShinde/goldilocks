<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Plugin\GroupedProduct\Model\Product\Type\Grouped;

use Amasty\PreOrderRelease\Model\ConfigProvider;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class AddReleaseDateAttribute
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function afterGetAssociatedProductCollection(Grouped $subject, Collection $collection): Collection
    {
        $collection->addAttributeToSelect([$this->configProvider->getReleaseDateAttribute()]);

        return $collection;
    }
}
