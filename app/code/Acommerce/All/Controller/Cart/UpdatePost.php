<?php

namespace Acommerce\All\Controller\Cart;

use Magento\Checkout\Model\Cart as CustomerCart;

class UpdatePost extends \Magento\Checkout\Controller\Cart\UpdatePost
{
    protected $helper;

    protected $needReload;

    protected $_itemUpdating;

    protected $_itemQty;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        \Acommerce\All\Helper\Data $helper
    )
    {
        $this->helper = $helper;
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
    }

    /**
     * Update customer's shopping cart
     *
     * @return void
     */
    protected function _updateShoppingCart()
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get(
                        \Magento\Framework\Locale\ResolverInterface::class
                    )->getLocale()]
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $item = $this->_checkoutSession->getQuote()->getItemById($index);
                        if (!$item) {
                            continue;
                        }
                        /**
                         * Check max sale quantity
                         */
                        $product = $item->getProduct();
                        if ($data['qty'] > $this->helper->getMaxSaleQty($product)) {
                            $this->messageManager->addErrorMessage($this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml('Sorry, you have reached the maximum quantity allowed for this item.'));
                            $this->needReload = true;
                            $this->_itemUpdating = $item->getId();
                            $this->_itemQty = $item->getQty();
                            return false;
                        }

                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                if (!$this->cart->getCustomerSession()->getCustomerId() && $this->cart->getQuote()->getCustomerId()) {
                    $this->cart->getQuote()->setCustomerId(null);
                }

                $cartData = $this->cart->suggestItemsQty($cartData);
                $this->cart->updateItems($cartData)->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(
                $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t update the shopping cart.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
    }

    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');
        $this->needReload = false;
        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            default:
                $this->_updateShoppingCart();
        }
        if($this->needReload == false){
            return $this->_goBack();
        }else{
            $result =  $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
            $result = $result->setData(['is_redirect' => 'true', 'item_updating' => $this->_itemUpdating, 'item_qty' => $this->_itemQty]);
            return $result;
        }
    }
}