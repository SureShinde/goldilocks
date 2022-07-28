<?php
/**
 * @author iPay88 Inc. <support@ipay88.com.my>
 * @package iPay88\ThirdpartyIntegration\Magento
 * @Description: Using for Magento 2.x and Philippines Only.
 */
namespace IPay88\IPay88\Controller\Payment;

use Magento\Checkout\Model\Session;

class Requery extends \Magento\Framework\App\Action\Action
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

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $params = $this->getRequest()->getParams();

        $merchantCode    = isset($params['MerchantCode'])  ? $params['MerchantCode'] : null;
        $refNo           = isset($params['RefNo'])         ? $params['RefNo'] : null;
        $amount          = isset($params['Amount'])        ? $params['Amount'] : null;

        $Result = '';

        if(!empty($merchantCode) && !empty($refNo) && !empty($amount)) {
            $query = "https://www.mobile88.com/epayment/enquiry.asp?MerchantCode=" . $merchantCode . "&RefNo=" . $refNo . "&Amount=" . $amount;

            $url = parse_url($query);
            $host = $url["host"];
            $path = $url["path"] . "?" . $url["query"];

            $timeout = 1;
            $fp = fsockopen ($host, 80, $errno, $errstr, $timeout);

            if ($fp) {
                $buf = '';
                fputs ($fp, "GET $path HTTP/1.0\nHost: " . $host . "\n\n");
                while (!feof($fp)) {
                    $buf .= fgets($fp, 128);
                }

                if(!empty($buf)) {
                    $lines = preg_split('/;|,/', $buf);
                    $Result = $lines[count($lines)-1];

                } else {
                    $Result = "Invalids Params!!!";
                }

                $resultPage->getLayout()->getBlock('ipay88_payment_requery')->setData('requeryMessage', $Result);

            } else {
                # enter error handing code here
            }

            fclose($fp);

        } else {
            $resultPage->getLayout()->getBlock('ipay88_payment_requery')->setData('iPay88ErrorDesc', "Whoops! Invalid params!");
        }

        return $resultPage;
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