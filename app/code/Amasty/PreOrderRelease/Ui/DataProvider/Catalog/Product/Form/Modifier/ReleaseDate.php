<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Ui\DataProvider\Catalog\Product\Form\Modifier;

use Amasty\PreOrderRelease\Model\ConfigProvider;
use Amasty\PreOrderRelease\Setup\Patch\Data\AddReleaseDateAttribute;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

class ReleaseDate extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(LocatorInterface $locator, ConfigProvider $configProvider)
    {
        $this->locator = $locator;
        $this->configProvider = $configProvider;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        if ($this->configProvider->isReleaseDateEnabled()) {
            $model = $this->locator->getProduct();
            if ($modelId = $model->getId()) {
                $data[$modelId][self::DATA_SOURCE_DEFAULT][AddReleaseDateAttribute::ATTRIBUTE_CODE]
                    = $model->getData(AddReleaseDateAttribute::ATTRIBUTE_CODE);
            }
        }

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $releaseDateConfig = &$meta['advanced_inventory_modal']['children']['stock_data']['children']
                              ['amasty_preorder_release_date']['arguments']['data']['config'];

        if (!$this->configProvider->isReleaseDateEnabled()) {
            $releaseDateConfig['visible'] = false;
        } elseif ($this->locator->getProduct()->getTypeId() === Bundle::TYPE_CODE) {
            $releaseDateConfig['visible'] = true;
        } else {
            $releaseDateConfig['imports']['visible'] = '${$.provider}:data.product.stock_data.backorders:101';
            $releaseDateConfig['imports']['__disableTmpl']['visible'] = false;
        }

        return $meta;
    }
}
