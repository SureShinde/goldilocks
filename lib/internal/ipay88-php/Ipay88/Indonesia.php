<?php
/**
 * Ipay88 Inc
 * @author Ipay88 Inc <pvchi@ipay88.com>
 * @package Ipay88\Lib
 */

class Ipay88_Indonesia
{
    const URL_PAYMENT_PRODUCTION = 'https://payment.ipay88.co.id/epayment/entry.asp';
    const URL_PAYMENT_SANDBOX    = 'https://sandbox.ipay88.co.id/epayment/entry.asp';

    const URL_PAYMENT_PRODUCTION_VERSION2 = 'https://payment.ipay88.co.id/epayment/entry_v2.asp';
    const URL_PAYMENT_SANDBOX_VERSION2 = 'https://sandbox.ipay88.co.id/epayment/entry_v2.asp';

    const PAYMENT_METHOD_ENABLE = 1;
    const PAYMENT_METHOD_DISABLE = 2;

    const PAYMENT_METHOD_CREDIT_CARD            = 1;
    const PAYMENT_METHOD_MANDIRI_CLICKPAY       = 4;
    const PAYMENT_METHOD_XL_TUNAI               = 7;
    const PAYMENT_METHOD_VIRTUAL_ACCOUNT        = 9;
    const PAYMENT_METHOD_KARTUKU                = 10;
    const PAYMENT_METHOD_CIMB_CLICKS            = 11;
    const PAYMENT_METHOD_MANDIRI_ECASH          = 13;
    const PAYMENT_METHOD_IB_MUAMALAT            = 14;
    const PAYMENT_METHOD_TCASH                  = 15;
    const PAYMENT_METHOD_INDOSAT_DOMPETKU       = 16;
    const PAYMENT_METHOD_ATM_AUTOMATIC          = 17;
    const PAYMENT_METHOD_FLASHIZ                = 19;
    const PAYMENT_METHOD_PAY4ME                 = 22;
    const PAYMENT_METHOD_DANAMON_ONLONE         = 23;
    const PAYMENT_METHOD_PERMATA_ATM            = 31;
    const PAYMENT_METHOD_PAYPAL                 = 6;

