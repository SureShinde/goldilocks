<?php

namespace Magenest\AdvancedLogin\Controller\Otp;

use Magenest\AdvancedLogin\Controller\OtpAbstract;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;

class PhonePost extends OtpAbstract implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface|Page
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $telephone = $this->getRequest()->getParam('telephone');
        $customerEmail = $this->customerSession->getData('customer_email');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($telephone && $customerEmail) {
            try {
                $currentStore = $this->storeManager->getStore();
                $customer = $this->customerFactory->create()->setStore($currentStore)->loadByEmail($customerEmail);
                $customer->setData('telephone', $telephone);
                $customer->save();
                $this->smsHelper->sendSmsOTP($telephone);
                $url = $this->urlModel->getUrl('advancedlogin/otp/index', ['_secure' => true]);
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage(__($exception->getMessage()));
                $url = $this->urlModel->getUrl('advancedlogin/otp/phone');
            }
        } else {
            $url = $this->urlModel->getUrl('advancedlogin/otp/phone');
        }
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }
}
