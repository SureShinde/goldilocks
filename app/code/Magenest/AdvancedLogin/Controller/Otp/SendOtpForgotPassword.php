<?php

namespace Magenest\AdvancedLogin\Controller\Otp;

use Magenest\AdvancedLogin\Controller\OtpAbstract;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Page;

class SendOtpForgotPassword extends OtpAbstract implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface|Page
     * @throws LocalizedException
     */
    public function execute()
    {
        $telephone = $this->getRequest()->getParam('telephone');
        if (!$telephone) {
            $response['message'] = __("Please enter your phone number");
        } elseif (!$this->phoneHelper->checkPhoneExist($telephone)) {
            $response['message'] = __("No customer has registered with this phone number");
        } else {
            if ($this->smsHelper->validateExpireOTP(true)) {
                $this->smsHelper->sendSmsOTP($telephone);
                $remaining = $this->smsHelper->getOtpResendRemaining();
                $response['remaining'] = $remaining;
                $response['message'] = __('Send OTP successful ! If you do not receive the code, please wait <span id="resend-remaining">%1</span> seconds then resend', $remaining);
            } else {
                $remaining = $this->smsHelper->getOtpResendRemaining();
                $response['remaining'] = $remaining;
                $remaining = $this->smsHelper->getOtpResendRemaining();
                $response['message'] = __('Please wait <span id="resend-remaining">%1</span> seconds then try again.', $remaining);
            }
        }
        $resultJson = $this->jsonFactory->create();
        return $resultJson->setData($response);
    }
}
