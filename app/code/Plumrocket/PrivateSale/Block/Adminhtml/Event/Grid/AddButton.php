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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Adminhtml\Event\Grid;

use Magento\Framework\UrlInterface;
use Magento\Backend\Block\Widget\Button\SplitButton;
use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class AddButton implements ButtonProviderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * AddButton constructor.
     *
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        return [
            'label' => __('Add New Event'),
            'class' => 'retry primary',
            'class_name' => SplitButton::class,
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'options' => [
                [
                    'label' => __('Category Event'),
                    'onclick' => "setLocation('"
                        . $this->urlBuilder->getUrl(
                            'prprivatesale/event/edit',
                            ['type' => EventType::CATEGORY]
                        ) . "')",
                ],
                [
                    'label' => __('Single Product Event'),
                    'onclick' => "setLocation('"
                        . $this->urlBuilder->getUrl('prprivatesale/event/edit', ['type' => EventType::PRODUCT])
                        . "')",
                    'default' => true
                ]
            ]
        ];
    }
}
