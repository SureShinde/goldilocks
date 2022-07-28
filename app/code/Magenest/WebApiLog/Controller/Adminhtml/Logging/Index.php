<?php

namespace Magenest\WebApiLog\Controller\Adminhtml\Logging;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @package Magenest\WebApiLog\Controller\Adminhtml\Logging
 */
class Index extends Action
{
    /**
     * @var string
     */
    const ADMIN_RESOURCE = 'Magenest_WebApiLog::logging';

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_WebApiLog::logging');
        $resultPage->addBreadcrumb(__('Magenest'), __('Api Logging'));
        $resultPage->getConfig()->getTitle()->prepend(__('Api Logging'));

        return $resultPage;
    }
}
