<?php

namespace Magenest\AdvancedLogin\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ConfigProvider
 *
 * @package Magenest\AdvancedLogin\Model
 */
class ConfigProvider
{
    const XML_PATH_TELEPHONE_LOGIN_ENABLE = 'customer/advancedlogin/telephone_login_enable';

    const XML_PATH_EMAIL_SUFFIX = 'customer/advancedlogin/email_suffix';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager)
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve the configured telephone login
     *
     * @return int
     */
    public function isTelephoneLoginEnable()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_TELEPHONE_LOGIN_ENABLE, ScopeInterface::SCOPE_WEBSITES);
    }

    /**
     * Retrieve the configured email suffix
     *
     * @return int
     */
    public function getEmailSuffix()
    {
        return $this->scopeConfig->getValue(Create::XML_PATH_DEFAULT_EMAIL_DOMAIN, ScopeInterface::SCOPE_STORES);
    }
}
