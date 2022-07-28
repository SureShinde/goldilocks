<?php

namespace Acommerce\All\Helper;

use Magento\Framework\App\Helper\Context;
use \Magento\CatalogInventory\Model\Stock\Item;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;

    protected $stockItem;

    public function __construct(Context $context, Item $stockItem)
    {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->stockItem = $stockItem;
        parent::__construct($context);
    }

    public function getMaxSaleQty($product = null)
    {
        if($product){
            $stockItem = $this->stockItem->load($product->getId(), 'product_id');
            if($stockItem->getUseConfigMaxSaleQty()){
                return $this->_scopeConfig->getValue(
                    'cataloginventory/item_options/max_sale_qty',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            }else{
                return $stockItem->getMaxSaleQty();
            }
        }else{
            return $this->_scopeConfig->getValue(
                'cataloginventory/item_options/max_sale_qty',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

    }
}