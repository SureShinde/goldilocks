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

namespace Plumrocket\PrivateSale\Block\Adminhtml\Event\Edit;

use Magento\Backend\Block\Widget\Context;
use Plumrocket\PrivateSale\Model\EventRepository;

class SaveButton extends AbstractButton
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'options' => $this->getOptions(),
            'class_name' => \Magento\Ui\Component\Control\Container::SPLIT_BUTTON,
            'sort_order' => 90,
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'prprivatesale_event_form.prprivatesale_event_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    [
                                        'store' => $this->getRequestParam('store'),
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

        ];
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    private function getOptions()
    {
        $options = [
            [
                'label' => __('Save and Continue'),
                'id_hard' => 'save_and_continue_button',
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'prprivatesale_event_form.prprivatesale_event_form',
                                    'actionName' => 'save',
                                    'params' => [
                                        false,
                                        [
                                            'store' => $this->getRequestParam('store'),
                                            'back' => 'edit'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ],
        ];

        return $options;
    }
}
