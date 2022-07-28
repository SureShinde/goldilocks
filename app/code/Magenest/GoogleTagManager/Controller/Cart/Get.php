<?php

namespace Magenest\GoogleTagManager\Controller\Cart;

class Get extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magenest\GoogleTagManager\Helper\CatalogSession
     */
    private $sessionHelper;

    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magenest\GoogleTagManager\Helper\CatalogSession $sessionHelper,
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->sessionHelper = $sessionHelper;
    }

    public function execute()
    {
        return $this->resultJsonFactory->create()->setData(
            $this->sessionHelper->getProductData()
        );
    }
}
