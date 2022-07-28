<?php

declare(strict_types=1);

namespace Amasty\Preorder\ViewModel\Product\View;

use Amasty\Preorder\Model\Map\Product\Type\Configurable as ConfigurableMapper;
use Amasty\Preorder\Model\Product\GetPreorderInformation;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class PreorderConfigurable implements ArgumentInterface
{
    /**
     * @var ConfigurableMapper
     */
    private $configurableMapper;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    public function __construct(
        ConfigurableMapper $configurableMapper,
        Json $serializer,
        GetPreorderInformation $getPreorderInformation
    ) {
        $this->configurableMapper = $configurableMapper;
        $this->serializer = $serializer;
        $this->getPreorderInformation = $getPreorderInformation;
    }

    public function getIsAllProductsPreorder(ProductInterface $product): bool
    {
        $this->configurableMapper->setProduct($product);
        return $this->configurableMapper->getIsAllProductsPreorder();
    }

    public function getMap(ProductInterface $product): string
    {
        $this->configurableMapper->setProduct($product);
        return $this->serializer->serialize($this->configurableMapper->getProductPreorderMap());
    }

    public function getJsonCurrentAttribute(ProductInterface $product): string
    {
        $this->configurableMapper->setProduct($product);
        return $this->serializer->serialize($this->configurableMapper->getConfigurableAttributes());
    }
}
