<?php
/**
 * @author iPay88 Inc. <support@ipay88.com.my>
 * @package iPay88\ThirdpartyIntegration\Magento
 * @Description: Using for Magento 2.x and Philippines Only.
 */
namespace IPay88\IPay88\Controller\Payment;

use Magento\Checkout\Model\Session;

class Ipn extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $registry;
    protected $order;
    protected $_checkoutSession;
    protected $_orderSender;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Session $checkoutSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\Registry $registry
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->order = $order;
        $this->_orderSender  = $orderSender;

        parent::__construct($context);
    }

    private function getInitConfig()
    {
        if (!class_exists('Ipay88_Config')) {
            $config = \Magento\Framework\App\Filesystem\DirectoryList::getDefaultConfig();
            require_once(BP . '/' . $config['lib_internal']['path'] . "/ipay88-php/Include.php");
        }
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $orderId = isset($_POST['RefNo']) ? $_POST['RefNo'] : '';

        $resultRedirect = $this->resultRedirectFactory->create();
        $this->getInitConfig();

        if(!empty($orderId)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            /* @var $order \Magento\Sales\Model\Order */
            $order = $objectManager->create('\Magento\Sales\Model\Order');
            $order->loadByIncrementId($orderId);

            $helper = $this->_objectManager->create('Ipay88\Ipay88\Helper\Data');

            $merchantKey            = $helper->getConfig('ipay88_merchant_key');
            $merchantCode           = $helper->getConfig('ipay88_merchant_code');
            $paymentId              =  isset($_POST['PaymentId']) ? $_POST['PaymentId'] : '';

            $refNo = $orderId;
            $orderAmount            = isset($_POST['Amount']) ? $_POST['Amount'] : 0;
            $currency               = isset($_POST['Currency']) ? $_POST['Currency'] : '';
            $status                 = isset($_POST['Status']) ? $_POST['Status'] : '';
            $responseSignature      = isset($_POST['Signature']) ? $_POST['Signature'] : '';
            $errorDescription        = isset($_POST['ErrDesc']) ? $_POST['ErrDesc'] : '';

            $hashAmount             = $orderAmount * 100;
            $sourceStr = $merchantKey . $merchantCode . $paymentId . $refNo . $hashAmount . $currency . $status;
            $signautre = new \Ipay88_Signature($sourceStr);
            $expectedSignature = $signautre->generateSignature();

            $paymentLog = '';
            if(is_array($_POST) && count($_POST)) {
                foreach ($_POST as $key => $value) {
                    $paymentLog .= $key . ': ' . $value . '<br/>';
                }
            }

            if($status == \Ipay88_Config::PAYMENT_STATUS_SUCCESS && $responseSignature == $expectedSignature) {
                $paymentLog .= 'Payment successful. In the backend url';

                try {
                    $payment = $order->getPayment();
                    // notify customer
                    $invoice = $payment->getCreatedInvoice();
                    if ($invoice && !$order->getEmailSent()) {
                        $this->_orderSender->send($order);
                        $order->addStatusHistoryComment(
                            __('You notified customer about invoice #%1.',
                                $invoice->getIncrementId())
                        )->setIsCustomerNotified(
                            true
                        )->save();
                    }

                }catch (\Exception $e) {
                    var_dump($e->getMessage()); die;
                }

                $order->addStatusHistoryComment($paymentLog);
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                $order->save();

                /**
                 * @var $pingBack \Ipay88\Ipay88\Model\Pingback
                 */
                $pingBack = $this->_objectManager->create('Ipay88\Ipay88\Model\Pingback');
                $pingBack->pingback($_POST);

                die("RECEIVEOK");

            } elseif ($status == \Ipay88_Config::PAYMENT_STATUS_PENDING && $responseSignature == $expectedSignature) {
                $paymentLog .= 'Payment Pending';
                $order->addStatusHistoryComment($paymentLog);
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $order->save();

                die("RECEIVE NOT OK");
            } else {
                $paymentLog .= 'Payment failed';
                // cancel the order and restore quantity
                $order->addStatusHistoryComment($paymentLog);
                $order->cancel()->save();

                die("RECEIVE NOT OK");
            }
        } else {
            echo "PAYMENT FAIL";
        }

        die;
    }

    /**
     * Return checkout session object
     *
     * @return Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
}