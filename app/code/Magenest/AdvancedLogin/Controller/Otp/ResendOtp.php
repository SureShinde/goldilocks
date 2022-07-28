<?php

namespace Magenest\AdvancedLogin\Controller\Otp;

use Magenest\AdvancedLogin\Controller\OtpAbstract;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;

class ResendOtp extends OtpAbstract implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function execute()
    {
        $customerEmail = $this->customerSession->getData('customer_email');
        $currentStore = $this->storeManager->getStore();
        $customer = $this->customerFactory->create()->setStore($currentStore)->loadByEmail($customerEmail);
        if ($this->smsHelper->validateExpireOTP(true)) {
            $response['message'] = __('Resend OTP successful !');
            $this->smsHelper->sendSmsOTP($customer->getData('telephone'));
        } else {
            $remaining = $this->smsHelper->getOtpResendRemaining();
            $msgBefore = __('Expires in <span id="resend-remaining">%1</span> seconds', $remaining);
            $msgAfter = __('You can resend the otp code');
            $response['message'] = $msgBefore . '<script>var timeLeft = ' . $remaining . ';
var downloadTimer = setInterval(function(){
  if(timeLeft <= 0){
    clearInterval(downloadTimer);
    document.getElementById("resend-remaining").parentElement.innerHTML = "'.$msgAfter.'"
  } else {
    if(document.getElementById("resend-remaining") === null){
         clearInterval(downloadTimer);
    }else{
    document.getElementById("resend-remaining").innerHTML = timeLeft;
    }
  }
  timeLeft -= 1;
}, 1000);</script>';
        }
        $resultJson = $this->jsonFactory->create();
        return $resultJson->setData($response);
    }
}
