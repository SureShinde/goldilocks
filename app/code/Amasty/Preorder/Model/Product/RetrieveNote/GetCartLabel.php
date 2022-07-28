<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\RetrieveNote;

use Amasty\Preorder\Model\Product\Constants;
use Magento\Catalog\Api\Data\ProductInterface;

class GetCartLabel implements GetAttributeValueInterface
{
    /**
     * @var RetrieveAttributeValue
     */
    private $retrieveAttributeValue;

    public function __construct(RetrieveAttributeValue $retrieveAttributeValue)
    {
        $this->retrieveAttributeValue = $retrieveAttributeValue;
    }

    public function execute(ProductInterface $product): string
    {
        return $this->retrieveAttributeValue->execute($product, Constants::CART_LABEL);
    }
}
