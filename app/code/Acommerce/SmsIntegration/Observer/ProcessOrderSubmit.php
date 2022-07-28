<?php

namespace Acommerce\SmsIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class ProcessOrderSubmit implements ObserverInterface
{
    protected $_smshelper;

    protected $_request;

    /**
     * @param \Acommerce\SmsIntegration\Helper\Data $smshelper
     * @param \Magento\Framework\App\Request\Http $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Acommerce\SmsIntegration\Helper\Data $smshelper,
        \Magento\Framework\App\Request\Http $request,
        LoggerInterface $logger
    ) {
        $this->_smshelper = $smshelper;
        $this->_request = $request;
    }

    public function execute(Observer $observer)
    {

        $controllerName = $this->_request->getControllerName();

        //$logger->info('$controllerName:'.print_r($controllerName,true));

        $order = $observer->getEvent()->getOrder();

        if ($controllerName == 'order_edit') {
            //$response = $this->_smshelper->smsNewOrder($order, 'edit_order');
        } else {
            if ($order->getPayment()->getMethod() != 'pnx' && $order->getPayment()->getMethod() != 'pnx_wallets') {
                $response = $this->_smshelper->smsNewOrder($order, 'new_order');
            }
        }

        // Synced with Order edit email order sender override
    }
}
