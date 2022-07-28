<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\CatalogWidget\Block\Product\ProductsList;

use Amasty\Preorder\Plugin\ProductList\GetPreorderHtmlForList;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\Framework\Exception\LocalizedException;

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

    /**
     * @param ProductsList $subject
     * @param string $resultHtml
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml(ProductsList $subject, string $resultHtml): string
    {
        if (!$subject->getProductCollection()) {
            $subject->setProductCollection($subject->createCollection());
        }

        if ($collection = $subject->getProductCollection()) {
            $resultHtml .= $this->getPreorderHtmlForList->get($subject, $collection->getItems());
        }

        return $resultHtml;
    }
}
