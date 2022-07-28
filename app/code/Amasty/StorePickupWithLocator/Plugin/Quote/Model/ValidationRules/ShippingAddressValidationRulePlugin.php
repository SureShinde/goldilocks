<?php

namespace Amasty\StorePickupWithLocator\Plugin\Quote\Model\ValidationRules;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ValidationRules\ShippingAddressValidationRule;
use Amasty\StorePickupWithLocator\Model\Quote\SetIgnoreShippingValidationForQuote;

/**
 * Disable Shipping Validation
 */
class ShippingAddressValidationRulePlugin
{
    /**
     * @var SetIgnoreShippingValidationForQuote $validationDisabler
     */
    private $validationDisabler;

    public function __construct(SetIgnoreShippingValidationForQuote $validationDisabler)
    {
        $this->validationDisabler = $validationDisabler;
    }

    /**
     * @param ShippingAddressValidationRule $subject
     * @param Quote $quote
     */
    public function beforeValidate(
        ShippingAddressValidationRule $subject,
        Quote $quote
    ) {
        $this->validationDisabler->execute($quote);
    }
}
