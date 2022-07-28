<?php

namespace Magenest\AdvancedLogin\Plugin\Customer\Controller\CheckLogin;

use Acommerce\SmsIntegration\Helper\Data;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;

class Index
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
     * @var CustomerFactory
     */
    private $customerFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Data
     */
    private $smsHelper;

    /**
     * @param Session $session
     * @param ResultFactory $resultFactory
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param Data $smsHelper
     */
    public function __construct(
        Session $session,
        ResultFactory $resultFactory,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager,
        Data $smsHelper
    ) {
        $this->session = $session;
        $this->resultFactory = $resultFactory;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->smsHelper = $smsHelper;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute($subject, $result)
    {
        $customerEmail = $this->session->getData('customer_email');
        if (!$this->session->getData('is_otp_confirm') && $this->session->getData('flag_success')) {
            $this->session->setData('is_social_login', true);
            $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $currentStore = $this->storeManager->getStore();
            $customer = $this->customerFactory->create()->setStore($currentStore)->loadByEmail($customerEmail);
            $telephone = $customer->getData('telephone');
            if ($telephone) {
                $this->smsHelper->sendSmsOTP($telephone);
                $redirect->setUrl('/advancedlogin/otp/index');
            } else {
                $redirect->setUrl('/advancedlogin/otp/phone');
            }
            return $redirect;
        }
        return $result;
    }
}
