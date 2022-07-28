<?php

namespace Magenest\AbandonedCart\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;

class EmailChart extends \Magento\Backend\App\Action
{
    /** @var  \Psr\Log\LoggerInterface $_logger */
    protected $_logger;

    /** @var  \Magento\Framework\Registry $_coreRegistry */
    protected $_coreRegistry;

    /** @var \Magento\Framework\View\Result\PageFactory $_resultPageFactory */
    protected $_resultPageFactory;

    /**
     * EmailChart constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Action\Context $context
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_logger            = $logger;
        $this->_coreRegistry      = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $pageResult */
        $pageResult = $this->_resultPageFactory->create();
        $pageResult->setActiveMenu('Magenest_AbandonedCart::abandonedcart');
        $pageResult->addBreadcrumb(__('Email Campaign Dashboard'), __('Email Campaign Dashboard'));
        $pageResult->getConfig()->getTitle()->prepend(__('Email Campaign Dashboard'));
        return $pageResult;
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_AbandonedCart::abcreport');
    }
}
