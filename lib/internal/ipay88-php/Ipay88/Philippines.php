<?php
/**
 * Ipay88 Inc
 * @author Ipay88 Inc <pvchi@ipay88.com.my>
 * @package Ipay88\Lib
 */

class Ipay88_Philippines
{
    const URL_PAYMENT_PRODUCTION    = 'https://payment.ipay88.com.ph/epayment/entry.asp';
    const URL_PAYMENT_SANDBOX       = 'https://sandbox.ipay88.com.ph/epayment/entry.asp';

    const PAYMENT_METHOD_ENABLE = 1;
    const PAYMENT_METHOD_DISABLE = 2;

    const PAYMENT_METHOD_CREDIT_CARD = 1;
    const PAYMENT_METHOD_BANCNET = 5;

    const PAYMENT_METHOD_CREDIT_CONFIG_KEY = 'ipay88_credit_card_bank_config';
    const PAYMENT_METHOD_BANCNET_CONFIG_KEY = 'ipay88_bancnet_bank_config';

    protected $paymentMethod;

    protected $banks;

    protected $bankKeyConfig;

    protected $paymentId;

    protected $bankName;

    protected $logo;

    protected $imagePath;

    protected $bankEnabled;

    /**
     * @return array
     */
    public function getBankEnabled()
    {
        return $this->bankEnabled;
    }

    /**
     * @param mixed $bankEnabled
     */
    public function setBankEnabled($bankEnabled)
    {
        $this->bankEnabled = $bankEnabled;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param mixed $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return mixed
     */
    public function getBanks()
    {
        return $this->banks;
    }

    /**
     * @param mixed $banks
     */
    public function setBanks($banks)
    {
        $this->banks = $banks;
    }

    /**
     * @return mixed
     */
    public function getBankKeyConfig()
    {
        return $this->bankKeyConfig;
    }

    /**
     * @param mixed $bankKeyConfig
     */
    public function setBankKeyConfig($bankKeyConfig)
    {
        $this->bankKeyConfig = $bankKeyConfig;
    }

    /**
     * @return mixed
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @param mixed $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * @return mixed
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param mixed $bankName
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    }

    /**
     * @return mixed
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param mixed $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * @return mixed
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * @param mixed $imagePath
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    }

    // Payment methods, please view technical spec for latest update.
    protected $pMethod = array(
        self::PAYMENT_METHOD_CREDIT_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_CREDIT_CARD, 'name' => 'Credit Card (PHP)', 'logo' => 'VisaMasterLogo_s.png'),
        self::PAYMENT_METHOD_BANCNET_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_BANCNET_CONFIG_KEY, 'name' => 'BancNet', 'logo' => 'BancNet.png'),

    );

    /**
     * @return array
     */
    public function getPmethod()
    {
        return $this->pMethod;
    }

    // Payment methods, please view technical spec for latest update.
    public $paymentMethods = array(
        self::PAYMENT_METHOD_CREDIT_CARD => array('name' => 'Credit Card (PHP)', 'logo' => 'VisaMasterLogo_s.png'),
        self::PAYMENT_METHOD_BANCNET => array('name' => 'Bancnet', 'logo' => 'BancNet.png'),
    );

    /**
     * @param $key
     * @return null
     */
    public function getPaymentMethodInfoByKey($key)
    {
        if (isset($this->pMethod[$key])) {
            return $this->pMethod[$key];
        }
        return null;
    }

    /**
     * @param $id
     * @return null
     */
    public function getPaymentMethodInfoById($id)
    {
        if (isset($this->pMethod[$id])) {
            return $this->pMethod[$id];
        }
        return null;
    }

    public function __construct($options = null)
    {
    }


    public function getWiget()
    {
        $html = '';

        $bankEnable = $this->getBankEnabled();

        if (is_array($bankEnable) && count($bankEnable)) {
            foreach ($bankEnable as $bank) {

            }
        }

        return $html;
    }
}
