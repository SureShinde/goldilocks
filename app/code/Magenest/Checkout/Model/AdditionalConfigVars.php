<?php

namespace Magenest\Checkout\Model;

use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class AdditionalConfigVars implements ConfigProviderInterface
{
    private \Magento\Checkout\Model\Session $_checkoutSession;
    private \Magento\Catalog\Model\ProductRepository $productRepository;

    /**
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->_checkoutSession = $_checkoutSession;
        $this->productRepository = $productRepository;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $items = $this->_checkoutSession->getQuote()->getItems();
        $result = [];
        foreach ($items as $item) {
            $product = $item->getProduct();
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $product = $this->productRepository->get($item->getSku());
            }
            $oldPrice = $product
                ->getPriceInfo()
                ->getPrice(RegularPrice::PRICE_CODE)
                ->getAmount()->getValue();
            $result['itemData'][$item->getItemId()]['originPrice'] = $oldPrice * $item->getQty();
            $result['itemData'][$item->getItemId()]['showOldPrice'] = $product->getPrice() != $item->getPrice();
        }
        return $result;
    }
}
