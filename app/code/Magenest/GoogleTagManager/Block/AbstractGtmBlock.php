<?php

namespace Magenest\GoogleTagManager\Block;

use Magenest\GoogleTagManager\Api\ProductObjectGeneratorInterface;

/**
 * Used to build GTM blocks for different views
 */
abstract class AbstractGtmBlock extends \Magento\Framework\View\Element\Template implements EnhancedEcommerceInterface
{
    /**
     * @var \Magenest\GoogleTagManager\Helper\AttributeCodes
     */
    private $attributeCodes;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var ProductObjectGeneratorInterface
     */
    private $productObjectGenerator;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magenest\GoogleTagManager\Helper\AttributeCodes $attributeCodes
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param ProductObjectGeneratorInterface $productObjectGenerator
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magenest\GoogleTagManager\Helper\AttributeCodes $attributeCodes,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        ProductObjectGeneratorInterface $productObjectGenerator,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->attributeCodes = $attributeCodes;
        $this->jsonHelper = $jsonHelper;
        $this->productObjectGenerator = $productObjectGenerator;
    }

    /**
     * @inheritdoc
     */
    public function getProducts()
    {
        $products = [];

        foreach ($this->getItems() as $item) {
            $products[] = $this->getProductDetails($item);
        }

        return $products;
    }

    /**
     * @inheritdoc
     */
    public function getProductDetails($object)
    {
        $product = $object->getProduct() ?: $object;

        $data = \array_merge(
            $this->getBasicItemAttributes($object),
            $this->getProductAttributes($product),
            $this->getCustomAttributes($product)
        );

        return $this->productObjectGenerator->generate($object, $data);
    }

    /**
     * @inheritdoc
     */
    public function getBasicItemAttributes($object)
    {
        return [
            'name' => $object->getName(),
            'id'   => $object->getSku(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getProductAttributes($product)
    {
        return $this->attributeCodes->getProductAttributes($product);
    }

    /**
     * @inheritdoc
     */
    public function getCustomAttributes($product)
    {
        return $this->attributeCodes->getCustomAttributes($product);
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @return string
     */
    public function jsonEncode($valueToEncode)
    {
        return $this->jsonHelper->jsonEncode($valueToEncode);
    }

    /**
     * Backward compatibility for escapeJs for older than 100.2.0 framework version
     *
     * @param string $string
     * @return string
     */
    public function escapeJs($string)
    {
        if (\method_exists(parent::class, 'escapeJs')) {
            return parent::escapeJs($string);
        }

        if ($string === '' || \ctype_digit($string)) {
            return $string;
        }

        return \preg_replace_callback(
            '/[^a-z0-9,\._]/iSu',
            static function ($matches) {
                $chr = $matches[0];

                if (\strlen($chr) !== 1) {
                    $chr = \mb_convert_encoding($chr, 'UTF-16BE', 'UTF-8');
                    $chr = ($chr === false) ? '' : $chr;
                }

                return \sprintf('\\u%04s', \strtoupper(\bin2hex($chr)));
            },
            $string
        );
    }
}
