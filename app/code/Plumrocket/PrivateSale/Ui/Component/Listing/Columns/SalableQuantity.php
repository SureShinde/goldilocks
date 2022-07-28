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

namespace Plumrocket\PrivateSale\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Plumrocket\PrivateSale\Model\Inventory\ObjectProvider;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\App\ProductMetadataInterface;

class SalableQuantity extends Column
{
    /**
     * @var StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var ObjectProvider
     */
    private $objectProvider;

    /**
     * @var StockStateInterface
     */
    protected $stockState;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ObjectProvider $objectProvider
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockStateInterface $stockState
     * @param array $components
     * @param array $data
     */
    public function __construct(
        StockStateInterface $stockState,
        ProductMetadataInterface $productMetadata,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ObjectProvider $objectProvider,
        StockItemRepositoryInterface $stockItemRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->objectProvider = $objectProvider;
        $this->stockState = $stockState;
        $this->stockItemRepository = $stockItemRepository;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['totalRecords']) && $dataSource['data']['totalRecords'] > 0) {
            $isSourceItemManagementAllowedForProductType = $this->objectProvider
                ->getObjectIsSourceItemManagementAllowedForProductType();
            $getSalableQuantityDataBySku = $this->objectProvider->getObjectGetSalableQuantityDataBySkuObject();
            $fieldName = $this->getName();

            foreach ($dataSource['data']['items'] as &$row) {
                if ($getSalableQuantityDataBySku && $isSourceItemManagementAllowedForProductType) {
                    $row[$fieldName] =
                        $isSourceItemManagementAllowedForProductType->execute($row['type_id']) === true
                            ? $getSalableQuantityDataBySku->execute($row['sku'])
                            : [];
                } else {
                    $row[$fieldName] = [[
                        'stock_name' => __('Default Stock'),
                        'qty' => $this->getStockQtyById($row['entity_id']),
                        'manage_stock' => true,
                    ]];
                }
            }

            unset($row);
        }

        return $dataSource;
    }

    /**
     * Fix for version 2.3.3 and lower
     *
     * @param $id
     * @return float
     */
    private function getStockQtyById($id)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.3.3', '<=')) {
            return $this->stockState->getStockQty($id);
        }

        return $this->stockItemRepository->get((int) $id)->getQty();
    }
}
