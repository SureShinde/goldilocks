<?php


namespace Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns;

use Magento\Backend\Block\Template\Context;

class DrawChart extends \Magento\Backend\Block\Template
{
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isDisplay()
    {
        if ($this->getRequest()->getParam('id')) {
            return true;
        }
        return false;
    }
}
