<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\PrivateSale\Plugin\Block\Catalog\Product;

use Magento\Framework\Config\View as ConfigView;
use Magento\Framework\View\LayoutFactory;
use Plumrocket\PrivateSale\Block\Event\Product;
use Plumrocket\PrivateSale\Helper\Config;

class AbstractProduct
{
    /**
     * Module variables
     * @var array
     */
    protected $vars = [];

    /**
     * Block factory
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Config
     */
    private $config;

    /**
     * @param \Magento\Framework\View\LayoutFactory $layout
     * @param ConfigView                            $configView
     * @param \Plumrocket\PrivateSale\Helper\Config $config
     */
    public function __construct(
        LayoutFactory $layout,
        ConfigView $configView,
        Config $config
    ) {
        $this->layout = $layout;
        $this->vars = $configView->getVars('Plumrocket_PrivateSale');
        $this->config = $config;
    }

    /**
     * Around of product details html
     * @param  Magento\Catalog\Block\Product\ListProduct\Interceptor $provider
     * @param  string $result
     * @param  \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function aroundGetProductDetailsHtml($provider, $result, $product)
    {
        $result = $result($product);
        if ($this->vars['display_on_product_list'] == 'true' && $this->config->isModuleEnabled()) {
            /** @var \Plumrocket\PrivateSale\Block\Event\Product $block */
            $block = $this->layout->create()->createBlock(Product::class);
            $block->setProduct($product)
                ->setTemplate('Plumrocket_PrivateSale::event/item.phtml');
            $result .= $block->toHtml();
        }

        return $result;
    }
}
