<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Deliverydate;

use Amasty\DeliveryDateManager\Request\Validator\DDEditCustomerValidator;
use Amasty\DeliveryDateManager\Request\Validator\DDEditValidatorInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\DesignLoader;
use Magento\Framework\View\Result\Page;

class Edit implements HttpGetActionInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var DDEditCustomerValidator
     */
    private $editValidator;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DesignLoader
     */
    private $designLoader;

    public function __construct(
        ResultFactory $resultFactory,
        DDEditValidatorInterface $editValidator,
        RequestInterface $request,
        DesignLoader $designLoader
    ) {
        $this->resultFactory = $resultFactory;
        $this->editValidator = $editValidator;
        $this->request = $request;
        $this->designLoader = $designLoader;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->designLoader->load();
        $validatorResult = $this->editValidator->validateRequest($this->request);
        
        if (!$validatorResult->isSuccess()) {
            return $validatorResult->getResult();
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/order/history');
        }

        $title = __('Edit Delivery Date For The Order #%1', $validatorResult->getOrder()->getIncrementId());
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