    const PAYMENT_METHOD_CREDIT_CONFIG_KEY                 = 'ipay88_credit_card_bank_config';
    const PAYMENT_METHOD_MANDIRI_CLICKPAY_CONFIG_KEY       = 'ipay88_mandiri_clickpay_bank_config';
    const PAYMENT_METHOD_XL_TUNAI_CONFIG_KEY               = 'ipay88_xl_tuinai_bank_config';
    const PAYMENT_METHOD_VIRTUAL_ACCOUNT_CONFIG_KEY        = 'ipay88_virtual_account_bank_config';
    const PAYMENT_METHOD_KARTUKU_CONFIG_KEY                = 'ipay88_karkutu_bank_config';
    const PAYMENT_METHOD_CIMB_CLICKS_CONFIG_KEY            = 'ipay88_cimb_click_bank_config';
    const PAYMENT_METHOD_MANDIRI_ECASH_CONFIG_KEY          = 'ipay88_mandiri_ecash_bank_config';
    const PAYMENT_METHOD_IB_MUAMALAT_CONFIG_KEY            = 'ipay88_ib_muamalat_bank_config';
    const PAYMENT_METHOD_TCASH_CONFIG_KEY                  = 'ipay88_tcash_bank_config';
    const PAYMENT_METHOD_INDOSAT_DOMPETKU_CONFIG_KEY       = 'ipay88_indosat_dompetku_bank_config';
    const PAYMENT_METHOD_ATM_AUTOMATIC_CONFIG_KEY          = 'ipay88_atm_automatic_bank_config';
    const PAYMENT_METHOD_FLASHIZ_CONFIG_KEY                = 'ipay88_flashiz_bank_config';
    const PAYMENT_METHOD_PAY4ME_CONFIG_KEY                 = 'ipay88_pay4me_bank_config';
    const PAYMENT_METHOD_DANAMON_ONLONE_CONFIG_KEY         = 'ipay88_danamon_online_bank_config';
    const PAYMENT_METHOD_PERMATA_ATM_CONFIG_KEY            = 'ipay88_permata_atm_bank_config';
    const PAYMENT_METHOD_PAYPAL_CONFIG_KEY                 = 'ipay88_paypal_bank_config';

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
        self::PAYMENT_METHOD_CREDIT_CONFIG_KEY             => array('paymentId' => self::PAYMENT_METHOD_CREDIT_CARD, 'name' => 'Credit Card', 'logo' => 'credit-card.png'),
        self::PAYMENT_METHOD_MANDIRI_CLICKPAY_CONFIG_KEY   => array('paymentId' => self::PAYMENT_METHOD_MANDIRI_CLICKPAY, 'name' => 'Mandiri clickpay', 'logo' => 'mandiriclickpay.png'),
        self::PAYMENT_METHOD_XL_TUNAI_CONFIG_KEY           => array('paymentId' => self::PAYMENT_METHOD_XL_TUNAI, 'name' => 'XL Tunai', 'logo' => 'xltunai.png'),
        self::PAYMENT_METHOD_VIRTUAL_ACCOUNT_CONFIG_KEY    => array('paymentId' => self::PAYMENT_METHOD_VIRTUAL_ACCOUNT, 'name' => 'BII VA (Virtual Account)', 'logo' => 'bii_va.png'),
        self::PAYMENT_METHOD_KARTUKU_CONFIG_KEY            => array('paymentId' => self::PAYMENT_METHOD_KARTUKU, 'name' => 'Kartuku', 'logo' => 'kartuku.png'),
        self::PAYMENT_METHOD_CIMB_CLICKS_CONFIG_KEY        => array('paymentId' => self::PAYMENT_METHOD_CIMB_CLICKS, 'name' => 'CIMBClicks', 'logo' => 'cimb.png'),
        self::PAYMENT_METHOD_MANDIRI_ECASH_CONFIG_KEY      => array('paymentId' => self::PAYMENT_METHOD_MANDIRI_ECASH, 'name' => 'Mandiri e-Cash', 'logo' => 'mandiriecash.png'),
        self::PAYMENT_METHOD_IB_MUAMALAT_CONFIG_KEY        => array('paymentId' => self::PAYMENT_METHOD_IB_MUAMALAT, 'name' => 'IB Muamalat', 'logo' => 'ibmuamalat.png'),
        self::PAYMENT_METHOD_TCASH_CONFIG_KEY              => array('paymentId' => self::PAYMENT_METHOD_TCASH, 'name' => 'T-Cash', 'logo' => 'tcash.png'),
        self::PAYMENT_METHOD_INDOSAT_DOMPETKU_CONFIG_KEY   => array('paymentId' => self::PAYMENT_METHOD_INDOSAT_DOMPETKU, 'name' => 'Indosat Dompetku', 'logo' => 'dompetku.png'),
        self::PAYMENT_METHOD_ATM_AUTOMATIC_CONFIG_KEY      => array('paymentId' => self::PAYMENT_METHOD_ATM_AUTOMATIC, 'name' => 'Mandiri ATM Automatic', 'logo' => 'mandiri_atm.png'),
        self::PAYMENT_METHOD_FLASHIZ_CONFIG_KEY            => array('paymentId' => self::PAYMENT_METHOD_FLASHIZ, 'name' => 'FLASHiZ', 'logo' => 'flashizpay.png'),
        self::PAYMENT_METHOD_PAY4ME_CONFIG_KEY             => array('paymentId' => self::PAYMENT_METHOD_PAY4ME, 'name' => 'Pay4ME', 'logo' => 'pay4me.png'),
        self::PAYMENT_METHOD_DANAMON_ONLONE_CONFIG_KEY     => array('paymentId' => self::PAYMENT_METHOD_DANAMON_ONLONE, 'name' => 'Danamon Online Banking', 'logo' => 'danamonob.png'),
        self::PAYMENT_METHOD_PERMATA_ATM_CONFIG_KEY        => array('paymentId' => self::PAYMENT_METHOD_PERMATA_ATM, 'name' => 'Permata ATM', 'logo' => 'permata_atm.png'),
        self::PAYMENT_METHOD_PAYPAL_CONFIG_KEY             => array('paymentId' => self::PAYMENT_METHOD_PAYPAL, 'name' => 'PayPal (USD)', 'logo' => 'paypal.png'),
    );

    /**
     * @return array
     */
    public function getPmethod() {
        return $this->pMethod;
    }

    // Payment methods, please view technical spec for latest update.
    public $paymentMethods = array(
        self::PAYMENT_METHOD_CREDIT_CARD        => array('name' => 'Credit Card', 'logo' => 'credit-card.png'),
        self::PAYMENT_METHOD_MANDIRI_CLICKPAY   => array('name' => 'Mandiri clickpay', 'logo' => 'mandiriclickpay.png'),
        self::PAYMENT_METHOD_XL_TUNAI           => array('name' => 'XL Tunai', 'logo' => 'xltunai.png'),
        self::PAYMENT_METHOD_VIRTUAL_ACCOUNT    => array('name' => 'BII VA (Virtual Account)', 'logo' => 'bii_va.png'),
        self::PAYMENT_METHOD_KARTUKU            => array('name' => 'Kartuku', 'logo' => 'kartuku.png'),
        self::PAYMENT_METHOD_CIMB_CLICKS        => array('name' => 'CIMBClicks', 'logo' => 'cimb.png'),
        self::PAYMENT_METHOD_MANDIRI_ECASH      => array('name' => 'Mandiri e-Cash', 'logo' => 'mandiriecash.png'),
        self::PAYMENT_METHOD_IB_MUAMALAT        => array('name' => 'IB Muamalat', 'logo' => 'ibmuamalat.png'),
        self::PAYMENT_METHOD_TCASH              => array('name' => 'T-Cash', 'logo' => 'tcash.png'),
        self::PAYMENT_METHOD_INDOSAT_DOMPETKU   => array('name' => 'Indosat Dompetku', 'logo' => 'dompetku.png'),
        self::PAYMENT_METHOD_ATM_AUTOMATIC      => array('name' => 'Mandiri ATM Automatic', 'logo' => 'mandiri_atm.png'),
        self::PAYMENT_METHOD_FLASHIZ            => array('name' => 'FLASHiZ', 'logo' => 'flashizpay.png'),
        self::PAYMENT_METHOD_PAY4ME             => array('name' => 'Pay4ME', 'logo' => 'pay4me.png'),
        self::PAYMENT_METHOD_DANAMON_ONLONE     => array('name' => 'Danamon Online Banking', 'logo' => 'danamonob.png'),
        self::PAYMENT_METHOD_PERMATA_ATM        => array('name' => 'Permata ATM', 'logo' => 'permata_atm.png'),
        self::PAYMENT_METHOD_PAYPAL             => array('name' => 'PayPal (USD)', 'logo' => 'paypal.png'),
    );

    /**
     * @param $key
     * @return null
     */
    public function getPaymentMethodInfoByKey($key) {
        if(isset($this->pMethod[$key])) {
            return $this->pMethod[$key];
        }
        return null;
    }

    /**
     * @param $id
     * @return null
     */
    public function getPaymentMethodInfoById($id) {
        if(isset($this->pMethod[$id])) {
            return $this->pMethod[$id];
        }
        return null;
    }

    public function __construct($options = null)
    {
    }


    public function getWiget() {
        $html = '';

        $bankEnable = $this->getBankEnabled();

        if(is_array($bankEnable) && count($bankEnable)) {
            foreach ($bankEnable as $bank) {

            }
        }

        return $html;
    }
}