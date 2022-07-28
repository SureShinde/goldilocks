<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\ProductList;

use Magento\Catalog\Block\Product\ProductList\Related;

class ListByGetItemsPlugin
{
    /**
     * @var GetPreorderHtmlForList
     */
    private $getPreorderHtmlForList;

    public function __construct(GetPreorderHtmlForList $getPreorderHtmlForList)
    {
        $this->getPreorderHtmlForList = $getPreorderHtmlForList;
    }

    /**
     * @param Related|mixed $subject
     * @param string $resultHtml
     * @return string
     */
    public function afterToHtml($subject, string $resultHtml): string
    {
        return $resultHtml . $this->getPreorderHtmlForList->get($subject, $subject->getItems()->getItems());
    }
}
