<?php

namespace Magenest\Pandago\Controller\Adminhtml\Authentication;

use Magenest\Pandago\Model\Api;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Controller GetAuthorizationCode
 */
class GetAccessToken extends Action
{
    /**
     * @var Api
     */
    private Api $api;
    private StoreManagerInterface $storeManager;

    /**
     * GetAccessToken constructor.
     *
     * @param Api $api
     * @param Action\Context $context
     */
    public function __construct(
        Api $api,
        Action\Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->api = $api;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $result = $this->api->getToken($storeId);
            if ($result) {
                $result = [
                    'success' => true,
                    'message' => __('Connect successful!')
                ];
            } else {
                $result = [
                    'success' => false,
                    'message' => __('Something went wrong while connect to the Pandago')
                ];
            }
        } catch (LocalizedException | \Zend_Http_Client_Exception $exception) {
            $result = [
                'success' => false,
                'message' => $exception->getMessage()
            ];
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultRedirect->setData($result);

        return $resultRedirect;
    }
}
