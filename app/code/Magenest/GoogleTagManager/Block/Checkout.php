<?php

namespace Magenest\GoogleTagManager\Block;

use Magenest\GoogleTagManager\Api\ProductObjectGeneratorInterface;

class Checkout extends AbstractGtmBlock
{
    /**
     * @var \Magenest\GoogleTagManager\Helper\Data
     */
    private $gtmHelper;

    /**
     * @var \Magenest\GoogleTagManager\Helper\Checkoutsteps
     */
    private $checkoutSteps;

    /**
     * @var \Magenest\GoogleTagManager\Model\Checkout
     */
    private $checkout;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magenest\GoogleTagManager\Helper\AttributeCodes $attributeCodes
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magenest\GoogleTagManager\Helper\Data $gtmHelper
     * @param \Magenest\GoogleTagManager\Helper\Checkoutsteps $checkoutSteps
     * @param \Magenest\GoogleTagManager\Model\Checkout $checkout
     * @param ProductObjectGeneratorInterface $productObjectGenerator
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magenest\GoogleTagManager\Helper\AttributeCodes $attributeCodes,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magenest\GoogleTagManager\Helper\Data $gtmHelper,
        \Magenest\GoogleTagManager\Helper\Checkoutsteps $checkoutSteps,
        \Magenest\GoogleTagManager\Model\Checkout $checkout,
        ProductObjectGeneratorInterface $productObjectGenerator,
        array $data = []
    ) {
        parent::__construct($context, $attributeCodes, $jsonHelper, $productObjectGenerator, $data);

        $this->gtmHelper = $gtmHelper;
        $this->checkoutSteps = $checkoutSteps;
        $this->checkout = $checkout;

        $this->_isScopePrivate = true;
    }

    protected function _toHtml()
    {
        if (!$this->gtmHelper->isEnabled() || empty($this->getCartItems()) || empty($this->getCheckoutSteps())) {
            return '';
        }

        return parent::_toHtml();
    }

    public function getCurrencyCode()
    {
        return $this->checkout->getQuote()->getQuoteCurrencyCode();
    }

    public function getCartItems()
    {
        return $this->checkout->getQuote()->getAllVisibleItems();
    }

    public function getPrice($item)
    {
        return $this->gtmHelper->getFormattedPrice(
            $this->checkout->getProductPrice($item)
        );
    }

    public function getCheckoutSteps()
    {
        return $this->checkoutSteps->unserializeValue(
            $this->gtmHelper->getCheckoutSteps()
        );
    }

    /**
     * @inheritDoc
     */
    public function getItems()
    {
        return $this->getCartItems();
    }

    /**
     * @inheritDoc
     */
    public function getBasicItemAttributes($object)
    {
        return \array_replace(parent::getBasicItemAttributes($object), [
            'price' => $this->getPrice($object),
            'quantity' => $object->getQty(),
        ]);
    }
}
