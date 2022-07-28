<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Catalog\Block\Product\AbstractProduct;

use Amasty\Preorder\Model\ConfigProvider;
use Magento\Catalog\Block\Product\AbstractProduct as NativeAbstractProduct;

/**
 * Class ChangeStockInfo
 *
 * Change stock info for preorder note on server.
 */
class ChangeStockInfo
{
    /**
     * @var array
     */
    private $applicableBlocks = [
        'product.info.configurable',
        'product.info.simple',
        'product.info.bundle',
        'product.info.virtual',
        'product.info.downloadable',
        'product.info.grouped.stock',
        'product.info.type.giftcard'
    ];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    public function afterToHtml(NativeAbstractProduct $subject, string $html): string
    {
        if (in_array($subject->getNameInLayout(), $this->applicableBlocks)
            && $this->configProvider->isEnabled()
            && $subject->getProduct()->getExtensionAttributes()->getPreorderInfo()->isPreorder()
        ) {
            $preorderNote = $subject->getProduct()->getExtensionAttributes()->getPreorderInfo()->getNote();
            if ($preorderNote) {
                $html = sprintf('<div class="stock available"><span>%s</span></div>', $preorderNote);
            }
        }

        return $html;
    }
}
