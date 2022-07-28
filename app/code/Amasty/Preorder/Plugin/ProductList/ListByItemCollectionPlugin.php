<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\ProductList;

use Magento\Catalog\Block\Product\ProductList\Upsell;
use Magento\TargetRule\Block\Catalog\Product\ProductList\Related as TargetRelated;
use Magento\TargetRule\Block\Catalog\Product\ProductList\Upsell as TargetUpsell;

class ListByItemCollectionPlugin
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
     * @param Upsell|TargetRelated|TargetUpsell $subject
     * @param string $resultHtml
     * @return string
     */
    public function afterToHtml($subject, string $resultHtml): string
    {
        $itemCollection = $subject->getItemCollection();
        if (!is_array($itemCollection)) {
            $itemCollection = $itemCollection->getItems();
        }

        return $resultHtml . $this->getPreorderHtmlForList->get($subject, $itemCollection);
    }
}
