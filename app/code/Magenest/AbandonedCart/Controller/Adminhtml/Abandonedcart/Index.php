<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml\Abandonedcart;

class Index extends \Magenest\AbandonedCart\Controller\Adminhtml\Abandonedcart
{

    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $pageResult */
        $pageResult = $this->_resultPageFactory->create();
        $pageResult->setActiveMenu('Magenest_AbandonedCart::abandonedcart');
        $pageResult->addBreadcrumb(__('Abandoned Carts'), __('Abandoned Carts'));
        $pageResult->getConfig()->getTitle()->prepend(__('Abandoned Carts'));
        return $pageResult;
    }
}
