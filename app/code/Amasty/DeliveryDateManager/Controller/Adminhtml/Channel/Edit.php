<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterfaceFactory;
use Amasty\DeliveryDateManager\Controller\Adminhtml\Channel;
use Amasty\DeliveryDateManager\Model\DeliveryChannel\Get as DeliveryChannelGet;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Channel implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::deliverydate_channel';

    /**
     * @var DeliveryChannelGet
     */
    private $deliveryChannelGet;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var DeliveryChannelInterfaceFactory
     */
    private $deliveryChannelFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var null
     */
    private $loadedModel = null;

    public function __construct(
        Context $context,
        DeliveryChannelGet $deliveryChannelGet,
        PageFactory $resultPageFactory,
        DeliveryChannelInterfaceFactory $deliveryChannelFactory,
        Session $session
    ) {
        parent::__construct($context);
        $this->deliveryChannelGet = $deliveryChannelGet;
        $this->resultPageFactory = $resultPageFactory;
        $this->deliveryChannelFactory = $deliveryChannelFactory;
        $this->session = $session;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|void
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$this->isExistData($id)) {
            $this->messageManager->addErrorMessage(__('This configuration no longer exists.'));

            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('channel/*');
        }

        return $this->loadPageData($id);
    }

    /**
     * @param string|null $id
     * @return bool
     * @throws NoSuchEntityException
     */
    private function isExistData(string $id = null): bool
    {
        if ($id) {
            $this->loadedModel = $this->deliveryChannelGet->execute((int)$id);
            if (!$this->loadedModel->getChannelId()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param null $id
     */
    private function loadPageData($id = null)
    {
        // set entered data if was error when we do save
        $data = $this->session->getPageData(true);
        if (!empty($data)) {
            $this->loadedModel = $this->deliveryChannelFactory->create();
            $this->loadedModel->addData($data);
        }

        $this->initAction();

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->addBreadcrumb(
            $id ? __('Edit Delivery Channel Configuration') : __('New Delivery Channel Configuration'),
            $id ? __('Edit Delivery Channel Configuration') : __('New Delivery Channel Configuration')
        );

        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $this->loadedModel ? $this->loadedModel->getName() : __('New Delivery Channel Configuration')
        );

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
