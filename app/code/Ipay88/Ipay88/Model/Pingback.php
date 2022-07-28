<?php
namespace Ipay88\Ipay88\Model;
class Pingback
{
    protected $_objectManager;
    protected $_helper;
    const PINGBACK_OK = 'OK';
    const TRANSACTION_TYPE_ORDER = 'order';
    const STATE_PAID = 2;
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->_objectManager = $objectManager;
        $this->_helper = $this->_objectManager->get('Ipay88\Ipay88\Helper\Data');
    }

    public function pingback($getData)
    {
        $this->_helper->getInitConfig();
        if (!class_exists('Ipay88_Config')) {
            $config = \Magento\Framework\App\Filesystem\DirectoryList::getDefaultConfig();
            require_once(BP . '/' . $config['lib_internal']['path'] . "/ipay88-php/Include.php");
        }

        $pingback = new \Ipay88_Pingback($getData);

        if ($pingback->getStatus() == \Ipay88_Config::PAYMENT_STATUS_SUCCESS) {
            $orderModel = $this->_objectManager->get('Magento\Sales\Model\Order');

            $orderIncrementId = $pingback->getRefNo();
            $orderModel->loadByIncrementId($orderIncrementId);

            $orderStatus = $orderModel::STATE_PROCESSING;
            $this->createOrderInvoice($orderModel, $pingback);
            $orderModel->setStatus($orderStatus);
            $orderModel->save();

            return true;
        }

        return false;
    }

    public function createOrderInvoice($order, $pingback)
    {
        if ($order->canInvoice()) {
            $invoice = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($order);
            $invoice->register();
            $invoice->setState(self::STATE_PAID);
            $invoice->save();
            $transactionSave = $this->_objectManager->create('Magento\Framework\DB\Transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();
            $order->addStatusHistoryComment(__('Created invoice #%1.', $invoice->getId()))->setIsCustomerNotified(true)->save();
            $this->createTransaction($order, $pingback);
        }
    }

    public function createTransaction($order, $pingback)
    {
        $payment = $this->_objectManager->create('Magento\Sales\Model\Order\Payment');
        $payment->setTransactionId($pingback->getRefNo());
        $payment->setOrder($order);
        $payment->setIsTransactionClosed(1);
        $transaction = $payment->addTransaction(self::TRANSACTION_TYPE_ORDER);
        $transaction->beforeSave();
        $transaction->save();
    }
}