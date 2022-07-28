<?php

namespace Acommerce\Gtm\Helper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    protected $_gtmOptions;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var  \Magento\Catalog\Helper\Category
     */
    protected $categoryHelper;

    /**
     * @var array
     */
    protected $storeCategories;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $resourceCategory;

    /**
     * @var \Magento\Framework\Escaper $escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Checkout\Model\Session\SuccessValidator
     */
    protected $checkoutSuccessValidator;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;


    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;


    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Magento\Catalog\Model\ResourceModel\Category $resourceCategory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Checkout\Model\Session\SuccessValidator $checkoutSuccessValidator
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\ResourceModel\Category $resourceCategory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Checkout\Model\Session\SuccessValidator $checkoutSuccessValidator,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        parent::__construct($context);
        $this->_gtmOptions = $this->scopeConfig->getValue('Acommerce_Gtm', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->blockFactory = $blockFactory;
        $this->registry = $registry;
        $this->categoryHelper = $categoryHelper;
        $this->resourceCategory = $resourceCategory;
        $this->escaper = $escaper;
        $this->storeCategories = [];
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->checkoutSuccessValidator = $checkoutSuccessValidator;
        $this->categoryFactory = $categoryFactory;
        $this->resource = $resource;
        $this->_populateStoreCategories();
    }


    private function _populateStoreCategories()
    {
        $categories = $this->categoryHelper->getStoreCategories(false, true);
        foreach ($categories as $categ) {
            $this->storeCategories[$categ->getData('entity_id')] = $categ->getData('name');
        }
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->_gtmOptions['general']['enable'];
    }

    /**
     * @return boolean
     */
    public function trackPromotions()
    {
        return $this->_gtmOptions['general']['promotion_tracking'];
    }

    /**
     * @return boolean
     */
    public function excludeTaxFromTransaction()
    {
        return $this->_gtmOptions['general']['exclude_tax_from_transaction'];
    }

    /**
     * @return boolean
     */
    public function excludeShippingFromTransaction()
    {
        return $this->_gtmOptions['general']['exclude_shipping_from_transaction'];
    }


    /**
     * @return int
     */
    public function getImpressionChunkSize()
    {
        return $this->_gtmOptions['general']['impression_chunk_size'];
    }

    /**
     * @return string
     */
    public function getGtmCodeSnippet()
    {
        return trim($this->_gtmOptions['general']['gtm_code']);
    }

    /**
     * @return string
     */
    public function getGtmNonJsCodeSnippet()
    {
        return trim($this->_gtmOptions['general']['gtm_nonjs_code']);
    }


    /**
     * @return string
     */
    public function getDataLayerScript()
    {
        $script = '';

        if (!($block = $this->createBlock('Core', 'datalayer.phtml'))) {
            return $script;
        }

        $this->addCategoryPageInformation();
        $this->addSearchResultPageInformation();
        $this->addProductPageInformation();
        $this->addCartPageInformation();
        $this->addCheckoutInformation();
        $this->addOrderInformation();
        $this->addShoppingCartInformation();

        $html = $block->toHtml();

        return $html;
    }

    /**
     * @param $blockName
     * @param $template
     * @return bool
     */
    protected function createBlock($blockName, $template)
    {
        if ($block = $this->blockFactory->createBlock('\Acommerce\Gtm\Block\\' . $blockName)
            ->setTemplate('Acommerce_Gtm::' . $template)
        ) {
            return $block;
        }

        return false;
    }

    /**
     * Returns the product id or sku based on the backend settings
     * In the case of a simple product that is a child of a configurable product
     * returns the id or sky of the parent product
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getGtmRootProductId($product)
    {
        $idOption = $this->_gtmOptions['general']['id_selection'];
        $gtmProductId = '';

        switch ($idOption) {
            case 'sku' :
                $gtmProductId = $product->getData('sku');
                break;
            case 'id' :
            default:
                $gtmProductId = $product->getData('sku');
                break;
        }

        return $gtmProductId;
    }

    /**
     * Returns the product id or sku based on the backend settings
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getGtmProductId($product)
    {
        $idOption = $this->_gtmOptions['general']['id_selection'];
        $gtmProductId = '';

        switch ($idOption) {
            case 'sku' :
                $gtmProductId = $product->getSku();
                break;
            case 'id' :
            default:
                $gtmProductId = $product->getSku();
                break;
        }

        return $gtmProductId;
    }

    /**
     * @return string
     */
    public function getAffiliationName()
    {
        return $this->storeManager->getWebsite()->getName() . ' - ' .
        $this->storeManager->getGroup()->getName() . ' - ' .
        $this->storeManager->getStore()->getName();
    }

    static function numberFormat($n, $n_decimals = 2)
    {
        return number_format($n, $n_decimals, ".", "");
    }

    /**
     * @param int $qty
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function addToCartPushData($qty, $product)
    {
        $result = [];

        $result['event'] = 'addToCart';
        $result['ecommerce'] = [];
        $result['ecommerce']['currencyCode'] = $this->getCurrencyCode();
        $result['ecommerce']['add'] = [];
        $result['ecommerce']['add']['products'] = [];

        $productData = [];
        $productData['name'] = html_entity_decode($product->getName());
        $productData['sku'] = $this->getGtmProductId($product);
        $productData['price'] = $this->numberFormat($product->getFinalPrice(), 2);
        if ($this->isBrandEnabled()) {
            $productData['brand'] = $this->getGtmBrand($product);
        }

        $productData['category'] = $this->getGtmCategoryByProduct($product);
        $productData['quantity'] = $qty;

        $result['ecommerce']['add']['products'][] = $productData;

        return $result;
    }

    /**
     * @param int $qty
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function removeFromCartPushData($qty, $product)
    {
        $result = [];

        $result['event'] = 'removeFromCart';
        $result['ecommerce'] = [];
        $result['ecommerce']['remove'] = [];
        $result['ecommerce']['remove']['products'] = [];

        $productData = [];
        $productData['name'] = html_entity_decode($product->getName());
        $productData['sku'] = $this->getGtmProductId($product);
        $productData['id'] = $this->getGtmProductId($product);
        $productData['price'] = $this->numberFormat($product->getFinalPrice(), 2);
        if ($this->isBrandEnabled()) {
            $productData['brand'] = $this->getGtmBrand($product);
        }

        $productData['category'] = $this->getGtmCategoryByProduct($product);
        $productData['quantity'] = $qty;

        $result['ecommerce']['remove']['products'][] = $productData;

        return $result;
    }


    /**
     * @param int $step
     * @param string $checkoutOption
     * @return array
     */
    public function addCheckoutStepPushData($step, $checkoutOption)
    {
        $result = [];

        $result['event'] = 'checkoutOption';
        $result['ecommerce'] = [];
        $result['ecommerce']['checkout_option'] = [];

        $optionData = [];
        $optionData['step'] = $step;
        $optionData['option'] = $checkoutOption;

        $result['ecommerce']['checkout_option']['actionField'] = $optionData;

        return $result;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }


    /**
     * Returns category tree path
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function getGtmCategory($category)
    {
        $categoryPath = $category->getData('path');

        return $this->_buildCategoryPath($categoryPath);
    }


    /**
     * Returns category tree path
     * @param array $categoryIds
     * @return string
     */
    public function getGtmCategoryFromCategoryIds($categoryIds)
    {
        if (!count($categoryIds)) {
            return '';
        }
        $categoryId = $categoryIds[0];
        $categoryPath = $this->resourceCategory->getCategoryPathById($categoryId);

        return $this->_buildCategoryPath($categoryPath);
    }


    /**
     * Returns product variant for product presents in cart
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getGtmCartProductVariant($product)
    {
        if (!$product) {
            return null;
        }

        $variant = null;
        $options = $product->getTypeInstance(true)->getOrderOptions($product);
        if (isset($options['attributes_info']) && !empty($options['attributes_info'])) {
            $variant = [];
            foreach ($options['attributes_info'] as $attributeInfo) {
                $variant[] = $attributeInfo['value'];
            }
            $variant = implode(',', $variant);
        }

        return $variant;
    }


    /**
     * Returns product variant for product present in order
     * @param Magento\Sales\Model\Order\Item $item
     * @return array
     */
    public function getGtmOrderProductVariant($item)
    {
        if (!$item) {
            return null;
        }

        $variant = null;
        $options = $item->getProductOptions();
        if (isset($options['attributes_info']) && !empty($options['attributes_info'])) {
            $variant = [];
            foreach ($options['attributes_info'] as $attributeInfo) {
                $variant[] = $attributeInfo['value'];
            }
            $variant = implode(',', $variant);
        }

        return $variant;
    }


    /**
     * @param string $categoryPath
     * @return string
     */
    private function _buildCategoryPath($categoryPath)
    {
        /* first 2 categories can be ignored */
        $categoriIds = array_slice(explode('/', $categoryPath), 2);
        $categoriesWithNames = array();

        foreach ($categoriIds as $categoriId) {
            if (isset($this->storeCategories[$categoriId])) {
                $categoriesWithNames[] = $this->storeCategories[$categoriId];
            }
        }

        return implode('/', $categoriesWithNames);
    }


    /**
     * @return boolean
     */
    public function isBrandEnabled()
    {
        return $this->_gtmOptions['general']['enable_brand'];
    }

    /**
     * @return boolean
     */
    public function isCustomDimensionCustomerIdEnabled()
    {
        return $this->_gtmOptions['general']['custom_dimension_customerid'];
    }

    /**
     * @return boolean
     */
    public function isCustomDimensionCustomerGroupEnabled()
    {
        return $this->_gtmOptions['general']['custom_dimension_customergroup'];
    }

    /**
     * @return boolean
     */
    public function isCustomDimensionStockStatusEnabled()
    {
        return $this->_gtmOptions['general']['custom_dimension_stockstatus'];
    }

    /**
     * @return boolean
     */
    public function isAdWordConversionTrackingEnabled()
    {
        return $this->_gtmOptions['adwords_conversion_tracking']['enable'];
    }


    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getGtmBrand($product)
    {
        $gtmBrand = '';
        if ($this->isBrandEnabled()) {
            $brandAttribute = $this->_gtmOptions['general']['brand_attribute'];

            try {
                $_value = $product->getResource()->getAttributeRawValue(
                    $product->getId(),
                    $brandAttribute,
                    \Magento\Store\Model\Store::DEFAULT_STORE_ID
                );

                if(!empty($_value)) {

                    $gtmBrand = $product
                    ->getResource()
                    ->getAttribute($brandAttribute)
                    ->getSource()
                    ->getOptionText(
                        $_value
                    );
                }
            } catch (\Exception $e) {
            }
        }
        return $gtmBrand;
    }


    /**
     * Set category page product impressions
     */
    public function addCategoryPageInformation()
    {
        $currentCategory = $this->getCurrentCategory();

        if (!empty($currentCategory)) {
            $categoryBlock = $this->createBlock('Category', 'category.phtml');

            if ($categoryBlock) {
                $categoryBlock->setCurrentCategory($currentCategory);
                $categoryBlock->toHtml();
            }
        }
    }


    /**
     * Set product page detail infromation
     */
    public function addProductPageInformation()
    {
        $currentProduct = $this->getCurrentProduct();

        if (!empty($currentProduct)) {
            $productBlock = $this->createBlock('Product', 'product.phtml');

            if ($productBlock) {
                $productBlock->setCurrentProduct($currentProduct);
                $productBlock->toHtml();
            }
        }
    }


    /**
     * Set purchase details
     */
    public function addOrderInformation()
    {
        $lastOrderId = $this->checkoutSession->getLastOrderId();
        $requestPath = $this->_request->getModuleName() .
            DIRECTORY_SEPARATOR . $this->_request->getControllerName() .
            DIRECTORY_SEPARATOR . $this->_request->getActionName();

        if ($requestPath != 'checkout/onepage/success' || !$lastOrderId) {
            return;
        }

        $orderBlock = $this->createBlock('Order', 'order.phtml');
        if ($orderBlock) {
            $order = $this->orderRepository->get($lastOrderId);
            $orderBlock->setOrder($order);
            $orderBlock->toHtml();
        }
    }


    /**
     * Set crossell productImpressions
     */
    public function addCartPageInformation()
    {
        $cartBlock = $this->createBlock('Cart', 'cart.phtml');

        if ($cartBlock) {
            $cartBlock->toHtml();
        }
    }


    public function addCheckoutInformation()
    {
        $requestPath = $this->_request->getModuleName() .
            DIRECTORY_SEPARATOR . $this->_request->getControllerName() .
            DIRECTORY_SEPARATOR . $this->_request->getActionName();

        if ($requestPath == 'checkout/index/index') {
            $checkoutBlock = $this->createBlock('Checkout', 'checkout.phtml');

            if ($checkoutBlock) {
                $quote = $this->checkoutSession->getQuote();
                $checkoutBlock->setQuote($quote);
                $checkoutBlock->toHtml();
            }
        }
    }

    public function addShoppingCartInformation()
    {
        $requestPath = $this->_request->getModuleName() .
            DIRECTORY_SEPARATOR . $this->_request->getControllerName() .
            DIRECTORY_SEPARATOR . $this->_request->getActionName();


        if ($requestPath == 'checkout/cart/index') {
            $shoppingCartBlock = $this->createBlock('ShoppingCart', 'shopping_cart.phtml');

            if ($shoppingCartBlock) {
                $quote = $this->checkoutSession->getQuote();
                $shoppingCartBlock->setQuote($quote);
                $shoppingCartBlock->toHtml();
            }
        }

    }


    /**
     * Set search result page product impressions
     */
    public function addSearchResultPageInformation()
    {
        $moduleName = $this->_request->getModuleName();
        $controllerName = $this->_request->getControllerName();
        $listPrefix = '';
        if ($controllerName == 'advanced') {
            $listPrefix = __('Advanced');
        }

        if ($moduleName == 'catalogsearch') {
            $searchBlock = $this->createBlock('Search', 'search.phtml');
            if ($searchBlock) {
                $searchBlock->setListPrefix($listPrefix);
                return $searchBlock->toHtml();
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function addProductClick($product, $index = 0, $list = '')
    {
        $productClickBlock = $this->createBlock('Core', 'product_click.phtml');
        $html = '';

        if ($productClickBlock) {
            $productClickBlock->setProduct($product);
            $productClickBlock->setIndex($index);

            /**
             * If a list value is set use that one, if nothing add one
             */
            if (!$list) {
                $currentCategory = $this->getCurrentCategory();
                if (!empty($currentCategory)) {
                    $list = $this->getGtmCategory($currentCategory);
                } else {
                    /* Check if it is from a listing from search or advanced search*/
                    $requestPath = $this->_request->getModuleName() .
                        DIRECTORY_SEPARATOR . $this->_request->getControllerName() .
                        DIRECTORY_SEPARATOR . $this->_request->getActionName();
                    switch ($requestPath) {
                        case 'catalogsearch/advanced/result':
                            $list = __('Advanced Search Listing');
                            break;
                        case 'catalogsearch/result/index':
                            $list = __('Search Listing');
                            break;
                    }
                }
            }
            $productClickBlock->setList($list);
            $html = trim($productClickBlock->toHtml());
        }

        if (!empty($html)) {
            $eventCallBack = ", 'eventCallback': function() { document.location = '" .
                $this->escaper->escapeHtml($product->getUrlModel()->getUrl($product)) . "' }});";
            $html = substr(rtrim($html, ");"), 0, -1);
            $html .= $eventCallBack;
            $html = 'onclick="' . $html . '"';
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getDimensionsActionUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl() . 'weltpixel_gtm/index/dimensions';
    }

    /**
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return mixed
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * @return mixed
     */
    public function getProductPrice($product)
    {
        $price = 0;
        try {
            $price = $product->getPriceInfo()->getPrice('final_price')->getValue();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $price = 0;
        }
        return $price;
    }

    /**
     * @return string
     */
    public function getGtmCategoryByProduct($product) {
        $categories = $product->getCategoryCollection();
        $path = '';
        $level = 0;
        $categoryName = array();

        if(count($categories) > 0) {
            foreach ($categories as $category) {
                if($category->getLevel() == 4) {
                    $path = $category->getPath();
                    $level = $category->getLevel();
                    break;
                } else {
                    if($category->getLevel() > $level) {
                        $path = $category->getPath();
                        $level = $category->getLevel();
                    }
                }
            }
        }

        if(!empty($path)) {

            $categories = explode('/', $path);
            array_shift($categories);
            array_shift($categories);
            foreach ($categories as $categoryId) {
                $categoryName[] = $this->getCategoryName($categoryId);
            }
        }
        return implode('/', $categoryName);
    }

    public function getCategoryName($categoryId)
    {
        $conn = $this->resource->getConnection();

        $select = $conn->select()
            ->from(
                ['o' => 'catalog_category_entity_varchar']
            )
            ->where('o.attribute_id=?', '45')
            ->where('o.store_id=?', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            ->where('o.entity_id=?', $categoryId);

         $data = $conn->fetchRow($select);
         return $data['value'];

    }

    public function getProductVariant($product) {

        $product = $product;
        $attributes = $product->getAttributes();

        $data = array();
        $colorAttributes = array();
        $sizeAttributes = array();

        foreach ($attributes as $attribute) {
            $_code = $attribute->getAttributeCode();

            if (strpos($_code, 'color') !== false || strpos($_code, 'size') !== false) {

                $_value = $product->getResource()->getAttributeRawValue(
                    $product->getId(),
                    $_code,
                    \Magento\Store\Model\Store::DEFAULT_STORE_ID
                );

                if(!empty($_value)) {

                    if (strpos($_code, 'color') !== false) {
                        $colorAttributes[] = $_code;
                    }

                    if (strpos($_code, 'size') !== false) {
                        $sizeAttributes[] = $_code;
                    }

                    $data[$_code] = $product
                    ->getResource()
                    ->getAttribute($_code)
                    ->getSource()
                    ->getOptionText(
                        $_value
                    );
                }
            }
        }

        $variant = array();
        if(count($colorAttributes) > 0) {
            foreach ($colorAttributes as $key => $colorAttribute) {
                $variant[] = $data[$colorAttribute];
            }
        }
        if(count($sizeAttributes) > 0) {
            foreach ($sizeAttributes as $key => $sizeAttribute) {
                $variant[] = $data[$sizeAttribute];
            }
        }
        return (count($variant) == 0) ? '' : implode(',', $variant);
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

}
