<?php

namespace Magenest\GoogleTagManager\Test\Unit\Model;

use Magenest\GoogleTagManager\Model\Checkout as Model;

use function Magenest\TestingTools\Functions\get;

class CheckoutTest extends \Magenest\TestingTools\Test\Unit\TestCase
{
    public function shippingAmountsProvider()
    {
        return [
            [true, 10],
            [false, 8]
        ];
    }

    /**
     * @param $hasTax
     * @param $expected
     *
     * @dataProvider shippingAmountsProvider
     */
    public function testGetShippingShouldReturnAmountWithOrWithoutTax($hasTax, $expected)
    {
        /** @var Model $model */
        $model = get([
            'gtmHelper..isTaxIncludedInShipping' => $hasTax
        ]);

        $order = get('order', [
            'getShippingAmount' => 8,
            'getShippingInclTax' => 10
        ]);

        $result = $model->getShipping($order);

        $this->assertEquals($expected, $result);
    }

    public function shippingInOrderProvider()
    {
        return [
            [true, 110],
            [false, 100]
        ];
    }

    /**
     * @param $hasShipping
     * @param $expected
     *
     * @dataProvider shippingInOrderProvider
     */
    public function testGetRevenueWithOrWithoutShippingShouldReturnDependingOnAdminSetting($hasShipping, $expected)
    {
        /** @var Model $model */
        $model = get([
            'gtmHelper:' => [
                'isShippingIncludedInGrandTotal' => $hasShipping,
                'isTaxIncludedInShipping' => true
            ]
        ]);

        $order = get('order', [
            'getGrandTotal' => 110,
            'getShippingInclTax' => 10
        ]);

        $result = $model->getRevenueWithOrWithoutShipping($order);

        $this->assertEquals($expected, $result);
    }

    public function discountInOrderProvider()
    {
        return [
            [true, 80, 80],
            [false, 80, 100]
        ];
    }

    /**
     * @param $hasDiscount
     * @param $revenue
     * @param $expected
     *
     * @dataProvider discountInOrderProvider
     */
    public function testGetRevenueWithOrWithoutDiscountShouldReturnDependingOnAdminSetting($hasDiscount, $revenue, $expected)
    {
        /** @var Model $model */
        $model = get([
            'gtmHelper..isDiscountIncludedInGrandTotal' => $hasDiscount
        ]);

        $order = get('order', [
            'getDiscountAmount' => -20
        ]);

        $result = $model->getRevenueWithOrWithoutDiscount($order, $revenue);

        $this->assertEquals($expected, $result);
    }

    public function taxInOrderProvider()
    {
        return [
            [true, 100, 100],
            [false, 100, 80]
        ];
    }

    /**
     * @param $hasTax
     * @param $revenue
     * @param $expected
     *
     * @dataProvider taxInOrderProvider
     */
    public function testGetRevenueWithOrWithoutTaxShouldReturnDependingOnAdminSetting($hasTax, $revenue, $expected)
    {
        /** @var Model $model */
        $model = get([
            'gtmHelper..isTaxIncludedInGrandTotal' => $hasTax
        ]);

        $order = get('order', [
            'getTaxAmount' => 20
        ]);

        $result = $model->getRevenueWithOrWithoutTax($order, $revenue);

        $this->assertEquals($expected, $result);
    }

    public function itemPriceProvider()
    {
        return [
            [true, true, 90],
            [true, false, 72],
            [false, true, 100],
            [false, false, 82]
        ];
    }

    /**
     * @param $hasDiscount
     * @param $hasTax
     * @param $expected
     *
     * @dataProvider itemPriceProvider
     */
    public function testGetProductPriceShouldReturnPriceBasedOnOrderItemConfig($hasDiscount, $hasTax, $expected)
    {
        /** @var Model $model */
        $model = get([
            'gtmHelper:' => [
                'isDiscountIncludedInItem' => $hasDiscount,
                'isTaxIncludedInItem' => $hasTax
            ]
        ]);

        $item = get('item', [
            'getQtyOrdered' => 2,
            'getRowTotalInclTax' => 200,
            'getDiscountAmount' => 20,
            'getTaxAmount' => 36
        ]);

        $result = $model->getProductPrice($item);

        $this->assertEquals($expected, $result);
    }

    /**
     * @param $hasDiscount
     * @param $hasTax
     * @param $expected
     *
     * @dataProvider itemPriceProvider
     */
    public function testGetProductPriceShouldReturnPriceBasedOnCartItemConfig($hasDiscount, $hasTax, $expected)
    {
        /** @var Model $model */
        $model = get([
            'gtmHelper:' => [
                'isDiscountIncludedInItem' => $hasDiscount,
                'isTaxIncludedInItem' => $hasTax
            ]
        ]);

        $item = get('item?cart', [
            'getQty' => 2,
            'getRowTotalInclTax' => 200,
            'getDiscountAmount' => 20,
            'getTaxAmount' => 36
        ]);

        $result = $model->getProductPrice($item);

        $this->assertEquals($expected, $result);
    }
}
