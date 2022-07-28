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

namespace Plumrocket\PrivateSale\Model\Import;

use Magento\Framework\Exception\CouldNotSaveException;
use Plumrocket\PrivateSale\Model\Inventory\SalableQuantity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Plumrocket\PrivateSale\Api\Data\FlashSaleInterface;
use Plumrocket\PrivateSale\Api\Data\FlashSaleInterfaceFactory;
use Plumrocket\PrivateSale\Api\FlashSaleRepositoryInterface;
use Plumrocket\PrivateSale\Model\ResourceModel\FlashSale\CollectionFactory;

class FlashSale
{
    /**
     * @var SalableQuantity
     */
    protected $salableQuantity;

    /**
     * @var CollectionFactory
     */
    private $flahsSaleCollectionFactory;

    /**
     * @var FlashSaleInterfaceFactory
     */
    private $flashSaleInterfaceFactory;

    /**
     * @var string
     */
    private $validationRule = ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS;

    /**
     * @var int
     */
    private $errorCount = 0;

    /**
     * @var FlashSaleRepositoryInterface
     */
    private $flashSaleRepository;

    /**
     * FlashSale constructor.
     *
     * @param CollectionFactory $flahsSaleCollectionFactory
     * @param FlashSaleInterfaceFactory $flashSaleInterfaceFactory
     * @param FlashSaleRepositoryInterface $flashSaleRepository
     * @param SalableQuantity $salableQuantity
     */
    public function __construct(
        CollectionFactory $flahsSaleCollectionFactory,
        FlashSaleInterfaceFactory $flashSaleInterfaceFactory,
        FlashSaleRepositoryInterface $flashSaleRepository,
        SalableQuantity $salableQuantity
    ) {
        $this->flahsSaleCollectionFactory = $flahsSaleCollectionFactory;
        $this->flashSaleInterfaceFactory = $flashSaleInterfaceFactory;
        $this->flashSaleRepository = $flashSaleRepository;
        $this->salableQuantity = $salableQuantity;
    }

    /**
     * @param array $rows
     */
    public function importData(array $rows, int $eventId)
    {
        $rows = $this->prepareRows($rows);
        $columnsToUpdate = array_unique(array_column($rows, 'entity_id'));

        if (! empty($columnsToUpdate) && ! $this->hasToBeTerminated()) {
            /** @var \Plumrocket\PrivateSale\Model\ResourceModel\FlashSale\Collection $collection */
            $collection = $this->flahsSaleCollectionFactory->create();
            $collection->addFieldToFilter(FlashSaleInterface::PRODUCT_ID, ['in' => $columnsToUpdate])
                ->addFieldToFilter(FlashSaleInterface::EVENT_ID, $eventId);

            foreach ($rows as $row) {
                /** @var \Plumrocket\PrivateSale\Api\Data\FlashSaleInterface $flashSaleModel */
                $flashSaleModel = $collection->getItemByColumnValue('product_id', $row['entity_id']);
                $salePrice = (float) ($row['sale_price'] ?: 0);
                $productId = (int) $row['entity_id'];
                $discount = (float) ($row['discount_amount_percent'] ?: 0);
                $isRequiredFieldsEmpty = ('' === $row['flash_sale_qty_limit']) && ! $salePrice && ! $discount;

                if ($flashSaleModel && $isRequiredFieldsEmpty) {
                    $collection->removeItemByKey($flashSaleModel->getId());
                    $flashSaleModel->delete();
                } elseif (! $isRequiredFieldsEmpty) {
                    if (! $flashSaleModel) {
                        $flashSaleModel = $this->flashSaleInterfaceFactory->create();
                    }

                    $qtyLimit = (int) (('' !== $row['flash_sale_qty_limit']) ?
                        $row['flash_sale_qty_limit'] : $this->salableQuantity->getById($productId));

                    $flashSaleModel->setSalePrice($salePrice)
                        ->setEventId($eventId)
                        ->setProductId($productId)
                        ->setDiscount($discount)
                        ->setQtyLimit($qtyLimit);

                    try {
                        $this->flashSaleRepository->save($flashSaleModel);
                    } catch (CouldNotSaveException $e) {
                        continue;
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param $rule
     */
    public function setValidationRule(string $rule)
    {
        $this->validationRule = $rule;
    }

    /**
     * @return string
     */
    public function getValidationRule()
    {
        return $this->validationRule;
    }

    /**
     * @return $this
     */
    protected function addError()
    {
        $this->errorCount++;
        return $this;
    }

    /**
     * Check if import has to be terminated
     *
     * @return bool
     */
    public function hasToBeTerminated()
    {
        return $this->getValidationRule() === ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR
            && $this->errorCount > 0;
    }

    /**
     * @param array $rows
     */
    private function prepareRows(array $rows)
    {
        $headers = array_shift($rows);
        $result = [];

        foreach ($rows as $row) {
            $row = array_combine($headers, $row);
            if (! $this->validateRowForUpdate($row)) {
                continue;
            }

            $result[] = $row;
        }

        return $result;
    }

    /**
     * @param $row
     */
    private function validateRowForUpdate($row)
    {
        if (empty($row['entity_id'])) {
            $this->addError();
            return false;
        }

        return true;
    }
}
