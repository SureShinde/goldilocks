<?php

namespace Magenest\WebApiLog\Controller\Adminhtml\Logging;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class Log
 *
 * @package Magenest\WebApiLog\Controller\Adminhtml\Logging
 */
class Log extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    public $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    public $layoutFactory;

    /**
     * Log constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory    = $layoutFactory;

        parent::__construct($context);
    }

    /**
     * view action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $content = $this->layoutFactory->create()
            ->createBlock(
                \Magenest\WebApiLog\Block\Adminhtml\ApiLogListing::class
            );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($content->toHtml());
    }
}
