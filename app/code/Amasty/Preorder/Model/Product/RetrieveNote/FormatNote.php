<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\RetrieveNote;

use Amasty\Preorder\Model\Product\RetrieveNote\FormatNote\CustomResolverInterface;
use Amasty\Preorder\Model\Product\RetrieveNote\FormatNote\DefaultAttributeResolver;
use Magento\Catalog\Model\Product;

class FormatNote
{
    public const ATTRIBUTE_REGEXP = '/\{([^\{\}]+)\}/';

    /**
     * @var DefaultAttributeResolver
     */
    private $defaultAttributeResolver;

    /**
     * @var CustomResolverInterface[]
     */
    private $customResolverPool;

    public function __construct(DefaultAttributeResolver $defaultAttributeResolver, array $customResolverPool = [])
    {
        $this->defaultAttributeResolver = $defaultAttributeResolver;
        $this->customResolverPool = $customResolverPool;
    }

    public function execute(string $template, Product $product): string
    {
        return preg_replace_callback(
            self::ATTRIBUTE_REGEXP,
            function ($matches) use ($product) {
                return $this->replaceAttribute($matches, $product);
            },
            $template
        );
    }

    private function replaceAttribute(array $matches, Product $product): string
    {
        $attributeCode = $matches[1];
        if (isset($this->customResolverPool[$attributeCode])) {
            $result = $this->customResolverPool[$attributeCode]->execute($product);
        } else {
            $result = $this->defaultAttributeResolver->execute($product, $attributeCode);
        }

        return $result;
    }
}
