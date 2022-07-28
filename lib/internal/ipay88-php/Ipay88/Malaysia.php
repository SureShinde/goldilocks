<?php
/**
 * Ipay88 Inc
 * @author Ipay88 Inc <pvchi@ipay88.com.my>
 * @package Ipay88\Lib
 */

class Ipay88_Malaysia
{
    const URL_PAYMENT_PRODUCTION = 'https://www.mobile88.com/epayment/entry.asp';
    const URL_PAYMENT_SANDBOX  = 'https://sandbox.mobile88.com/epayment/entry.asp';

    const PAYMENT_METHOD_ENABLE = 1;
    const PAYMENT_METHOD_DISABLE = 2;

    const PAYMENT_METHOD_CREDIT_CARD = 2;
    const PAYMENT_METHOD_MAYBANK_2U = 6;
    const PAYMENT_METHOD_ALLIANCE_ONLINE = 8;
    const PAYMENT_METHOD_AMBANK_ONLINE = 10;
    const PAYMENT_METHOD_RHB_ONLINE = 14;

    const PAYMENT_METHOD_HONG_LEONG_ONLINE = 15;
    const PAYMENT_METHOD_FPX = 16;
    const PAYMENT_METHOD_CIMB_CLICKS = 20;
    const PAYMENT_METHOD_WEB_CASH = 22;
    const PAYMENT_METHOD_CELCOM_AIRCASH = 100;

    const PAYMENT_METHOD_BANK_RAKYAT_BANKING = 102;
    const PAYMENT_METHOD_AFFIN_ONLINE = 103;
    const PAYMENT_METHOD_PAY4ME = 122;
    const PAYMENT_METHOD_MYBSN = 124;
    const PAYMENT_METHOD_PAYPAL = 48;

    const PAYMENT_METHOD_CREDIT_CONFIG_KEY = 'ipay88_credit_card_bank_config';
    const PAYMENT_METHOD_MAYBANK_2U_CONFIG_KEY = 'ipay88_maybank2u_bank_config';
    const PAYMENT_METHOD_ALLIANCE_ONLINE_CONFIG_KEY = 'ipay88_alliance_online_bank_config';
    const PAYMENT_METHOD_AMBANK_ONLINE_CONFIG_KEY = 'ipay88_ambank_online_bank_config';
    const PAYMENT_METHOD_RHB_ONLINE_CONFIG_KEY = 'ipay88_rhb_online_bank_config';

    const PAYMENT_METHOD_HONG_LEONG_ONLINE_CONFIG_KEY = 'ipay88_hong_leong_online_config';
    const PAYMENT_METHOD_FPX_CONFIG_KEY = 'ipay88_fpx_config';
    const PAYMENT_METHOD_CIMB_CLICKS_CONFIG_KEY = 'ipay88_cimb_bank_config';
    const PAYMENT_METHOD_WEB_CASH_CONFIG_KEY = 'ipay88_web_cash_bank_config';
    const PAYMENT_METHOD_CELCOM_AIRCASH_CONFIG_KEY = 'ipay88_celcom_aircash_bank_config';

