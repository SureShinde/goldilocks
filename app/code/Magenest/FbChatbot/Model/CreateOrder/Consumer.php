<?php
declare(strict_types=1);

namespace Magenest\FbChatbot\Model\CreateOrder;

use Magenest\FbChatbot\Helper\Data;
use Magenest\FbChatbot\Helper\SessionHelper;
use Magenest\FbChatbot\Model\Bot;
use Magento\Quote\Model\Quote;
use Magento\Framework\App\ResourceConnection;

class Consumer
{
    const TOPIC_NAME = 'chatbot.createOrder';
    /**
     * @var Bot
     */
    private $bot;
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SessionHelper
     */
    protected $sessionHelper;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;
    /**
     * @var Quote
     */
    protected $_quote;
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * Consumer constructor.
     * @param Bot $bot
     * @param Data $helper
     * @param SessionHelper $sessionHelper
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param ResourceConnection $resource
     */
    public function __construct(
        Bot $bot,
        Data $helper,
        SessionHelper $sessionHelper,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        ResourceConnection $resource
    )
    {
        $this->bot = $bot;
        $this->helper = $helper;
        $this->sessionHelper = $sessionHelper;
        $this->quoteManagement = $quoteManagement;
        $this->resource = $resource;
    }

    /**
     * precess create order via chatBot
     * @param string $data
     */
    public function process(string $data)
    {
        try {
            $data = $this->helper->unserialize($data);
            $this->_quote = $this->sessionHelper->getQuote($data['quoteId']);
            $this->placeOrder($data['senderId'], $this->_quote);
        }catch (\Exception $e){
            $this->bot->sendTextMessage($data['senderId'], __('Sorry we can create order now. Please try again later!')->__toString());
            $this->helper->getLogger()->error('Error when processing create order '. $e);
        }
    }

    /**
     * @param $senderId
     * @param Quote $quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function placeOrder($senderId, Quote $quote)
    {
        $shippingAddress = $this->helper->unserialize($quote->getStoredMessage());
        $address = $this->bot->getAddress($shippingAddress);
        if($quote->getIsActive() && is_array($shippingAddress)) {
            $customer = $this->sessionHelper->getCustomerByEmail($shippingAddress['email'], $address);
            $quote->getShippingAddress()->setCustomerId($customer->getId());
            $quote->getBillingAddress()->setCustomerId($customer->getId());
            //Assign quote to customer
            $quote->assignCustomerWithAddressChange($customer, $quote->getBillingAddress(), $quote->getShippingAddress());
            // set note for quote
            $quote->setCustomerNote($shippingAddress['note']);
            // Set Sales Order Payment
            $paymentCode = $shippingAddress['payment_code'] ?? 'checkmo';
            $quote->getPayment()->importData(['method' => $paymentCode]);
            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);
            // set ordered bot is true
            $connection  = $this->resource->getConnection();
            $salesOrderGridTable  = $connection->getTableName('sales_order_grid');
            $connection->update($salesOrderGridTable, ['ordered_bot' => 1], ["entity_id = ?" => $order->getId()]);
            $this->bot->sendTextMessage($senderId, __('We created order success. Your order number is #%1', $order->getIncrementId())->__toString());
            $this->helper->getLogger()->addInfo('create order success! order id '. $order->getId());
        }
    }
}
