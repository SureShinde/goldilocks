<?php

declare(strict_types=1);

namespace Amasty\Preorder\ViewModel\Product\ProductList;

use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Map\Product\Type\Configurable as ConfigurableMapper;
use Amasty\Preorder\Model\Product\GetPreorderInformation;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ProductTypeConfigurable;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Preorder implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var ConfigurableMapper
     */
    private $configurableMapper;

    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    public function __construct(
        ConfigProvider $configProvider,
        JsonSerializer $serializer,
        ConfigurableMapper $configurableMapper,
        GetPreorderInformation $getPreorderInformation
    ) {
        $this->configProvider = $configProvider;
        $this->serializer = $serializer;
        $this->configurableMapper = $configurableMapper;
        $this->getPreorderInformation = $getPreorderInformation;
    }

    /**
     * @param ProductInterface[] $items
     * @return string
     */
    public function generateJsonConfig(array $items): string
    {
        $config = [];

        foreach ($items as $product) {
            $productPreorderInformation = $this->getPreorderInformation->execute($product);
            if ($productPreorderInformation->isPreorder()) {
                $config[$product->getId()] = [
                    'cart_label' => $productPreorderInformation->getCartLabel()
                ];

                if ($this->configProvider->isPreOrderNoteShow()) {
                    $config[$product->getId()]['note'] = $productPreorderInformation->getNote();
                }
            }

            if ($product->getTypeId() == ProductTypeConfigurable::TYPE_CODE) {
                $config[$product->getId()]['configurable'] = $this->generateConfigurableConfig($product);
            }
        }

        return $this->serializer->serialize($config);
    }

    private function generateConfigurableConfig($product): array
    {
        $this->configurableMapper->setProduct($product);
        $productId = $product->getId();

        return [
            'entity' => $productId,
            'swatchOpt' => sprintf('.swatch-opt-%d', $productId),
            'addToCartLabel' => $this->getPreorderInformation->execute($product)->getCartLabel(),
            'map' => $this->configurableMapper->getProductPreorderMap(),
            'currentAttributes' => [$productId => $this->configurableMapper->getConfigurableAttributes()],
            'isAllProductsPreorder' => (int) $this->configurableMapper->getIsAllProductsPreorder()
        ];
    }
}
