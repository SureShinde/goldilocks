<?php

namespace Magenest\AdvancedLogin\Plugin\Customer\Controller\Account;

use Acommerce\SmsIntegration\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;

class LoginPost
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var ResultFactory
     */
    private $resultFactory;
    /**
     * @var Data
     */
    private $smsHelper;

    /**
     * @param Session $session
     * @param ResultFactory $resultFactory
     * @param Data $smsHelper
     */
    public function __construct(
        Session                                     $session,
        ResultFactory $resultFactory,
        Data $smsHelper
    ) {
        $this->session = $session;
        $this->resultFactory = $resultFactory;
        $this->smsHelper = $smsHelper;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute($subject, $result)
    {
        if (!$this->session->getData('is_otp_confirm') && $this->session->getData('flag_success')) {
            $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $this->session->setData('is_social_login', false);
            $telephone = $this->session->getData('telephone');
            $this->smsHelper->sendSmsOTP($telephone);
            $redirect->setUrl('/advancedlogin/otp/index');
            return $redirect;
        }
        return $result;
    }
}
