<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Plugin\Model\Store\ResourceModel;

/**
 * Store resource plugin
 */
class Store
{

    /**
     * Stock repository
     *
     * @var \Magento\InventoryApi\Api\StockRepositoryInterface
     */
    protected $stockRepository;

    /**
     * Get default stock
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface
     */
    protected $getDefaultStock;

    /**
     * Get store code by store ID
     *
     * @var \Ecombricks\StoreInventory\Model\InventorySales\ResourceModel\GetStoreCodeByStoreId
     */
    protected $getStoreCodeByStoreId;

    /**
     * Store sales channel factory
     *
     * @var \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory
     */
    protected $storeSalesChannelFactory;

    /**
     * Get stock ID by store
     *
     * @var \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface
     */
    protected $getStockIdByStore;

    /**
     * Update sales channel store code
     *
     * @var \Ecombricks\StoreInventory\Model\InventorySales\ResourceModel\UpdateSalesChannelStoreCode
     */
    protected $updateSalesChannelStoreCode;

    /**
     * Delete sales channel
     *
     * @var \Magento\InventorySalesApi\Model\DeleteSalesChannelToStockLinkInterface
     */
    protected $deleteSalesChannel;

    /**
     * Constructor
     *
     * @param \Magento\InventoryApi\Api\StockRepositoryInterface $stockRepository
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface $getDefaultStock
     * @param \Ecombricks\StoreInventory\Model\InventorySales\ResourceModel\GetStoreCodeByStoreId $getStoreCodeByStoreId
     * @param \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory
     * @param \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore
     * @param \Ecombricks\StoreInventory\Model\InventorySales\ResourceModel\UpdateSalesChannelStoreCode $updateSalesChannelStoreCode
     * @param \Magento\InventorySalesApi\Model\DeleteSalesChannelToStockLinkInterface $deleteSalesChannel
     * @return void
     */
    public function __construct(
        \Magento\InventoryApi\Api\StockRepositoryInterface $stockRepository,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetDefaultStockInterface $getDefaultStock,
        \Ecombricks\StoreInventory\Model\InventorySales\ResourceModel\GetStoreCodeByStoreId $getStoreCodeByStoreId,
        \Ecombricks\StoreInventory\Model\InventorySales\StoreSalesChannelFactory $storeSalesChannelFactory,
        \Ecombricks\StoreInventory\Api\InventorySalesApi\GetStockIdByStoreInterface $getStockIdByStore,
        \Ecombricks\StoreInventory\Model\InventorySales\ResourceModel\UpdateSalesChannelStoreCode $updateSalesChannelStoreCode,
        \Magento\InventorySalesApi\Model\DeleteSalesChannelToStockLinkInterface $deleteSalesChannel
    )
    {
        $this->stockRepository = $stockRepository;
        $this->getDefaultStock = $getDefaultStock;
        $this->getStoreCodeByStoreId = $getStoreCodeByStoreId;
        $this->storeSalesChannelFactory = $storeSalesChannelFactory;
        $this->getStockIdByStore = $getStockIdByStore;
        $this->updateSalesChannelStoreCode = $updateSalesChannelStoreCode;
        $this->deleteSalesChannel = $deleteSalesChannel;
    }

    /**
     * Around save
     *
     * @param \Magento\Store\Model\ResourceModel\Store $subject
     * @param \Closure $proceed
     * @param \Magento\Store\Model\Store|\Magento\Framework\Model\AbstractModel $store
     * @return \Magento\Store\Model\ResourceModel\Store
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function aroundSave(
        \Magento\Store\Model\ResourceModel\Store $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $store
    )
    {
        $storeCode = $store->getCode();
        $oldStoreCode = null;
        if (null !== $store->getId()) {
            $oldStoreCode = $this->getStoreCodeByStoreId->execute((int) $store->getId());
        }
        $proceed($store);
        if (
            ($oldStoreCode !== null) &&
            ($oldStoreCode !== \Magento\Store\Model\Store::ADMIN_CODE) &&
            ($oldStoreCode !== $storeCode)
        ) {
            $this->updateSalesChannelStoreCode->execute($oldStoreCode, $storeCode);
        }
        if ($storeCode === \Magento\Store\Model\Store::ADMIN_CODE || $this->getStockIdByStore->execute($store) !== null) {
            return $subject;
        }
        $defaultStock = $this->getDefaultStock->execute();
        $defaultStockExtension = $defaultStock->getExtensionAttributes();
        $salesChannels = $defaultStockExtension->getSalesChannels();
        $salesChannels[] = $this->storeSalesChannelFactory->createByStore($store);
        $defaultStockExtension->setSalesChannels($salesChannels);
        $this->stockRepository->save($defaultStock);
        return $subject;
    }

    /**
     * After delete
     *
     * @param \Magento\Store\Model\ResourceModel\Store $subject
     * @param \Magento\Store\Model\ResourceModel\Store $result
     * @param \Magento\Store\Model\Store|\Magento\Framework\Model\AbstractModel $store
     * @return \Magento\Store\Model\ResourceModel\Store
     */
    public function afterDelete(
        \Magento\Store\Model\ResourceModel\Store $subject,
        \Magento\Store\Model\ResourceModel\Store $result,
        \Magento\Framework\Model\AbstractModel $store
    )
    {
        $storeCode = $store->getCode();
        if ($storeCode !== \Magento\Store\Model\Store::ADMIN_CODE) {
            $this->deleteSalesChannel->execute(\Ecombricks\StoreInventory\Api\InventorySalesApi\Data\SalesChannelInterface::TYPE_STORE, $storeCode);
        }
        return $result;
    }

}
