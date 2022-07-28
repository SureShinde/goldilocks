<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Mostviewed\Block\Widget\Related;

use Amasty\Mostviewed\Block\Widget\Related as RelatedBlock;
use Amasty\Mostviewed\Model\OptionSource\BlockPosition;
use Amasty\Preorder\Plugin\ProductList\GetPreorderHtmlForList;

class AddPreorderHtml
{
    private const IGNORED_POSITIONS = [
        BlockPosition::CART_BEFORE_CROSSSEL,
        BlockPosition::CART_AFTER_CROSSSEL,
        BlockPosition::CART_INTO_CROSSSEL,
        BlockPosition::CART_CONTENT_TOP,
        BlockPosition::CART_CONTENT_BOTTOM
    ];

    /**
     * @var GetPreorderHtmlForList
     */
    private $getPreorderHtmlForList;

    public function __construct(GetPreorderHtmlForList $getPreorderHtmlForList)
    {
        $this->getPreorderHtmlForList = $getPreorderHtmlForList;
    }

    /**
     * Append preorder html for products from mostviewed related block.
     *
     * @see RelatedBlock::fetchView
     *
     * @param RelatedBlock $subject
     * @param string $html
     * @return string
     */
    public function afterFetchView(RelatedBlock $subject, string $html): string
    {
        $productCollection = $subject->getProductCollection();
        if (!$productCollection || in_array($subject->getPosition(), self::IGNORED_POSITIONS, true)) {
            return $html;
        }

        return $html . $this->getPreorderHtmlForList->get($subject, $productCollection->getItems());
    }
}
