<?php
namespace Acommerce\Gtm\Block;

/**
 * Class \Acommerce\Gtm\Block\Cart
 */
class Cart extends \Acommerce\Gtm\Block\Core
{
    /**
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection|null
     */
    public function getCrosselProductCollection()
    {
        /** @var \Magento\Checkout\Block\Cart\Crosssell $crosselProductListBlock */
        $crosselProductListBlock = $this->_layout->getBlock('checkout.cart.crosssell');

        if (empty($crosselProductListBlock)) {
            return null;
        }

        $collection = $crosselProductListBlock->getItems();
        return $collection;
    }
}