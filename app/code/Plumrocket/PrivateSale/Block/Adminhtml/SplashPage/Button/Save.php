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

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Adminhtml\SplashPage\Button;

use Magento\Store\Model\Store;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Save implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->request = $context->getRequest();
    }

    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $storeId = $this->request->getParam('store', Store::DEFAULT_STORE_ID);
        return [
            'label' => __('Save'),
            'class' => 'primary',
            'id_hard' => 'save_button',
            'on_click' => 'return false;',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'prprivatesale_splash_page_form.prprivatesale_splash_page_form',
                                'actionName' => 'save',
                                'params' => [
                                    false,
                                    [
                                        'store' => $storeId,
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

        ];
    }
}
