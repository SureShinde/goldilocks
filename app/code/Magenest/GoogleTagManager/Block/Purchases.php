<?php

namespace Magenest\GoogleTagManager\Block;

use Magenest\GoogleTagManager\Api\ProductObjectGeneratorInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Purchases extends AbstractGtmBlock
{
    const SUCCESS_PAGE_KEY = 'success';
    const LIST_NAME = 'Default Category';

    /**
     * @var \Magenest\GoogleTagManager\Api\OrderDetailsGeneratorInterface
     */
    private $orderDetailsGenerator;

    /**
     * @var \Magenest\GoogleTagManager\Model\Checkout
     */
    private $checkout;

    /**
     * @var \Magenest\GoogleTagManager\Block\Checkout
     */
    private $checkoutBlock;

    /**

     * @var \Magenest\GoogleTagManager\Helper\Data
     */
    private $gtmHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $catCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magenest\GoogleTagManager\Api\OrderDetailsGeneratorInterface $orderDetailsGenerator
     * @param \Magenest\GoogleTagManager\Model\Checkout $checkout
     * @param \Magenest\GoogleTagManager\Block\Checkout $checkoutBlock
     * @param \Magenest\GoogleTagManager\Helper\AttributeCodes $attributeCodes
     * @param \Magenest\GoogleTagManager\Helper\Data $gtmHelper
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $catCollectionFactory
     * @param ProductObjectGeneratorInterface $productObjectGenerator
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magenest\GoogleTagManager\Api\OrderDetailsGeneratorInterface $orderDetailsGenerator,
        \Magenest\GoogleTagManager\Model\Checkout $checkout,
        \Magenest\GoogleTagManager\Block\Checkout $checkoutBlock,
        \Magenest\GoogleTagManager\Helper\AttributeCodes $attributeCodes,
        \Magenest\GoogleTagManager\Helper\Data $gtmHelper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $catCollectionFactory,
        ProductObjectGeneratorInterface $productObjectGenerator,
        array $data = []
    ) {
        parent::__construct($context, $attributeCodes, $jsonHelper, $productObjectGenerator, $data);

        $this->orderDetailsGenerator = $orderDetailsGenerator;
        $this->checkout = $checkout;
        $this->checkoutBlock = $checkoutBlock;
        $this->catCollectionFactory = $catCollectionFactory;

        $this->gtmHelper = $gtmHelper;

        $this->_isScopePrivate = true;
    }

    protected function _toHtml()
    {
        if (!$this->gtmHelper->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    public function getOrder()
    {
        return $this->checkout->getOrder();
    }

    public function getRevenue($order)
    {
        return $this->checkout->getRevenue($order);
    }

    public function getShipping($order)
    {
        return $this->checkout->getShipping($order);
    }

    public function getItems()
    {
        return $this->getOrder()->getAllVisibleItems();
    }

    public function getPrice($item)
    {
        return $this->gtmHelper->getFormattedPrice(
            $this->checkout->getProductPrice($item)
        );
    }

    public function getCheckoutStep()
    {
        $checkoutSteps = $this->checkoutBlock->getCheckoutSteps();

        return $checkoutSteps[self::SUCCESS_PAGE_KEY] ?? null;
    }

    public function hasCheckoutSteps()
    {
        return !empty($this->checkoutBlock->getCheckoutSteps());
    }

    public function getOrderDetails()
    {
        return $this->orderDetailsGenerator->generate($this->getOrder());
    }

    public function getBasicItemAttributes($object)
    {
        $basicAttributes = parent::getBasicItemAttributes($object);

        $basicAttributes['price'] = $this->getPrice($object);
        $basicAttributes['quantity'] = $object->getQtyOrdered();

        if ($this->gtmHelper->isCategoryNameOnPurchaseEnabled()) {
            $product = $object->getProduct() ?: $object;
            $basicAttributes['category'] = $this->getCategoryNameOrListName($product);
        }

        return $basicAttributes;
    }

    /**
     * Retrieve category name or list name, good for when neither is available, like on start page etc
     *
     * @param \Magento\Framework\DataObject $product
     * @return string|null
     */
    public function getCategoryNameOrListName($product)
    {
        return $this->getCategoryName($product)
            ?: self::LIST_NAME;
    }

    /**
     * Get product lowest level category name.
     *
     * @param \Magento\Framework\DataObject $product
     * @return string
     */
    public function getCategoryName($product)
    {
        if (!$product) {
            return null;
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection */
        $categoryCollection = $this->catCollectionFactory->create();

        $categoryCollection
            ->addFieldToSelect('name')
            ->addFieldToSelect('path')
            ->addFieldToFilter('entity_id', ['in' => $product->getCategoryIds()])
            ->setOrder('level', 'ASC')
            ->setPageSize(1);

        $lowestCategory = $categoryCollection->getFirstItem(); // phpcs:ignore Ecg.Performance.GetFirstItem.Found -- pagesize defined

        if (!$lowestCategory->getId()) {
            return null;
        }

        return $lowestCategory->getName();
    }
}
