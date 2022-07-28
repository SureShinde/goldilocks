<?php

namespace Magenest\AdvancedLogin\Plugin\Model;

class AuthenticateCustomerBySecret
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

    public function beforeExecute(\Magento\LoginAsCustomer\Model\AuthenticateCustomerBySecret $subject, $secret)
    {
        $this->registry->register('login_as_customer', true);
        return [$secret];
    }
}
