<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Guest;

use Amasty\DeliveryDateManager\Request\Validator\DDEditValidatorInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\DesignLoader;
use Magento\Framework\View\Result\Page;
use Magento\Sales\Helper\Guest;

class Edit implements HttpGetActionInterface
{
    /**
     * @var Guest
     */
    private $guestHelper;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var DDEditValidatorInterface
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
        Guest $guestHelper,
        UrlInterface $urlBuilder,
        DesignLoader $designLoader
    ) {
        $this->resultFactory = $resultFactory;
        $this->editValidator = $editValidator;
        $this->request = $request;
        $this->guestHelper = $guestHelper;
        $this->urlBuilder = $urlBuilder;
        $this->designLoader = $designLoader;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        $this->designLoader->load();
        $validatorResult = $this->editValidator->validateRequest($this->request);

        if (!$validatorResult->isSuccess()) {
            return $validatorResult->getResult();
        }

        $order = $validatorResult->getOrder();
        
        // trick to add order_id to request
        $params = $this->request->getParams();
        $params['order_id'] = $order->getId();
        $this->request->setParams($params);

        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $title = __('Edit Delivery Date For The Order #%1', $order->getIncrementId());
        $resultPage->getConfig()->getTitle()->prepend($title);

        $this->guestHelper->getBreadcrumbs($resultPage);
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
            'cms_page',
            [
                'label' => __('Order Information'),
                'title' => __('Order Information'),
                'link'  => $this->urlBuilder->getUrl('sales/guest/view')
            ]
        );
        $breadcrumbs->addCrumb(
            'delivery_date',
            ['label' => __('Delivery Date'), 'title' => $title]
        );

        return $resultPage;
    }
}
