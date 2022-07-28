<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Xsearch\Block\Search\Product;

use Amasty\Preorder\Model\Product\GetPreorderInformation;
use Amasty\Xsearch\Block\Search\Product as XsearchProduct;
use Magento\Catalog\Api\Data\ProductInterface;

class ChangeCartLabel
{
    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    public function __construct(GetPreorderInformation $getPreorderInformation)
    {
        $this->getPreorderInformation = $getPreorderInformation;
    }

    public function afterGetCartLabel(
        XsearchProduct $xsearchProductBlock,
        string $result,
        ProductInterface $product
    ): string {
        $preorderInformation = $this->getPreorderInformation->execute($product);
        return $preorderInformation->isPreorder() ? $preorderInformation->getCartLabel() : $result;
    }
}
