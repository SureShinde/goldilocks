<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Quote\Item;

use Amasty\Preorder\Model\Product\Detect\IsProductPreorderInterface;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option;

class IsPreorder
{
    /**
     * @var IsProductPreorderInterface
     */
    private $isSimplePreorder;

    public function __construct(IsProductPreorderInterface $isSimplePreorder)
    {
        $this->isSimplePreorder = $isSimplePreorder;
    }

    public function execute(QuoteItem $quoteItem, ?float $qty = null): bool
    {
        $product = $quoteItem->getProduct();
        $qty = $qty ?? (float) $quoteItem->getQty();

        if ($product->isComposite()) {
            $productTypeInstance = $product->getTypeInstance();

            if ($productTypeInstance instanceof ConfigurableType) {
                /** @var Option $option */
                $option = $quoteItem->getOptionByCode('simple_product');
                $simpleProduct = $option->getProduct();

                if (!$simpleProduct instanceof Product) {
                    return false;
                }

                return $this->isSimplePreorder->execute($simpleProduct, $qty);
            }

            if ($productTypeInstance instanceof BundleType) {
                $isPreorder = false;

                foreach ($quoteItem->getChildren() as $childItem) {
                    if ($this->execute($childItem, $qty)) {
                        $isPreorder = true;
                        break;
                    }
                }

                return $isPreorder;
            }
        } else {
            return $this->isSimplePreorder->execute($product, $qty);
        }

        return false;
    }
}
