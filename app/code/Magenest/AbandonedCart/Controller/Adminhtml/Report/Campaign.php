<?php


namespace Magenest\AbandonedCart\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Campaign extends Action
{
    /** @var  \Psr\Log\LoggerInterface $_logger */
    protected $_logger;

    /** @var  Registry $_coreRegistry */
    protected $_coreRegistry;

    /** @var PageFactory $_resultPageFactory */
    protected $_resultPageFactory;

    /**
     * Index constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param Context $context
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        Context $context
    ) {
        $this->_logger = $logger;
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|Page
     */
    public function execute()
    {
        /** @var Page $pageResult */
        $pageResult = $this->_resultPageFactory->create();
        $pageResult->setActiveMenu('Magenest_AbandonedCart::abandonedcart');
        $pageResult->addBreadcrumb(__('A/B Test Campaign Dashboard'), __('A/B Test Campaign Dashboard'));
        $pageResult->getConfig()->getTitle()->prepend(__('A/B Test Campaign Dashboard'));
        return $pageResult;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_AbandonedCart::abcreportcampaign');
    }
}