    const PAYMENT_METHOD_RAKYAT_BANKING_CONFIG_KEY = 'ipay88_rakyat_bank_config';
    const PAYMENT_METHOD_AFFIN_ONLINE_CONFIG_KEY = 'ipay88_affin_online_bank_config';
    const PAYMENT_METHOD_PAY4ME_CONFIG_KEY = 'ipay88_pay4me_bank_config';
    const PAYMENT_METHOD_MYBSN_CONFIG_KEY = 'ipay88_mybsn_bank_config';
    const PAYMENT_METHOD_PAYPAL_CONFIG_KEY = 'ipay88_paypal_bank_config';

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
        self::PAYMENT_METHOD_CREDIT_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_CREDIT_CARD, 'name' => 'Credit Card (MYR)', 'logo' => 'VisaMasterLogo_s.png'),
        self::PAYMENT_METHOD_MAYBANK_2U_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_MAYBANK_2U, 'name' => 'Maybank2U', 'logo' => 'Maybank2ULogo_s.png'),
        self::PAYMENT_METHOD_ALLIANCE_ONLINE_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_ALLIANCE_ONLINE, 'name' => 'Alliance Online', 'logo' => 'alliancebank_online.png'),
        self::PAYMENT_METHOD_AMBANK_ONLINE_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_AMBANK_ONLINE, 'name' => 'AmBank Online', 'logo' => 'AmBankLogo_s.png'),
        self::PAYMENT_METHOD_RHB_ONLINE_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_RHB_ONLINE, 'name' => 'RHB Online', 'logo' => 'RHBBank_s.png'),

        self::PAYMENT_METHOD_HONG_LEONG_ONLINE_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_HONG_LEONG_ONLINE, 'name' => 'Hong Leong Online', 'logo' => 'HLB_s.png'),
        self::PAYMENT_METHOD_FPX_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_FPX, 'name' => 'FPX', 'logo' => 'FPXLogo_new.png'),
        self::PAYMENT_METHOD_CIMB_CLICKS_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_CIMB_CLICKS, 'name' => 'CIMB Click', 'logo' => 'CIMBLogo_s.png'),
        self::PAYMENT_METHOD_WEB_CASH_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_WEB_CASH, 'name' => 'Web Cash', 'logo' => 'WebCashLogo_s.png'),
        self::PAYMENT_METHOD_CELCOM_AIRCASH_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_CELCOM_AIRCASH, 'name' => 'Celcom AirCash', 'logo' => 'Celcomaircash_s.png'),

        self::PAYMENT_METHOD_RAKYAT_BANKING_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_BANK_RAKYAT_BANKING, 'name' => 'Bank Rakyat Internet Banking', 'logo' => 'BRLogo_s.png'),
        self::PAYMENT_METHOD_AFFIN_ONLINE_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_AFFIN_ONLINE, 'name' => 'AffinOnline', 'logo' => 'AffinbankLogo_s.png'),
        self::PAYMENT_METHOD_PAY4ME_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_PAY4ME, 'name' => 'Pay4Me (Delay Payment)', 'logo' => 'Pay4Me_s.png'),
        self::PAYMENT_METHOD_MYBSN_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_MYBSN, 'name' => 'myBSN', 'logo' => 'mybsn_s.png'),
        self::PAYMENT_METHOD_PAYPAL_CONFIG_KEY => array('paymentId' => self::PAYMENT_METHOD_PAYPAL, 'name' => 'PayPal (MYR)', 'logo' => 'Paypal_logo4.png'),
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
        self::PAYMENT_METHOD_CREDIT_CARD => array('name' => 'Credit Card (MYR)', 'logo' => 'VisaMasterLogo_s.png'),
        self::PAYMENT_METHOD_MAYBANK_2U => array('name' => 'Maybank2U', 'logo' => 'Maybank2ULogo_s.png'),
        self::PAYMENT_METHOD_ALLIANCE_ONLINE => array('name' => 'Alliance Online', 'logo' => 'alliancebank_online.png'),
        self::PAYMENT_METHOD_AMBANK_ONLINE => array('name' => 'AmBank Online', 'logo' => 'AmBankLogo_s.png'),
        self::PAYMENT_METHOD_RHB_ONLINE => array('name' => 'RHB Online', 'logo' => 'RHBBank_s.png'),

        self::PAYMENT_METHOD_HONG_LEONG_ONLINE => array('name' => 'Hong Leong Online', 'logo' => 'HLB_s.png'),
        self::PAYMENT_METHOD_FPX => array('name' => 'FPX', 'logo' => 'FPXLogo_new.png'),
        self::PAYMENT_METHOD_CIMB_CLICKS => array('name' => 'CIMB Click', 'logo' => 'CIMBLogo_s.png'),
        self::PAYMENT_METHOD_WEB_CASH => array('name' => 'Web Cash', 'logo' => 'WebCashLogo_s.png'),
        self::PAYMENT_METHOD_CELCOM_AIRCASH => array('name' => 'Celcom AirCash', 'logo' => 'Celcomaircash_s.png'),

        self::PAYMENT_METHOD_BANK_RAKYAT_BANKING => array('name' => 'Bank Rakyat Internet Banking', 'logo' => 'BRLogo_s.png'),
        self::PAYMENT_METHOD_AFFIN_ONLINE => array('name' => 'AffinOnline', 'logo' => 'AffinbankLogo_s.png'),
        self::PAYMENT_METHOD_PAY4ME => array('name' => 'Pay4Me (Delay Payment)', 'logo' => 'Pay4Me_s.png'),
        self::PAYMENT_METHOD_MYBSN => array('name' => 'myBSN', 'logo' => 'mybsn_s.png'),
        self::PAYMENT_METHOD_PAYPAL => array('name' => 'PayPal (MYR)', 'logo' => 'Paypal_logo4.png'),
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
