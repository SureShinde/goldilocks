<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Catalog\Model\ProductRepository;

use Amasty\Preorder\Model\Product\Processor;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;

class AddInfoToProduct
{
    /**
     * @var Processor
     */
    private $processor;

    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    public function afterGetById(ProductRepository $subject, ProductInterface $product): ProductInterface
    {
        $this->processor->execute([$product]);
        return $product;
    }
}
