<?php

namespace Magenest\AdvancedLogin\Plugin\Customer\Model;

use Magenest\AdvancedLogin\Helper\Login;
use Magenest\AdvancedLogin\Model\ConfigProvider;

class AccountManagement
{
    /**
     * @var \Magenest\AdvancedLogin\Helper\Login
     */
    protected $loginHelper;
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * AccountManagement constructor.
     * @param Login $loginHelper
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        \Magenest\AdvancedLogin\Helper\Login $loginHelper,
        ConfigProvider $configProvider
    ) {
        $this->loginHelper = $loginHelper;
        $this->configProvider = $configProvider;
    }

    /**
     * @param \Magento\Customer\Model\AccountManagement $subject
     * @param $username
     * @param $password
     * @return array
     */
    public function beforeAuthenticate(\Magento\Customer\Model\AccountManagement $subject, $username, $password)
    {
        if ($this->checkUsernameType($username) === 'telephone' && $this->configProvider->isTelephoneLoginEnable()) {
            // use mobile number to login
            if ($customerEmail = $this->loginHelper->getCustomerByMobile($username)->getEmail()) {
                $username = $customerEmail;
            }
        }
        return [$username, $password];
    }

    /**
     * @param $username
     * @return string
     */
    protected function checkUsernameType($username)
    {
        if (preg_match(Login::REGEX_EMAIL, $username)) {
            return "email";
        }
        if (preg_match(Login::REGEX_MOBILE_NUMBER, $username)) {
            return "telephone";
        }
        return "invalid";
    }
}
