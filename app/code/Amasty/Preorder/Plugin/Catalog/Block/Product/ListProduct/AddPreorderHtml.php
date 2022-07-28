<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Catalog\Block\Product\ListProduct;

use Amasty\Preorder\Plugin\ProductList\GetPreorderHtmlForList;
use Magento\Catalog\Block\Product\ListProduct;

class AddPreorderHtml
{
    /**
     * @var GetPreorderHtmlForList
     */
    private $getPreorderHtmlForList;

    public function __construct(GetPreorderHtmlForList $getPreorderHtmlForList)
    {
        $this->getPreorderHtmlForList = $getPreorderHtmlForList;
    }

    public function afterToHtml(ListProduct $subject, string $resultHtml): string
    {
        if ($collection = $subject->getLoadedProductCollection()) {
            $resultHtml .= $this->getPreorderHtmlForList->get($subject, $collection->getItems());
        }

        return $resultHtml;
    }
}
