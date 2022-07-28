<?php

namespace Magenest\AdvancedLogin\Block\Form;

use Magenest\AdvancedLogin\Model\ConfigProvider;

class Login extends \Magento\Customer\Block\Form\Login
{
    /** @var ConfigProvider  */
    protected $configProvider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        parent::__construct($context, $customerSession, $customerUrl, $data);
    }

    /**
     * get validate type based on login configuration
     * @return string
     */
    public function getValidationType()
    {
        if ($this->configProvider->isTelephoneLoginEnable()) {
            return 'validate-email-telephone';
        }
        return 'validate-email';
    }

    /**
     * @return string
     */
    public function getTitleInput()
    {
        if ($this->configProvider->isTelephoneLoginEnable()) {
            return 'Email/Telephone';
        }
        return 'Email';
    }

    /**
     * get input type based on login configuration
     * @return string
     */
    public function getInputType()
    {
        if ($this->configProvider->isTelephoneLoginEnable()) {
            return 'text';
        }
        return 'email';
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        if ($this->configProvider->isTelephoneLoginEnable()) {
            return 'email address or telephone.';
        }
        return 'email address.';
    }
}
