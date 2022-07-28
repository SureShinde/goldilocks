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
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Promo\Banner;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Plumrocket\PrivateSale\Helper\Config;

class AbstractBanner extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var string[]
     */
    protected $denyForPages = [
        'checkout_index_index'
    ];

    /**
     * AbstractBanner constructor.
     * @param Config $config
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Config $config,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $config;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->config->isModuleEnabled() && $this->canShow()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return bool
     */
    private function canShow()
    {
        $fullActionName = $this->getRequest()->getFullActionName();

        return ! in_array($fullActionName, $this->denyForPages, true);
    }
}
