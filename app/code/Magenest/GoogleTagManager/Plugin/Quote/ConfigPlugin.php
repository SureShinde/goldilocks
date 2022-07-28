<?php

namespace Magenest\GoogleTagManager\Plugin\Quote;

use Magento\Quote\Model\Quote\Config as Subject;

class ConfigPlugin
{
    /**
     * @var \Magenest\GoogleTagManager\Helper\AttributeCodes
     */
    private $attributeCodes;

    public function __construct(
        \Magenest\GoogleTagManager\Helper\AttributeCodes $attributeCodes
    ) {
        $this->attributeCodes = $attributeCodes;
    }

    /**
     * Append product attribute keys, which are related to GTM, to a select for quote item collection
     *
     * @param Subject $subject
     * @param array $attributeKeys
     * @return array
     */
    public function afterGetProductAttributes(Subject $subject, array $attributeKeys)
    {
        $additionalAttributes = \array_keys($this->attributeCodes->getProductAttributeList());
        $customAttributes = \array_keys($this->attributeCodes->getCustomAttributeList());

        return \array_unique(
            \array_merge($attributeKeys, $additionalAttributes, $customAttributes)
        );
    }
}
