<?php

namespace Magenest\GoogleTagManager\Model;

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class Checkout
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magenest\GoogleTagManager\Helper\Data
     */
    private $gtmHelper;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession, // phpcs:ignore MEQP2.Classes.MutableObjects.MutableObjects
        \Magenest\GoogleTagManager\Helper\Data $gtmHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->gtmHelper = $gtmHelper;
    }

    public function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    public function getShipping(OrderInterface $order)
    {
        return $this->gtmHelper->isTaxIncludedInShipping()
            ? $order->getShippingInclTax()
            : $order->getShippingAmount();
    }

    public function getRevenue(OrderInterface $order)
    {
        $revenue = $this->getRevenueWithOrWithoutShipping($order);
        $revenue = $this->getRevenueWithOrWithoutDiscount($order, $revenue);
        $revenue = $this->getRevenueWithOrWithoutTax($order, $revenue);

        return $this->gtmHelper->getFormattedPrice($revenue);
    }

    public function getRevenueWithOrWithoutShipping(OrderInterface $order)
    {
        $revenue = $order->getGrandTotal();

        if (!$this->gtmHelper->isShippingIncludedInGrandTotal()) {
            $revenue -= $this->getShipping($order);
        }

        return $revenue;
    }

    public function getRevenueWithOrWithoutDiscount(OrderInterface $order, $revenue)
    {
        if (!$this->gtmHelper->isDiscountIncludedInGrandTotal()) {
            $revenue -= $order->getDiscountAmount();
        }

        return $revenue;
    }

    public function getRevenueWithOrWithoutTax(OrderInterface $order, $revenue)
    {
        if (!$this->gtmHelper->isTaxIncludedInGrandTotal()) {
            $revenue -= $order->getTaxAmount();
        }

        return $revenue;
    }

    /**
     * @param OrderItemInterface|CartItemInterface $item
     * @return string
     */
    public function getProductPrice($item)
    {
        $price = $item->getRowTotalInclTax();

        if ($this->gtmHelper->isDiscountIncludedInItem()) {
            $price -= $item->getDiscountAmount();
        }

        if (!$this->gtmHelper->isTaxIncludedInItem()) {
            $price -= $item->getTaxAmount();
        }

        if ($item instanceof \Magento\Quote\Api\Data\CartItemInterface) {
            $price /= $item->getQty();
        } elseif ($item instanceof \Magento\Sales\Api\Data\OrderItemInterface) {
            $price /= $item->getQtyOrdered();
        }

        return $price;
    }
}
