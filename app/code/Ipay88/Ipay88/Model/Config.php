<?php
/**
 * @author iPay88 Inc. <support@ipay88.com.my>
 * @package iPay88\ThirdpartyIntegration\Magento
 * @Description: Using for Magento 2.x and Philippines Only.
 */

namespace Ipay88\Ipay88\Model;

use Magento\Store\Model\ScopeInterface;

/**
 * Description of Config
 *
 * @author Andy Pieters <andy@pay.nl>
 */
class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfigInterface;
    private \Magento\Store\Model\StoreManagerInterface $storeManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $configInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_scopeConfigInterface = $configInterface;
        $this->storeManager = $storeManager;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMerchantCode()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->_scopeConfigInterface->getValue('payment/ipay88/ipay88_merchant_code', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getMerchantKey()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->_scopeConfigInterface->getValue('payment/ipay88/ipay88_merchant_key', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isTestMode()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->_scopeConfigInterface->getValue('payment/ipay88/testmode', ScopeInterface::SCOPE_STORE, $storeId) == 1;
    }

    /**
     * @param $methodCode
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPaymentOptionId($methodCode)
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->_scopeConfigInterface->getValue('payment/' . $methodCode . '/payment_option_id',ScopeInterface::SCOPE_STORE, $storeId);
    }
}
