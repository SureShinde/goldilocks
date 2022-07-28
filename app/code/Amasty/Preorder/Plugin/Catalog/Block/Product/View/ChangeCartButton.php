<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Catalog\Block\Product\View;

use Amasty\Preorder\Model\ConfigProvider;
use Amasty\Preorder\Model\Utils\StripTags;
use Magento\Catalog\Block\Product\View as ProductView;

class ChangeCartButton
{
    /**
     * @var string
     */
    private $regexp;

    /**
     * @var array
     */
    private $applicableBlocks = [
        'product.info.addtocart.bundle',
        'product.info.addtocart',
        'product.info.addtocart.additional'
    ];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StripTags
     */
    private $stripTags;

    public function __construct(
        ConfigProvider $configProvider,
        StripTags $stripTags
    ) {
        $this->configProvider = $configProvider;
        $this->stripTags = $stripTags;
        $this->_construct();
    }

    protected function _construct()
    {
        // @codingStandardsIgnoreLine
        $this->regexp = '@(<button[^>]*title=")[^"]*("[^>]*id="product-addtocart-button"[^>]*>[^>]*<span>)(.*)(</span>.*</button>)@Us';
    }

    public function afterToHtml(ProductView $subject, string $html): string
    {
        if (in_array($subject->getNameInLayout(), $this->applicableBlocks)
            && $this->configProvider->isPreorderEnabled()
            && $subject->getProduct()->getExtensionAttributes()->getPreorderInfo()->isPreorder()
        ) {
            $label = $subject->getProduct()->getExtensionAttributes()->getPreorderInfo()->getCartLabel();
            $labelText = $this->stripTags->execute($label);
            $html = preg_replace(
                $this->regexp,
                sprintf(
                    '${1}%s${2}%s$4<div class="original-add-to-cart-text" data-text="$3"></div>',
                    $labelText,
                    $label
                ),
                $html
            );
        }

        return $html;
    }
}
