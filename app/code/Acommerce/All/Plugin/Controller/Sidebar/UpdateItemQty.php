<?php

namespace Acommerce\All\Plugin\Controller\Sidebar;

use Magento\Framework\Json\Helper\Data;
use Magento\Checkout\Model\Sidebar;

class UpdateItemQty
{
    protected $_response;
    protected $jsonHelper;
    protected $sidebar;
    protected $_request;
    protected $_helper;
    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\App\ResponseInterface $response,
        Data $jsonHelper,
        Sidebar $sidebar,
        \Magento\Framework\App\RequestInterface $request,
        \Acommerce\All\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_response = $response;
        $this->jsonHelper = $jsonHelper;
        $this->sidebar = $sidebar;
        $this->_request = $request;
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
    }

    public function aroundExecute(\Magento\Checkout\Controller\Sidebar\UpdateItemQty $subject, \Closure $proceed)
    {
        $itemQty = (int)$this->_request->getParam('item_qty');
        $itemId = (int)$this->_request->getParam('item_id');

        $item = $this->_checkoutSession->getQuote()->getItemById($itemId);
        /**
         * Check max sale quantity
         */
        $product = $item->getProduct();
        if ($itemQty > $this->_helper->getMaxSaleQty($product)){
            return $this->jsonResponse(__("Sorry, you have reached the maximum quantity allowed for this item."));
        }

        $returnValue = $proceed();
        return $returnValue;
    }

    public function jsonResponse($error = '')
    {
        return $this->_response->representJson(
            $this->jsonHelper->jsonEncode($this->sidebar->getResponseData($error))
        );
    }
}