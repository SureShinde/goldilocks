<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\RetrieveNote\FormatNote;

use IntlDateFormatter;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DefaultAttributeResolver
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var ProductResource
     */
    private $productResource;

    public function __construct(TimezoneInterface $timezone, ProductResource $productResource)
    {
        $this->timezone = $timezone;
        $this->productResource = $productResource;
    }

    /**
     * @param ProductInterface $product
     * @param string $attributeCode
     * @return string
     */
    public function execute(ProductInterface $product, string $attributeCode): string
    {
        $attributes = $product->getAttributes();
        if (isset($attributes[$attributeCode])) {
            $value = $product->getData($attributeCode);
            if ($value === null) {
                $value = $this->productResource->getAttributeRawValue(
                    $product->getId(),
                    $attributeCode,
                    $product->getStoreId()
                );
                if (is_array($value)) {
                    $value = $value[$attributeCode] ?? null;
                }
            }

            /** @var Attribute $attribute */
            $attribute = $attributes[$attributeCode];
            if ($attribute->usesSource()) {
                $value = $attribute->getSource()->getOptionText($value);
            } elseif ($attribute->getFrontendInput() == 'date') {
                $value = $this->timezone->formatDate($value, IntlDateFormatter::MEDIUM, false);
            }
        }

        return $value ?? '';
    }
}
