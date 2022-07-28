<?php

namespace Magenest\AdvancedLogin\Controller\Otp;

use Magenest\AdvancedLogin\Controller\OtpAbstract;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Result\Page;

class Confirm extends OtpAbstract implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @return ResponseInterface|Raw|Redirect|ResultInterface|Page
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('otp_code');
        $customerEmail = $this->customerSession->getData('customer_email');
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$customerEmail) {
            $this->messageManager->addErrorMessage(__("The otp code is incorrect, please re-enter it"));
            $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
            $resultRedirect->setUrl($url);
            return $resultRedirect;
        }
        $currentStore = $this->storeManager->getStore();
        $customer = $this->customerFactory->create()->setStore($currentStore)->loadByEmail($customerEmail);
        $data['otp_code'] = $code;
        $data['telephone'] = $customer->getData('telephone');
        $validateOtp = $this->smsHelper->validateOTP($data);
        if ($validateOtp['status']) {
            $customer->setData('is_otp_confirm', true);
            $this->customerSession->setData('is_otp_confirm', true);
            $customer->save();
            $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $this->messageManager->addComplexSuccessMessage(
                    'confirmAccountSuccessMessage',
                    [
                        'url' => $this->customerUrl->getEmailConfirmationUrl($customer->getEmail()),
                    ]
                );
                $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
                $resultRedirect->setUrl($this->_redirect->success($url));
            } else {
                $customerData = $customer->getDataModel();
                $this->customerSession->setCustomerDataAsLoggedIn($customerData);
                $this->messageManager->addMessage($this->getMessageManagerSuccessMessage());
                $requestedRedirect = $this->accountRedirect->getRedirectCookie();
                if (!$this->scopeConfig->getValue('customer/startup/redirect_dashboard') && $requestedRedirect) {
                    $resultRedirect->setUrl($this->_redirect->success($requestedRedirect));
                    $this->accountRedirect->clearRedirectCookie();
                    return $resultRedirect;
                }
                $resultRedirect = $this->accountRedirect->getRedirect();
            }
            $isSocialLogin = $this->customerSession->getData('is_social_login');
            if ($isSocialLogin) {
                $resultRaw = $this->rawFactory->create();
                $html = "<script> window.close();window.opener.location.reload();</script>";
                $resultRaw->setContents($html);
                return $resultRaw;
            } else {
                $url = $this->urlModel->getUrl('customer/account/index', ['_secure' => true]);
                $resultRedirect->setUrl($url);
            }
        } else {
            $this->messageManager->addErrorMessage($validateOtp['message']);
            $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
            $resultRedirect->setUrl($url);
        }
        return $resultRedirect;
    }

    /**
     * Retrieve success message manager message
     *
     * @return MessageInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getMessageManagerSuccessMessage(): MessageInterface
    {
        if ($this->addressHelper->isVatValidationEnabled()) {
            if ($this->addressHelper->getTaxCalculationAddressType() == Address::TYPE_SHIPPING) {
                $identifier = 'customerVatShippingAddressSuccessMessage';
            } else {
                $identifier = 'customerVatBillingAddressSuccessMessage';
            }

            $message = $this->messageManager
                ->createMessage(MessageInterface::TYPE_SUCCESS, $identifier)
                ->setData(
                    [
                        'url' => $this->urlModel->getUrl('customer/address/edit'),
                    ]
                );
        } else {
            $message = $this->messageManager
                ->createMessage(MessageInterface::TYPE_SUCCESS)
                ->setText(
                    __('Thank you for registering with %1.', $this->storeManager->getStore()->getFrontendName())
                );
        }

        return $message;
    }
}
