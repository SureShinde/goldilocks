<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Quote\Item;

use Amasty\Preorder\Model\Product\RetrieveNote\GetNote as GetProductNote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class GetNote
{
    /**
     * @var GetProductNote
     */
    private $getProductNote;

    public function __construct(GetProductNote $getProductNote)
    {
        $this->getProductNote = $getProductNote;
    }

    public function execute(QuoteItem $quoteItem): string
    {
        $product = $quoteItem->getProduct();

        if ($quoteItem->getProductType() == 'configurable') {
            $option = $quoteItem->getOptionByCode('simple_product');
            $product = $option->getProduct();
        }

        return $this->getProductNote->execute($product);
    }
}
