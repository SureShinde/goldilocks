<?php

namespace Magenest\GoogleTagManager\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Store\Model\ScopeInterface;
use Magenest\GoogleTagManager\Block\Adminhtml\Form\Field\Product\Attributes;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class AttributeCodes
{
    const XML_PATH_PRODUCT_ATTRIBUTES = 'universalanalytics/product/product_attributes';
    const XML_PATH_CUSTOM_ATTRIBUTES = 'universalanalytics/product/custom_attributes';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $mathRandom;

    /**
     * @var array|null
     */
    private $productAttributeList;

    /**
     * @var array|null
     */
    private $productCustomAttributeList;

    private $serializer;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Math\Random $mathRandom,
        Serialize $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->mathRandom = $mathRandom;
        $this->serializer = $serializer;
    }

    /**
     * Get valid attribute code (key)
     *
     * @param string $attribute
     * @return string
     * @throws LocalizedException
     */
    public function getValidAttributeCode($attribute)
    {
        $attribute = $this->prepareValue($attribute);
        if (empty($attribute)) {
            throw new LocalizedException(\__('Attribute can not be empty.'));
        }

        return $attribute;
    }

    /**
     * Prepare input parameter
     *
     * @param string $value
     * @return string
     */
    private function prepareValue($value)
    {
        return \trim($value);
    }

    /**
     * Check if an array only has unique values
     *
     * @param mixed[] $array
     * @return bool
     */
    public function hasUniqueValues($array)
    {
        return (\count($array) === \count(\array_unique($array)));
    }

    /**
     * Generate a storable representation of a value
     *
     * @param array $value
     * @return string
     * @throws LocalizedException
     */
    public function serializeValue($value)
    {
        $serializedValue = '';
        if (\is_array($value)) {
            $data = [];
            foreach ($value as $mainColumnValue => $aliasColumnValue) {
                $data[$this->getValidAttributeCode($mainColumnValue)] = $this->prepareValue($aliasColumnValue);
            }
            $serializedValue = $this->serializer->serialize($data); // phpcs:ignore Magento2.Security.InsecureFunction.DiscouragedWithAlternative
        }

        return $serializedValue;
    }

    /**
     * Return array from a serialized string
     *
     * @param string|mixed $value
     * @return array|mixed
     */
    public function unserializeValue($value)
    {
        return (\is_string($value) && !empty($value))
            ? $this->serializer->unserialize($value) // phpcs:ignore Magento2.Security.InsecureFunction.DiscouragedWithAlternative
            : [];
    }

    /**
     * Check if array fields are encoded
     *
     * @param array $value
     * @return bool
     */
    public function isEncodedArrayFieldValue($value)
    {
        $isEncoded = true;
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!\is_array($row)
                || !\array_key_exists(Attributes::MAIN_COLUMN_ID, $row)
                || !\array_key_exists(Attributes::ALIAS_COLUMN_ID, $row)
            ) {
                $isEncoded = false;
                break;
            }
        }

        return $isEncoded;
    }

    /**
     * Encode array values to be used in \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array $value
     * @return array
     * @throws LocalizedException
     */
    public function encodeArrayFieldValue(array $value)
    {
        $result = [];
        foreach ($value as $mainColumnValue => $aliasColumnValue) {
            $resultId = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = [
                Attributes::MAIN_COLUMN_ID  => $this->getValidAttributeCode($mainColumnValue),
                Attributes::ALIAS_COLUMN_ID => $this->prepareValue($aliasColumnValue),
            ];
        }

        return $result;
    }

    /**
     * Decode array values
     *
     * @param array $value
     * @return array
     * @throws LocalizedException
     */
    public function decodeArrayFieldValue(array $value)
    {
        $result = [];

        foreach ($value as $row) {
            if (!\is_array($row)
                || !\array_key_exists(Attributes::MAIN_COLUMN_ID, $row)
                || !\array_key_exists(Attributes::ALIAS_COLUMN_ID, $row)
            ) {
                continue;
            }

            $attribute = $this->getValidAttributeCode($row[Attributes::MAIN_COLUMN_ID]);
            $alias = $this->prepareValue($row[Attributes::ALIAS_COLUMN_ID]);
            $result[$attribute] = $alias;
        }

        return $result;
    }

    /**
     * Make value readable by \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param string $value
     * @return array|mixed
     * @throws LocalizedException
     */
    public function makeArrayFieldValue($value)
    {
        $value = $this->unserializeValue($value);

        if (!$this->isEncodedArrayFieldValue($value)) {
            $value = $this->encodeArrayFieldValue($value);
        }

        return $value;
    }

    /**
     * Make value ready to be stored
     *
     * @param array $value
     * @return string
     * @throws LocalizedException
     */
    public function makeStorableArrayFieldValue($value)
    {
        if ($this->isEncodedArrayFieldValue($value)) {
            $value = $this->decodeArrayFieldValue($value);
        }

        $value = $this->serializeValue($value);

        return $value;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $attributeList
     * @param bool $processFrontendValue
     * @return array
     */
    public function extractProductData($product, $attributeList, $processFrontendValue = false)
    {
        $result = [];
        $productResource = $product->getResource();

        foreach ($attributeList as $attribute => $alias) {
            if ($product->getData($attribute) === null) {
                continue;
            }

            $attributeValue = $processFrontendValue
                ? $productResource->getAttribute($attribute)->getFrontend()->getValue($product)
                : $product->getData($attribute);

            $result[$alias ?: $attribute] = $attributeValue;
        }

        return $result;
    }

    /**
     * Retrieve list of product attributes for analytics
     *
     * @return array
     */
    public function getProductAttributeList()
    {
        if ($this->productAttributeList === null) {
            $rawValue = $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_ATTRIBUTES, ScopeInterface::SCOPE_STORE);
            $this->productAttributeList = $this->unserializeValue($rawValue);
        }

        return $this->productAttributeList;
    }

    /**
     * Retrieve list of custom product attributes or non-attribute data for analytics
     *
     * @return array
     */
    public function getCustomAttributeList()
    {
        if ($this->productCustomAttributeList === null) {
            $rawValue = $this->scopeConfig->getValue(
                self::XML_PATH_CUSTOM_ATTRIBUTES,
                ScopeInterface::SCOPE_STORE
            );

            $this->productCustomAttributeList = $this->unserializeValue($rawValue);
        }

        return $this->productCustomAttributeList;
    }

    /**
     * Retrieve product attributes for analytics
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getProductAttributes($product)
    {
        return $this->extractProductData($product, $this->getProductAttributeList(), true);
    }

    /**
     * Retrieve custom product attributes or non-attribute data for analytics
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getCustomAttributes($product)
    {
        return $this->extractProductData($product, $this->getCustomAttributeList());
    }
}
