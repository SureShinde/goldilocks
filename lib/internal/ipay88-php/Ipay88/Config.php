<?php
/**
 * Ipay88 Inc
 * @author Ipay88 Inc <pvchi@ipay88.com>
 * @package Ipay88\Lib
 */
class Ipay88_Config {
    private static $instance;

    const MODE_PRODUCTION = 1;
    const MODE_SANDBOX = 2;

    const ENABLE = 1;
    const DISABLE = 2;

    const PAYMENT_STATUS_SUCCESS = 1;
    const PAYMENT_STATUS_FAIL = 0;
    const PAYMENT_STATUS_PENDING = 6;

    /**
     * @var string
     */
    protected $merchantCode;


    /**
     * @var string
     */
    protected $merchantKey;

    /**
     * @var array
     */
    protected $modes = array(
        self::MODE_PRODUCTION   => 'Production',
        self::MODE_SANDBOX      => 'Sanbox'
    );

    /**
     * @return string
     */
    public function getMerchantCode()
    {
        return $this->merchantCode;
    }

    /**
     * @param string $merchantCode
     */
    public function setMerchantCode($merchantCode)
    {
        $this->merchantCode = $merchantCode;
    }

    /**
     * @return string
     */
    public function getMerchantKey()
    {
        return $this->merchantKey;
    }

    /**
     * @param string $merchantKey
     */
    public function setMerchantKey($merchantKey)
    {
        $this->merchantKey = $merchantKey;
    }

    /**
     * @return array
     */
    public function getModes()
    {
        return $this->modes;
    }

    /**
     * @param array $modes
     */
    public function setModes($modes)
    {
        $this->modes = $modes;
    }

    /**
     * @param array $config
     */

    public function set($config = array())
    {
        if (isset($config['merchant_code'])) {
            $this->setMerchantCode($config['merchant_code']);
        }
        if (isset($config['merchant_key'])) {
            $this->setMerchantKey($config['merchant_key']);
        }
    }

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    /**
     * iPay88_Config constructor.
     */
    public function __construct()
    {
    }

    /**
     *
     */
    private function __clone()
    {
    }
}