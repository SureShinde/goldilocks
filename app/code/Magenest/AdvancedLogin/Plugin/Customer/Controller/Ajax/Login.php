<?php

namespace Magenest\AdvancedLogin\Plugin\Customer\Controller\Ajax;

use Acommerce\SmsIntegration\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\UrlInterface;

class Login
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var UrlInterface
     */
    private $urlModel;
    /**
     * @var Data
     */
    private $smsHelper;

    /**
     * @param Session $session
     * @param JsonFactory $jsonFactory
     * @param UrlInterface $urlModel
     * @param Data $smsHelper
     */
    public function __construct(
        Session      $session,
        JsonFactory  $jsonFactory,
        UrlInterface $urlModel,
        Data         $smsHelper
    ) {
        $this->session = $session;
        $this->jsonFactory = $jsonFactory;
        $this->urlModel = $urlModel;
        $this->smsHelper = $smsHelper;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(\Magento\Customer\Controller\Ajax\Login $subject, $result)
    {
        if (!$this->session->getData('is_otp_confirm') && $this->session->getData('flag_success')) {
            $telephone = $this->session->getData('telephone');
            $this->smsHelper->sendSmsOTP($telephone);
            $response['redirectUrl'] = $this->urlModel->getUrl('advancedlogin/otp/index');
            $this->session->setData('is_social_login', false);
            $resultJson = $this->jsonFactory->create();
            return $resultJson->setData($response);
        }
        return $result;
    }
}
