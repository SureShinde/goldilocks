<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml\Logcontent;

class Index extends \Magenest\AbandonedCart\Controller\Adminhtml\Logcontent
{

    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_AbandonedCart::abandonedcart');
        $resultPage->addBreadcrumb(__('Notification Log'), __('Notification Log'));
        $resultPage->getConfig()->getTitle()->prepend(__('Notification Log'));
        return $resultPage;
    }
}
