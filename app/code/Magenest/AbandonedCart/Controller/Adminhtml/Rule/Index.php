<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml\Rule;

class Index extends \Magenest\AbandonedCart\Controller\Adminhtml\Rule
{

    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $pageResult */
        $pageResult = $this->_resultPageFactory->create();
        $pageResult->setActiveMenu('Magenest_AbandonedCart::abandonedcart');
        $pageResult->addBreadcrumb(__('Manage Rule'), __('Manage Rule'));
        $pageResult->getConfig()->getTitle()->prepend(__('Manage Rule'));
        return $pageResult;
    }
}
