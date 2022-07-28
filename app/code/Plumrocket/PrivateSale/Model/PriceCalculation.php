<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model;

use Magento\Catalog\Model\Product\Price\SpecialPriceFactory;
use Magento\Catalog\Model\Product\Price\SpecialPriceStorage;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Plumrocket\PrivateSale\Helper\Config as ConfigHelper;
use Plumrocket\PrivateSale\Model\Indexer\Product as ProductEventIndexer;
use Plumrocket\PrivateSale\Model\Indexer\TableNameResolver;
use Plumrocket\PrivateSale\Model\ResourceModel\FlashSale;
use Plumrocket\PrivateSale\Model\ResourceModel\SpecialPriceStorage\CollectionFactory as SpStorageFactory;

class PriceCalculation
{
    const PRICE_STORAGE_TBL = 'plumrocket_privatesale_special_price_storage';
    const PRICE_INDEX = 'catalog_product_price';

    /**
     * @var SpStorageFactory
     */
    private $priceStorage;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var TableNameResolver
     */
    private $tableNameResolver;

    /**
     * @var Product
     */
    private $resourceProduct;

    /**
     * @var SpecialPriceFactory
     */
    private $specialPriceFactory;

    /**
     * @var SpecialPriceStorage
     */
    private $specialPriceStorage;

    /**
     * @var PriceIndex
     */
    private $priceIndex;

    /**
     * @var ConfigHelper
     */
    private $config;

    /**
     * PriceCalculation constructor.
     * @param SpecialPriceStorage $specialPriceStorage
     * @param SpecialPriceFactory $specialPriceFactory
     * @param Product $resourceProduct
     * @param TableNameResolver $tableNameResolver
     * @param ResourceConnection $resourceConnection
     * @param SpStorageFactory $priceStorage
     * @param State $state
     * @param \Plumrocket\PrivateSale\Model\PriceIndex $priceIndex
     * @param ConfigHelper $config
     */
    public function __construct(
        SpecialPriceStorage $specialPriceStorage,
        SpecialPriceFactory $specialPriceFactory,
        Product $resourceProduct,
        TableNameResolver $tableNameResolver,
        ResourceConnection $resourceConnection,
        SpStorageFactory $priceStorage,
        State $state,
        PriceIndex $priceIndex,
        ConfigHelper $config
    ) {
        $this->specialPriceStorage = $specialPriceStorage;
        $this->specialPriceFactory = $specialPriceFactory;
        $this->priceStorage = $priceStorage;
        $this->resourceConnection = $resourceConnection;
        $this->tableNameResolver = $tableNameResolver;
        $this->resourceProduct = $resourceProduct;
        $this->priceIndex = $priceIndex;
        $this->config = $config;

        try {
            $state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
        }
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function recalculationPrice()
    {
        if (! $this->config->isModuleEnabled()) {
            return;
        }

        $connection = $this->getConnection();
        $flashSaleTable = $this->resourceConnection->getTableName(FlashSale::MAIN_TABLE);
        $indexTable = $this->resourceConnection->getTableName(
            $this->tableNameResolver->getResolvedName(ProductEventIndexer::INDEX_NAME)
        );

        $this->restoreSpecialPrice();

        $select = $connection->select()
            ->from(
                ['it' => $indexTable],
                ['product_id', 'event_id', 'website_id', 'start_date', 'end_date']
            )->join(
                ['fs' => $flashSaleTable],
                'fs.product_id = it.product_id AND fs.event_id = it.event_id',
                ['sale_price']
            )->where('fs.sale_price IS NOT NULL');

        $skus = [];
        $prices = [];
        $productIds = [];

        foreach ($connection->fetchAll($select) as $event) {
            $rawData = $this->resourceProduct->getAttributeRawValue(
                $event['product_id'],
                'sku',
                $event['website_id']
            );

            if (empty($rawData['sku'])) {
                continue;
            }

            $productIds[] = $event['product_id'];
            $sku = $rawData['sku'];
            $skus[] = $sku;

            $price = $this->specialPriceFactory->create()
                ->setSku($sku)
                ->setPrice($event['sale_price'])
                ->setStoreId(0);

            // TODO: add start and end time to special price.
            //->setPriceFrom($event['start_date'])
            //->setPriceTo($event['end_date']);

            $prices[] = $price;
        }

        $pricesData = $this->specialPriceStorage->get($skus);
        $bulkInsert = [];
        $innerSku = [];

        foreach ($pricesData as $price) {
            $bulkInsert[] = [
                'sku' => $price->getSku(),
                'special_price_value' => $price->getPrice(),
                'website_id' => 0,
                'date_from' => $price->getPriceFrom(),
                'date_to' => $price->getPriceTo()
            ];

            $innerSku[] = $price->getSku();
        }

        foreach ($skus as $sku) {
            if (! in_array($sku, $innerSku, true)) {
                $bulkInsert[] = [
                    'sku' => $sku,
                    'special_price_value' => 0,
                    'website_id' => 0,
                    'date_from' => null,
                    'date_to' => null
                ];
            }
        }

        $this->savePriceStorage($bulkInsert);
        $this->specialPriceStorage->update($prices);
        $this->priceReindex($productIds);
    }

    /**
     * @param bool $withReindex
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function restoreSpecialPrice($withReindex = false)
    {
        $priceItems = $this->priceStorage->create();
        $forUpdate = [];
        $forDelete = [];
        $skus = [];

        foreach ($priceItems as $item) {
            $price = $this->specialPriceFactory->create()
                ->setSku($item->getSku())
                ->setPrice($item->getSpecialPriceValue())
                ->setStoreId($item->getWebsiteId())
                ->setPriceFrom($item->getDateFrom())
                ->setPriceTo($item->getDateTo());

            $skus[] = $item->getSku();

            if ($item->getSpecialPriceValue() == 0) {
                $forDelete[] = $price;
            } else {
                $forUpdate[] = $price;
            }

            $item->delete();
        }

        if (! empty($forDelete)) {
            $this->specialPriceStorage->delete($forDelete);
        }

        if (! empty($forUpdate)) {
            $this->specialPriceStorage->update($forUpdate);
        }

        if ($withReindex && ! empty($skus)) {
            $ids = $this->resourceProduct->getProductsIdsBySkus($skus);
            $this->priceReindex($ids);
        }
    }

    /**
     * @param $bulkData
     */
    private function savePriceStorage($bulkData)
    {
        if (empty($bulkData)) {
            return;
        }

        try {
            $tableName = $this->resourceConnection->getTableName(self::PRICE_STORAGE_TBL);
            $this->getConnection()->insertMultiple($tableName, $bulkData);
        } catch (\Exception $e) {
        }
    }

    /**
     * @param $ids
     */
    private function priceReindex($ids)
    {
        //Catalog Price Reindex
        if (! empty($ids)) {
            try {
                $this->priceIndex->runIndex($ids);
            } catch (\Exception $e) {}
        }
    }
}
