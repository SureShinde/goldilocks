<?php

namespace Acommerce\All\Plugin\Controller\Cart;

class Add
{
    protected $_cart;
    protected $_checkoutSession;
    protected $_request;
    protected $_resultJsonFactory;
    protected $_helper;

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Acommerce\All\Helper\Data $helper
    )
    {
        $this->_cart = $cart;
        $this->_checkoutSession = $checkoutSession;
        $this->_request = $request;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_helper = $helper;
    }

    public function aroundExecute(\Magento\Checkout\Controller\Cart\Add $subject, \Closure $proceed)
    {
        $params = $this->_request->getParams();
        $quote = $this->_checkoutSession->getQuote();
        $items = $quote->getAllItems();

        /**
         * Check max sale quantity
         */
        foreach($items as $item) {
            if ($item->getProductId() == $params['product']){
                $product = $item->getProduct();
                $itemQty = $item->getQty() + $params['qty'];
                if ($itemQty > $this->_helper->getMaxSaleQty($product)){
                    return $this->maximumQty();
                }
            }
        }

        $returnValue = $proceed();
        return $returnValue;
    }

    protected function maximumQty()
    {
        $resultJson = $this->_resultJsonFactory->create();
        $resultJson->setData(['maximumQty' => 'maximumQty', 'error_message' => __("Sorry, you have reached the maximum quantity allowed for this item.")]);
        return $resultJson;
    }
}