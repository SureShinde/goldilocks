<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Block\InventoryProductAlert\Adminhtml\Product\Edit\Tab\Alerts;

/**
 * Product stock alerts tab plugin
 */
class Stock extends \Ecombricks\Common\Plugin\Block\Backend\Widget\Grid\Extended
{

    /**
     * Get stock by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockByStoreInterface
     */
    protected $getStockByStore;

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Object\WrapperFactory $wrapperFactory
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockByStoreInterface $getStockByStore
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockByStoreInterface $getStockByStore
    )
    {
        parent::__construct($wrapperFactory);
        $this->getStockByStore = $getStockByStore;
    }

    /**
     * Prepare columns
     *
     * @return $this
     */
    protected function prepareColumns()
    {
        $subject = $this->getSubject();
        $this->invokeSubjectParentMethod(\Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Stock::class, '_prepareColumns');
        $subject->addColumn(
            'store_name', [
                'header' => __('Store'),
                'index' => 'store_name',
            ]
        );
        $subject->addColumn(
            'stock_name',
            [
                'header' => __('Stock'),
                'index' => 'stock_name',
            ]
        );
        return $this;
    }

    /**
     * After load collection
     *
     * @return $this
     */
    protected function afterLoadCollection()
    {
        $subject = $this->getSubject();
        $storeManager = $this->getSubjectPropertyValue('_storeManager');
        $this->invokeSubjectParentMethod(\Magento\Backend\Block\Widget\Grid\Extended::class, '_afterLoadCollection');
        foreach ($subject->getCollection()->getItems() as $item) {
            $store = $storeManager->getStore($item->getStoreId());
            $stock = $this->getStockByStore->execute($store);
            $item->setStoreName($store->getName());
            $item->setStockName($stock->getName());
        }
        return $this;
    }

}
