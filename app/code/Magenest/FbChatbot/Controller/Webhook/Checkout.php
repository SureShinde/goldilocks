<?php

namespace Magenest\FbChatbot\Controller\Webhook;

use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Model\QuoteRepository;
use Magenest\FbChatbot\Helper\Data;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Framework\App\ResourceConnection;

class  Checkout extends Action
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var ResourceConnection
     */
    private  $resource;

    /**
     * Checkout constructor.
     * @param Context $context
     * @param Session $session
     * @param CustomerSession $customerSession
     * @param QuoteRepository $quoteRepository
     * @param Data $helper
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        Session $session,
        CustomerSession $customerSession,
        QuoteRepository $quoteRepository,
        Data $helper,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->customerSession = $customerSession;
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->resource = $resource;
    }

    public function execute()
    {
        $quote_id = $this->getRequest()->getParam('quote_id');
        $checkoutRedirect = $this->getRequest()->getParam('checkout_page');
        $this->session->setQuoteId($quote_id);
        $customer = $this->customerSession->getCustomerData();
        try {
            $botQuote = $this->quoteRepository->get($quote_id);
            if(!empty($customer)){
                $this->customerSession->setCustomerDataAsLoggedIn($customer);
                $quote = $this->session->getQuote();
                if(!$quote->getSenderId() && $botQuote->getItemsCount() > 0) {
                    $quote->setSenderId($botQuote->getSenderId());
                    $quote->setStoredMessage($botQuote->getStoredMessage());
                    $this->updateQuoteIdMask($botQuote->getSenderId(), $quote);
                    $this->quoteRepository->save($quote);
                }
            }
            if($checkoutRedirect) {
                return $this->_redirect('checkout/index/index');
            }
        }catch (\Throwable $e){
            $this->helper->getLogger()->warning("Modify cart & checkout: " . $e->getMessage());
        }

        return $this->_redirect('checkout/cart');
    }

    /**
     * update mask when redirect from bot to website customer is logged
     * @param $senderId
     * @param $quote
     */
    public function updateQuoteIdMask($senderId, $quote) {
        $connection = $this->resource->getConnection();
        $quoteMarkTable = $this->resource->getTableName('quote_id_mask');
        $select = "SELECT entity_id FROM {$quoteMarkTable} WHERE quote_id = {$quote->getId()}";
        $maskId = $connection->fetchOne($select);
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($maskId);
        $quoteIdMask->setMaskedId($senderId . "_FB_" . time());
        $quoteIdMask->setQuoteId($quote->getId())->save();
    }
}
