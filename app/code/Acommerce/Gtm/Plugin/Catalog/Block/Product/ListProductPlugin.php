<?php

namespace Acommerce\Gtm\Plugin\Catalog\Block\Product;

use Magento\Catalog\Block\Product\ListProduct;

class ListProductPlugin
{
     /**
     * @param \Acommerce\Gtm\Helper\Data $helper
     */
    public function __construct(
        \Acommerce\Gtm\Helper\Data $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ListProduct $subject
     * @return string $result
     */
    public function afterToHtml(
        ListProduct $subject,
        $result
        )
    {
        if (!$this->helper->isEnabled()) {
            return $result;
        }

        $scriptContent = $this->helper->getDataLayerScript();
        $result = $result . PHP_EOL . $scriptContent;
        return $result;
    }
}