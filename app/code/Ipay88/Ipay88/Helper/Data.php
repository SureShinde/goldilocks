<?php
/**
 * @author iPay88 Inc. <support@ipay88.com.my>
 * @package iPay88\ThirdpartyIntegration\Magento
 * @Description: Using for Magento 2.x and Philippines Only.
 */
namespace Ipay88\Ipay88\Helper;



class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $config;

    const Magento_Config_Model_Config_Source_Yes = 1;
    const Magento_Config_Model_Config_Source_No  = 0;
    private \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->getInitConfig();
        parent::__construct($context);
    }

    public function getInitConfig()
    {
        if (!class_exists('Ipay88_Config')) {
            $config = \Magento\Framework\App\Filesystem\DirectoryList::getDefaultConfig();
            require_once(BP . '/' . $config['lib_internal']['path'] . "/ipay88-php/Include.php");
        }
        $storeId = $this->storeManager->getStore()->getId();
        \Ipay88_Config::getInstance()->set([
            'merchant_code' => $this->config->getValue('payment/ipay88/ipay88_merchant_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
            'merchant_key' => $this->config->getValue('payment/ipay88/ipay88_merchant_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)
        ]);
    }

    public function getConfig($name)
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->config->getValue("payment/ipay88/{$name}", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getRedirectUrl() {
        $storeId = $this->storeManager->getStore()->getId();
        $isTest = $this->config->getValue('payment/ipay88/test_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);

        if(!$isTest == self::Magento_Config_Model_Config_Source_Yes) {
            return \Ipay88_Philippines::URL_PAYMENT_PRODUCTION;
        } else {
            return \Ipay88_Philippines::URL_PAYMENT_SANDBOX;
        }
    }


    public function prepareHostedBankConfig() {
        // Payment methods, please view technical spec for latest update.
        $storeId = $this->storeManager->getStore()->getId();
        return array(
            \Ipay88_Philippines::PAYMENT_METHOD_CREDIT_CONFIG_KEY    => $this->config->getValue('payment/ipay88/ipay88_credit_card_bank_config',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
            \Ipay88_Philippines::PAYMENT_METHOD_BANCNET_CONFIG_KEY   => $this->config->getValue('payment/ipay88/ipay88_bancnet_bank_config',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
        );
    }



    public function getWiget() {
        $bankConfigurations = $this->prepareHostedBankConfig();
        $imagePath = $this->getImageBankPath();
        $html =
            '<div class="payment_method_ipay88">
                <fieldset>
                    <legend>PLease choose bank to pay:</legend>
                    <ul>';
        if(is_array($bankConfigurations) && count($bankConfigurations)) {
            $i = 1;
            foreach ($bankConfigurations as $key => $config) {
                if($config) {
                    $philippines = new \Ipay88_Philippines();
                    $bank = $philippines->getPaymentMethodInfoByKey($key);

                    if(is_array($bank) && count($bank)) {
                        $paymentId = isset($bank['paymentId']) ? $bank['paymentId'] : '';
                        $name = isset($bank['name']) ? $bank['name'] : '';

                        $logo = $imagePath . DIRECTORY_SEPARATOR . (isset($bank['logo']) ? $bank['logo'] : '');

                        $html .=
                            '<li><div class="bank-name"> ' . $name .'</div>
                                <img payment_id="'.$paymentId.'" class="ipay88_bank_payment_method" src="'.$logo.'" alt="" class="ipay88_bank_logo" style="width:100px; height:33px">';
                        $html .=
                            '</li>';
                    }


                }
            }
        }
        $html .=
            '</ul>
                </fieldset>
            </div>';

        return $html;
    }

    public function getImageBankPath() {
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');
        /** @var \Magento\Store\Api\Data\StoreInterface|\Magento\Store\Model\Store $currentStore */
        $currentStore = $storeManager->getStore();

        $imageUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'ipay88/images/philippines/banks';

        return $imageUrl;
    }
}

?>
