<?php

namespace Acommerce\SmsIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class OrderSaveAfter implements ObserverInterface
{
    protected \Acommerce\SmsIntegration\Helper\Data $_smshelper;
    private Order\StatusLabel $statusLabel;

    /**
     * @param \Acommerce\SmsIntegration\Helper\Data $smshelper
     * @param Order\StatusLabel $statusLabel
     */
    public function __construct(
        \Acommerce\SmsIntegration\Helper\Data $smshelper,
        \Magento\Sales\Model\Order\StatusLabel $statusLabel
    )
    {
        $this->_smshelper = $smshelper;
        $this->statusLabel = $statusLabel;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getOrder();
        $status = $order->getData('status');
        $prevStatus = $order->getOrigData('status');
        if ($order instanceof \Magento\Framework\Model\AbstractModel) {
            if ($status !== $prevStatus && $prevStatus) {
                $prevStatus = $this->statusLabel->getStatusLabel($prevStatus);
                $status = $this->statusLabel->getStatusLabel($status);
                $comment = __("The order has been changed status from %1 to %2", $prevStatus, $status);
                $this->_smshelper->smsCommentOnOrder($order, 'comment_order', $comment);
            }
        }
    }
}
