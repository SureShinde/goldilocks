<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml\ABTestCampaigns;

class AddNew extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */

    public function execute()
    {
        $this->_forward('edit');
    }
}
