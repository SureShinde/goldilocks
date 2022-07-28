<?php
/**
 * @author iPay88 Inc. <support@ipay88.com.my>
 * @package iPay88\ThirdpartyIntegration\Magento
 * @Description: Using for Magento 2.x and Philippines Only.
 */
namespace Ipay88\Ipay88\Controller\Index;
use Magento\Framework\Controller\ResultFactory;

use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;


class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $product;
    protected $cart;
    protected $_checkoutSession;
    protected $_paymentHelper;

    const STATE_PENDING_PAYMENT = 'pending_payment';
    const PAYMENT_METHOD = 'ipay88';

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cartModel
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->_formkey = $formkey;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
        $this->cart = $cartModel;
        $this->_checkoutSession = $checkoutSession;

        parent::__construct($context);

        $this->customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        $this->helper = $this->_objectManager->get('Ipay88\Ipay88\Helper\Data');
    }


    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {

        if (!$this->cart->getQuote()->getId()) {
            $this->_redirect('/');
        } else {
            $cartInfo = $this->cart->getQuote()->getData();
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            if(!empty($cartInfo['customer_email'])) {
                $email = $cartInfo['customer_email'];
            }

            $resultPage = $this->resultPageFactory->create();
            $currencyCode = $this->cart->getQuote()->getStoreCurrencyCode();
            $customerEmail = empty($this->getEmailCustomer()) ? $email : $this->getEmailCustomer();

            $tempOrder = [
                'currency_id' => $currencyCode,
                'email' => $customerEmail,
                'shipping_address' => $this->getShipping(),
                'items' => $this->getProducts()
            ];

            $result = $this->createMageOrder($tempOrder);

            if ($result['status']) {
                $params = [
                    'email' => $customerEmail,
                    'orderId' => $result['order_id'],
                    'currency' => $currencyCode,
                    'total' => $result['total_paid']
                ];

                $orderAmount = number_format($params['total'], 2);

                $shippingData = $this->getShipping();
                $helper = $this->_objectManager->create('Ipay88\Ipay88\Helper\Data');

                $merchantCode           = $helper->getConfig('ipay88_merchant_code');
                $merchantKey            = $helper->getConfig('ipay88_merchant_key');
                $paymentId              = $this->getRequest()->getParam('ipay88_payment_method_selected');
                $hashAmount             = $orderAmount * 100;
                $currency               = $currencyCode;

                $prodDesc               = '';
                $products = $this->getProducts();

                if(is_array($products) && count($products)) {
                    foreach ($products as $product) {
                        $prodDesc .= isset($product['product_name']) ? $product['product_name'] : '';
                    }
                }

                if(strlen($prodDesc) > 99) {
                    $prodDesc = substr($prodDesc, 0, 98);
                }

                $customerUsername       = $shippingData['firstname'] . ' ' . $shippingData['lastname'];
                $userContact            = $shippingData['telephone'];

                $refNo = $params['orderId'];
                $sourceString = $merchantKey . $merchantCode . $refNo . $hashAmount . $currency;
                $signature = new \Ipay88_Signature($sourceString);
                $mySignature            = $signature->getSignature();


                $baseUrl = $this->_storeManager->getStore()->getUrl();
                $responseURL            = $baseUrl . 'ipay88/payment/response';
                $backendUrl             = $baseUrl . 'ipay88/payment/ipn';

                $data = array(
                    'MerchantCode'      => $merchantCode,
                    'RefNo'             => $refNo,
                    'PaymentId'         => $paymentId,
                    'Amount'            => $orderAmount,
                    'Currency'          => $currency,
                    'ProdDesc'          => $prodDesc,
                    'Lang'              => 'UTF-8',
                    'UserName'          => $customerUsername,
                    'UserEmail'         => $customerEmail,
                    'UserContact'       => $userContact,
                    'Signature'         => $mySignature,
                    'ResponseURL'       => $responseURL,
                    'BackendURL'        => $backendUrl
                );

                $widget = $this->getWidget($data);

                $resultPage->getConfig()->getTitle()
                    ->prepend(__('New order with iPay88: #' . $result['order_id']));
                $resultPage->getLayout()->getBlock('ipay88_ipay88')->setData('widget', $widget);
            } else {
                $resultPage->getConfig()->getTitle()
                    ->prepend(__($result['message']));
            }
        }

        return $resultPage;
    }


    public function getOrder(\Magento\Framework\Event\Observer $observer)
    {

        $order = $observer->getOrder();
        $order_id = $order->getIncrementId();
        $this->logger->info($order_id);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order_information = $order->loadByIncrementId($order_id);


        return $order;
    }

    private function getWidget($params)
    {
        $widget = new \Ipay88_Wiget($params);

        $helper = $this->_objectManager->create('Ipay88\Ipay88\Helper\Data');
        $redirectUrl = $helper->getRedirectUrl();

        $widget->setAction($redirectUrl);

        return $widget->generateRedirectForm();
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
            foreach ($items['items'] AS $item) {
                $products[] = [
                    'product_id'    => $item['product_id'],
                    'product_name'  => $item['name'],
                    'qty'           => $item['qty'],
                    'price'         => $item['price']
                ];
            }
        }
        return $products;
    }

    private function getUserProfileData()
    {
        $shippingData = $this->cart->getQuote()->getShippingAddress()->getData();
        return [
            'customer[city]' => $shippingData['city'],
            'customer[state]' => $shippingData['region'],
            'customer[address]' => $shippingData['street'],
            'customer[country]' => $shippingData['country_id'],
            'customer[zip]' => $shippingData['postcode'],
            'customer[firstname]' => $shippingData['firstname'],
            'customer[lastname]' => $shippingData['lastname']
        ];
    }

    public function createMageOrder($orderData)
    {
        $store = $this->_storeManager->getStore();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']);// load customet by email address

        if (!$customer->getEntityId()) {
            //If not avilable then create this customer
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($orderData['shipping_address']['firstname'])
                ->setLastname($orderData['shipping_address']['lastname'])
                ->setEmail($orderData['email'])
                ->setPassword($orderData['email']);
            $customer->save();
        }
        $quote = $this->quote->create(); //Create object of quote

        $quote->setStore($store); //set store for which you create quote
        // if you have allready buyer id then you can load customer directly
        $customer = $this->customerRepository->getById($customer->getEntityId());

        $quote->setCurrency();
        $quote->assignCustomer($customer); //Assign quote to customer

        //add items in quote
        foreach ($orderData['items'] as $item) {
            $product = $this->_product->load($item['product_id']);
            $product->setPrice($item['price']);
            $quote->addProduct(
                $product,
                intval($item['qty'])
            );
        }
        //Set Address to quote
        $quote->getBillingAddress()->addData($orderData['shipping_address']);
        $quote->getShippingAddress()->addData($orderData['shipping_address']);

        // Collect Rates and Set Shipping & Payment Method
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($orderData['shipping_address']['shipping_method']); //shipping method
        $quote->setPaymentMethod(self::PAYMENT_METHOD); //payment method
        $quote->setInventoryProcessed(false); //not effetc inventory
        $quote->save(); //Now Save quote and your quote is ready
        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => self::PAYMENT_METHOD]);
        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        // Create Order From Quote
        try {
            $order = $this->quoteManagement->submit($quote);
        } catch (\Exception $e) {
            $this->messageManager->addNotice($e->getMessage());
        }

        if (!empty($order)) {
            $order->setEmailSent(1);
            $result['order_id'] = $order->getRealOrderId();
            $result['total_paid'] = $order->getData('total_due');
            $result['status'] = 1;

            $order->setStatus(self::STATE_PENDING_PAYMENT);
            $order->save();

            $this->cart->getQuote()->removeAllItems();
            $this->cart->getQuote()->delete();
            $this->cart->getQuote()->save();
        } else {
            $result = ['status' => 0, 'message' => 'Create order has been error'];
        }
        return $result;
    }

    /**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

}