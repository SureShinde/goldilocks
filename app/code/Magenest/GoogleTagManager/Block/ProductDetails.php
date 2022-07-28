<?php

namespace Magenest\GoogleTagManager\Block;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magenest\GoogleTagManager\Api\ProductObjectGeneratorInterface;
use Magenest\GoogleTagManager\Helper\AttributeCodes;
use Magenest\GoogleTagManager\Helper\Data as gtmHelper;
use Magenest\GoogleTagManager\Logger;

class ProductDetails extends AbstractGtmBlock
{
    const LIST_NAME = 'Product Page';
    const RELATED_PRODUCT_LIST_NAME = 'Related Products';
    const UP_SELL_PRODUCT_LIST_NAME = 'Up-sell';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Resolver
     */
    private $catalogLayerResolver;

    /**
     * @var gtmHelper
     */
    private $gtmHelper;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $jsonHelper
     * @param Resolver $catalogLayerResolver
     * @param AttributeCodes $attributeCodes
     * @param gtmHelper $gtmHelper
     * @param Logger $logger
     * @param ProductObjectGeneratorInterface $productObjectGenerator
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $jsonHelper,
        Resolver $catalogLayerResolver,
        AttributeCodes $attributeCodes,
        gtmHelper $gtmHelper,
        Logger $logger,
        ProductObjectGeneratorInterface $productObjectGenerator,
        array $data = []
    ) {
        parent::__construct($context, $attributeCodes, $jsonHelper, $productObjectGenerator, $data);

        $this->registry = $registry;
        $this->catalogLayerResolver = $catalogLayerResolver;
        $this->gtmHelper = $gtmHelper;
        $this->logger = $logger;
    }

    /**
     * Rewrite to disable content if needed.
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _toHtml()
    {
        if (!$this->gtmHelper->isEnabled()) {
            return '';
        }

        if (!$this->getCurrentProduct()) {
            $currentUrl = $this->_storeManager->getStore()->getCurrentUrl();

            $this->logger->warning(
                \sprintf('Unable to identify product for GoogleTagManager, url %s', $currentUrl)
            );

            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Retrieve current product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * Get Google tag manager product page list name
     */
    public function getListName()
    {
        return self::LIST_NAME;
    }

    /**
     * Retrieve category name or list name, good for when neither is available, like on start page etc
     */
    public function getCategoryNameOrListName()
    {
        $categoryName = $this->getCategoryName();

        return $categoryName ?: self::LIST_NAME;
    }

    /**
     * Somewhat similar to Magento\GoogleTagManager\Block\ListJson::getCurrentCategory in Enterprise
     */
    public function getCategoryName()
    {
        $catalogLayer = $this->catalogLayerResolver->get();

        if ($catalogLayer) {
            return $catalogLayer->getCurrentCategory()->getName();
        }

        $currentCategory = $this->registry->registry('current_category');

        if ($currentCategory) {
            return $currentCategory->getName();
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return [$this->getCurrentProduct()];
    }

    /**
     * @inheritdoc
     */
    public function getBasicItemAttributes($object)
    {
        $basicAttributes = parent::getBasicItemAttributes($object);
        $basicAttributes['category'] = $this->getCategoryNameOrListName();

        return $basicAttributes;
    }

    /**
     * Return impression list for related and up-sell products
     *
     * @return array
     */
    private function getImpressions()
    {
        $impressions = [];

        try {
            $currentProduct = $this->getCurrentProduct();

            $relatedProducts = $currentProduct->getRelatedProductCollection()->addAttributeToSelect('*');

            foreach ($relatedProducts->getItems() as $product) {
                $productData = $this->getProductDetails($product);
                $productData['list'] = self::RELATED_PRODUCT_LIST_NAME;
                $productData['position'] = (int)$product->getPosition();

                $impressions[] = $productData;
            }

            $upSellProducts = $currentProduct->getUpSellProductCollection()->addAttributeToSelect('*');

            foreach ($upSellProducts->getItems() as $product) {
                $productData = $this->getProductDetails($product);
                $productData['list'] = self::UP_SELL_PRODUCT_LIST_NAME;
                $productData['position'] = (int)$product->getPosition();

                $impressions[] = $productData;
            }
            // phpcs:ignore VCQP.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (\Exception $exception) {
            // Do nothing
        }

        return $impressions;
    }

    /**
     * Return related and up sell products array
     *
     * @return array
     */
    private function getProductImpressions()
    {
        return $this->gtmHelper->isEnabledProductImpressions() ? $this->getImpressions() : [];
    }

    /**
     * Get GTM Data for product page
     *
     * @return bool|string
     */
    public function getGtmJsonConfig()
    {
        $config['gtmProductDetail'] = [
            'products' => $this->getProducts(),
        ];

        $config['gtmImpressions'] = $this->getProductImpressions() ?: null;

        return $this->jsonEncode($config);
    }
}
