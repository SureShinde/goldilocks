<?php
namespace Acommerce\SmsIntegration\Model\Plugin;

class OrderStatePlugin
{

    protected $_smshelper;

    public function __construct(\Acommerce\SmsIntegration\Helper\Data $smshelper)
    {
        $this->_smshelper = $smshelper;
    }

    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject, $result
    ){
        //send SMS
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($result->getState(). ' - '. $result->getStatus());

        return $result;
    }
}