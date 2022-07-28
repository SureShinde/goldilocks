<?php
/**
 * @author iPay88 Inc. <support@ipay88.com.my>
 * @package iPay88\ThirdpartyIntegration\Magento
 * @Description: Using for Magento 2.x and Philippines Only.
 */

namespace Ipay88\Ipay88\Model;

/**
 * iPay88 In Store payment method model
 */
class Ipay88 extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'ipay88';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;


   public function getConfig() {

   }

}
