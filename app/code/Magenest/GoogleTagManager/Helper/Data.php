<?php

namespace Magenest\GoogleTagManager\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) (number of methods will scale with configuration options)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ACTIVE = 'universalanalytics/gtm/active';
    const XML_PATH_ACCOUNT = 'universalanalytics/gtm/account';

    const XML_PATH_REVENUE_TAX = 'universalanalytics/revenue/tax';
    const XML_PATH_REVENUE_SHIPPING = 'universalanalytics/revenue/shipping';
    const XML_PATH_REVENUE_DISCOUNT = 'universalanalytics/revenue/discount';

    const XML_PATH_ITEM_TAX = 'universalanalytics/item/tax';
    const XML_PATH_ITEM_DISCOUNT = 'universalanalytics/item/discount';
    const XML_PATH_SHIPPING_TAX = 'universalanalytics/shipping/tax';
    const XML_PATH_CHECKOUT_STEPS = 'universalanalytics/checkout/steps';

    const XML_PATH_PRICE_FORMAT_DECIMALS_PRECISION = 'universalanalytics/price_format/decimals_precision';
    const XML_PATH_PRICE_FORMAT_DECIMALS_POINT = 'universalanalytics/price_format/decimals_point';
    const XML_PATH_PRICE_FORMAT_THOUSANDS_SEPARATOR = 'universalanalytics/price_format/thousands_separator';
    const XML_PATH_PURCHASE_CATEGORY = 'universalanalytics/purchase/category';

    const XML_PATH_PRODUCT_IMPRESSIONS = 'universalanalytics/product/impressions';
    const XML_PATH_PRODUCT_REPORT_PARENT_CATEGORIES = 'universalanalytics/product/report_parent_categories';
    const XML_PATH_PRODUCT_REPORT_PURCHASE_CATEGORY = 'universalanalytics/product/report_purchase_category';

    public function isEnabled()
    {
        if (!$this->scopeConfig->getValue(self::XML_PATH_ACCOUNT, ScopeInterface::SCOPE_STORE)) {
            return false;
        }

        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORE)) {
            return false;
        }

        return true;
    }

    public function getAccountId()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ACCOUNT, ScopeInterface::SCOPE_STORE);
    }

    public function isTaxIncludedInGrandTotal()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REVENUE_TAX, ScopeInterface::SCOPE_STORE);
    }

    public function isShippingIncludedInGrandTotal()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REVENUE_SHIPPING, ScopeInterface::SCOPE_STORE);
    }

    public function isDiscountIncludedInGrandTotal()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REVENUE_DISCOUNT, ScopeInterface::SCOPE_STORE);
    }

    public function isTaxIncludedInItem()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ITEM_TAX, ScopeInterface::SCOPE_STORE);
    }

    public function isDiscountIncludedInItem()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ITEM_DISCOUNT, ScopeInterface::SCOPE_STORE);
    }

    public function isTaxIncludedInShipping()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SHIPPING_TAX, ScopeInterface::SCOPE_STORE);
    }

    public function getCheckoutSteps()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CHECKOUT_STEPS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function getPricesDecimalsPrecision()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRICE_FORMAT_DECIMALS_PRECISION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getPricesDecimalsPoint()
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PRICE_FORMAT_DECIMALS_POINT,
            ScopeInterface::SCOPE_STORE
        );

        return $value === null ? '' : $value;
    }

    /**
     * @return string
     */
    public function getPricesThousandSeparator()
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PRICE_FORMAT_THOUSANDS_SEPARATOR,
            ScopeInterface::SCOPE_STORE
        );

        return $value === null ? '' : $value;
    }

    /**
     * Formatted Price according to configuration
     *
     * @param mixed $price
     * @return string
     */
    public function getFormattedPrice($price)
    {
        return \number_format(
            $price,
            $this->getPricesDecimalsPrecision(),
            $this->getPricesDecimalsPoint(),
            $this->getPricesThousandSeparator()
        );
    }

    public function isCategoryNameOnPurchaseEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PURCHASE_CATEGORY);
    }

    /**
     * Get Boolean value if product impression can be shown
     *
     * @return bool
     */
    public function isEnabledProductImpressions()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_IMPRESSIONS);
    }

    public function reportParentCategories()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_REPORT_PARENT_CATEGORIES,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function reportQuoteItemCategory()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_REPORT_PURCHASE_CATEGORY,
            ScopeInterface::SCOPE_STORE
        );
    }
}
