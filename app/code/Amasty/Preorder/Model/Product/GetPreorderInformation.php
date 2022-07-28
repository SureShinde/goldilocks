<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product;

use Amasty\Preorder\Api\Data\ProductInformationInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class GetPreorderInformation
{
    /**
     * @var Processor
     */
    private $processor;

    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    public function execute(ProductInterface $product): ProductInformationInterface
    {
        if ($product->getExtensionAttributes()->getPreorderInfo() === null) {
            $this->processor->execute([$product]);
        }

        return $product->getExtensionAttributes()->getPreorderInfo();
    }
}
