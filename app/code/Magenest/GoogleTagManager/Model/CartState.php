<?php

namespace Magenest\GoogleTagManager\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote\Item;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class CartState
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableProduct;

    /**
     * @var \Magenest\GoogleTagManager\Model\RequestInfo\ValueExtractor
     */
    private $requestInfoValueExtractor;

    /**
     * @var \Magenest\GoogleTagManager\Helper\CatalogSession
     */
    private $sessionHelper;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct,
        \Magenest\GoogleTagManager\Model\RequestInfo\ValueExtractor $requestInfoValueExtractor,
        \Magenest\GoogleTagManager\Helper\CatalogSession $sessionHelper
    ) {
        $this->productRepository = $productRepository;
        $this->configurableProduct = $configurableProduct;
        $this->requestInfoValueExtractor = $requestInfoValueExtractor;
        $this->sessionHelper = $sessionHelper;
    }

    /**
     * @param ProductInterface|int $productInfo
     * @param array|mixed $requestInfo
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @deprecated use registerQuoteItem instead to notify about added products
     */
    public function registerProduct($productInfo, $requestInfo)
    {
        $product = $productInfo instanceof \Magento\Catalog\Api\Data\ProductInterface
            ? $productInfo
            : $this->productRepository->getById($productInfo);

        $qty = $this->requestInfoValueExtractor->getQuantity($requestInfo);
        $attributes = $this->requestInfoValueExtractor->getSuperAttributes($requestInfo);

        if (!empty($attributes) && $product->getTypeId() === Configurable::TYPE_CODE) {
            $childProduct = $this->configurableProduct->getProductByAttributes($attributes, $product);

            $this->sessionHelper->addProduct($childProduct ?? $product, $product->getName(), $qty);
        } else {
            $this->sessionHelper->addProduct($product, null, $qty);
        }
    }

    /**
     * Register quote item as added to the cart
     *
     * @param Item $item
     */
    public function registerQuoteItem(Item $item)
    {
        $previousQty = $item->getOrigData(Item::KEY_QTY);
        $qty = $item->getQty();

        // subtract existing qty from the item instead of reporting the new qty
        if ($previousQty) {
            $qty -= (float)$previousQty;
        }

        if ($qty > 0) {
            $this->sessionHelper->addItem($item, $qty);
        } else {
            $this->sessionHelper->removeItem($item, \abs($qty));
        }
    }
}
