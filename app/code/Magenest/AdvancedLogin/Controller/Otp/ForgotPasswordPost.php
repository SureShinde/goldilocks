<?php

namespace Magenest\AdvancedLogin\Controller\Otp;

use Magenest\AdvancedLogin\Controller\OtpAbstract;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\View\Result\Page;

class ForgotPasswordPost extends OtpAbstract implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface|Page
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws InputMismatchException
     */
    public function execute()
    {
        $telephone = $this->getRequest()->getParam('telephone');
        $data['otp_code'] = $this->getRequest()->getParam('otp_code');
        $data['telephone'] = $this->getRequest()->getParam('telephone');
        $validateOtp = $this->smsHelper->validateOTP($data);
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($validateOtp['status']) {
            $customerId = $this->phoneHelper->getCustomerIdByPhoneNumber($telephone);
            $customer = $this->customerRepository->getById($customerId);
            $newPasswordToken = $this->mathRandom->getUniqueHash();
            $this->account->changeResetPasswordLinkToken($customer, $newPasswordToken);
            $token = $this->customerRegistry->retrieveSecureData($customerId)->getData('rp_token');
            $url = $this->urlModel->getUrl(
                'customer/account/createPassword',
                [
                    'id' => $customerId,
                    'token' => $token,
                    '_secure' => true
                ]
            );
        } else {
            $this->messageManager->addErrorMessage($validateOtp['message']);
            $url = $this->urlModel->getUrl('customer/account/forgotpassword#phone', ['_secure' => true]);
        }
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }
}
