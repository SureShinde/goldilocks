<?php
/**
 * Copyright © 2018 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveAndContinueButton
 * @package Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns\Edit\Button
 */
class SaveAndContinue extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'on_click' => sprintf("location.href = '%s';", $this->getSaveAndContinuedUrl()),
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndContinueEdit'],
                ],
            ],
            'sort_order' => 80,
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getSaveAndContinuedUrl()
    {
        return $this->getUrl('*/*/save');
    }
}
