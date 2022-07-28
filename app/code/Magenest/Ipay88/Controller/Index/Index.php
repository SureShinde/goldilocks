<?php

namespace Magenest\Ipay88\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Index implements \Magento\Framework\App\ActionInterface
{
    const STATE_PENDING_PAYMENT = 'pending_payment';
    const PAYMENT_METHOD = 'ipay88';

    /**
     * @var \Ipay88\Ipay88\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var ResultFactory
     */
    protected $resultFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutData;
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * Index constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Checkout\Model\Cart $cartModel
     * @param \Ipay88\Ipay88\Helper\Data $helper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Checkout\Model\Cart $cartModel,
        \Ipay88\Ipay88\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Sales\Model\OrderRepository $orderRepository
    ) {
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->cart = $cartModel;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->_request = $request;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutData = $checkoutData;
        $this->cartManagement = $cartManagement;
        $this->orderRepository = $orderRepository;

    }


    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {

        $cartInfo = $this->cart->getQuote()->getData();
        $email = $this->_request->getParam('email') ?? '';
        if (!empty($cartInfo['customer_email'])) {
            $email = $cartInfo['customer_email'];
        }



        $result = $this->createMageOrder();

        $currencyCode = $this->cart->getQuote()->getStoreCurrencyCode();
        $customerEmail = $this->getEmailCustomer() ?? $email;

        if ($result['status']) {
            $params = [
                'email' => $customerEmail,
                'orderId' => $result['order_id'],
                'currency' => $currencyCode,
                'total' => $result['total_paid']
            ];

            $orderAmount = number_format($params['total'], 2);

            $shippingData = $this->getShipping();
            $merchantCode = $this->helper->getConfig('ipay88_merchant_code');
            $merchantKey = $this->helper->getConfig('ipay88_merchant_key');
            $paymentId = $this->_request->getParam('ipay88_payment_method_selected');
            $hashAmount = $orderAmount * 100;
            $currency = $currencyCode;

            $prodDesc = '';
            $products = $this->getProducts();

            if (is_array($products) && count($products)) {
                foreach ($products as $product) {
                    $prodDesc .= $product['product_name'] ?? '';
                }
            }

            if (strlen($prodDesc) > 99) {
                $prodDesc = substr($prodDesc, 0, 98);
            }

            $customerUsername = $shippingData['firstname'] . ' ' . $shippingData['lastname'];
            $userContact = $shippingData['telephone'];

            $refNo = $params['orderId'];
            $sourceString = $merchantKey . $merchantCode . $refNo . $hashAmount . $currency;
            $signature = new \Ipay88_Signature($sourceString);
            $mySignature = $signature->getSignature();

            $baseUrl = $this->_storeManager->getStore()->getUrl();
            $responseURL = $baseUrl . 'ipay88/payment/response';
            $backendUrl = $baseUrl . 'ipay88/payment/ipn';

            $data = array(
                'MerchantCode' => $merchantCode,
                'RefNo' => $refNo,
                'PaymentId' => $paymentId,
                'Amount' => $orderAmount,
                'Currency' => $currency,
                'ProdDesc' => $prodDesc,
                'Lang' => 'UTF-8',
                'UserName' => $customerUsername,
                'UserEmail' => $customerEmail,
                'UserContact' => $userContact,
                'Signature' => $mySignature,
                'ResponseURL' => $responseURL,
                'BackendURL' => $backendUrl
            );

            $htmlForm = $this->renderHtmlRediectForm($data);
            $jsonData = [
                'success' => true,
                'htmlForm' => $htmlForm
            ];
        } else {
            $jsonData = [
                'success' => false,
                'message' => $result['message']
            ];
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($jsonData);
    }

    public function renderHtmlRediectForm($data)
    {
        $action = $this->helper->getRedirectUrl();
        $formName = 'ipay88PaymentRedirectForm';
        $htmlCode =
            '<form style="text-align:center;" name="' . $formName . '"  method="POST" action="' . $action . '">';
        $options = $data;

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $option) {
                $htmlCode .= '<input type="hidden" name="' . $key . '" value="' . $option . '" />';
            }
        }
        $htmlCode .=
            '<input type="submit" class="hide" value="Pay Now" />
            </form>';
        return $htmlCode;
    }


    private function getEmailCustomer()
    {
        $email = null;
        if ($this->customerSession->isLoggedIn()) {
            $email = $this->customerSession->getCustomer()->getEmail();
        }
        return $email;
    }

    private function getShipping()
    {
        $shippingData = $this->cart->getQuote()->getShippingAddress()->getData();
        $shipping = [
            'firstname' => $shippingData['firstname'],
            'lastname' => $shippingData['lastname'],
            'street' => $shippingData['street'],
            'city' => $shippingData['city'],
            'country_id' => $shippingData['country_id'],
            'region' => $shippingData['region'],
            'postcode' => $shippingData['postcode'],
            'telephone' => $shippingData['telephone'],
            'fax' => $shippingData['fax'],
            'shipping_method' => $shippingData['shipping_method'],
            'save_in_address_book' => $shippingData['save_in_address_book']
        ];
        return $shipping;
    }

    private function getProducts()
    {
        $products = [];
        $items = $this->cart->getItems()->toArray();

        if (!empty($items)) {
            foreach ($items['items'] as $item) {
                $products[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'qty' => $item['qty'],
                    'price' => $item['price']
                ];
            }
        }
        return $products;
    }

    public function createMageOrder()
    {
        $quote = $this->checkoutSession->getQuote();
        $quote->setPaymentMethod(self::PAYMENT_METHOD); //payment method
        $quote->setInventoryProcessed(false); //not effetc inventory
        $quote->save(); //Now Save quote and your quote is ready
        try {
            $orderId = $this->cartManagement->placeOrder($quote->getId());
            $order = $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            $this->messageManager->addNoticeMessage($e->getMessage());
        }

        if (!empty($order)) {
            $order->setEmailSent(1);
            $result['order_id'] = $order->getRealOrderId();
            $result['total_paid'] = $order->getData('total_due');
            $result['status'] = 1;

            $order->setStatus(self::STATE_PENDING_PAYMENT);
            $order->save();
        } else {
            $result = ['status' => 0, 'message' => 'Create order has been error'];
        }
        return $result;
    }

}