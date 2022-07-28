<?php

namespace Magenest\AdvancedLogin\Plugin\Customer\Model;

class Session
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Customer\Model\Session $subject
     * @param callable $process
     * @param $customer
     * @return \Magento\Customer\Model\Session
     */
    public function aroundSetCustomerDataAsLoggedIn(\Magento\Customer\Model\Session $subject, callable $process, $customer)
    {
        if ($this->registry->registry('login_as_customer')) {
            return $process($customer);
        }
        if (!$customer->getCustomAttribute('is_otp_confirm') || ($customer->getCustomAttribute('is_otp_confirm') &&
                !$customer->getCustomAttribute('is_otp_confirm')->getValue())) {
            $subject->setData('customer_email', $customer->getEmail());
            $subject->setData('telephone', $customer->getCustomAttribute('telephone')->getValue());
            $subject->setData('flag_success', true);
            $subject->setData('is_otp_confirm', false);
            $subject->setData('before_request_params', false);
            return $subject;
        } else {
            $subject->setData('is_otp_confirm', true);
            return $process($customer);
        }
    }

    /**
     * @param \Magento\Customer\Model\Session $subject
     * @param callable $process
     * @param $customer
     * @return \Magento\Customer\Model\Session
     */
    public function aroundSetCustomerAsLoggedIn(\Magento\Customer\Model\Session $subject, callable $process, $customer)
    {
        if ($this->registry->registry('login_as_customer')) {
            return $process($customer);
        }
        if (!$customer->getData('is_otp_confirm')) {
            $subject->setData('customer_email', $customer->getEmail());
            $subject->setData('is_otp_confirm', false);
            $subject->setData('flag_success', true);
            return $subject;
        } else {
            $subject->setData('is_otp_confirm', true);
            return $process($customer);
        }
    }
}
