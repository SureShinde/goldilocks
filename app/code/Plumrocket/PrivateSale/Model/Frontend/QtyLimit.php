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

namespace Plumrocket\PrivateSale\Model\Frontend;

use Plumrocket\PrivateSale\Model\Event\GetEventIdByProductId;
use Plumrocket\PrivateSale\Model\ResourceModel\FlashSale\CollectionFactory as FlashSaleCollectionFactory;
use Magento\Checkout\Model\SessionFactory;
use Plumrocket\PrivateSale\Api\Data\FlashSaleInterface;

class QtyLimit
{
    /**
     * @var GetEventIdByProductId
     */
    private $getEventIdByProductId;

    /**
     * @var FlashSaleCollectionFactory
     */
    private $flashSale;

    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * QtyLimit constructor.
     * @param GetEventIdByProductId $getEventIdByProductId
     * @param FlashSaleCollectionFactory $flashSale
     * @param SessionFactory $sessionFactory
     */
    public function __construct(
        GetEventIdByProductId $getEventIdByProductId,
        FlashSaleCollectionFactory $flashSale,
        SessionFactory $sessionFactory
    ) {
        $this->getEventIdByProductId = $getEventIdByProductId;
        $this->flashSale = $flashSale;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @param $productId
     * @return int|null
     */
    public function availableItemsCount($productId)
    {
        $eventId = $this->getEventIdByProductId->execute((int) $productId);

        if (! empty($eventId)) {
            $flashSale = $this->flashSale->create()
                ->addFieldToFilter('event_id', ['eq' => $eventId])
                ->addFieldToFilter('product_id', ['eq' => $productId]);

            if ($flashSale !== null) {
                return $flashSale->getFirstItem()->getData(FlashSaleInterface::QTY_LIMIT);
            }
        }

        return null;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function check()
    {
        $quote = $this->sessionFactory->create()->getQuote();
        $disallowProducts = [];

        foreach ($quote->getAllItems() as $item) {
            $flashSaleCount = (float) $this->availableItemsCount($item->getProductId());

            if (empty($flashSaleCount)) {
                continue;
            }

            if ($parent = $item->getParentItem()) {
                $qty = $parent->getQty();
            } else {
                $qty = $item->getQty();
            }

            if ($qty > $flashSaleCount) {
                $disallowProducts[] = $item->getName();
            }
        }

        return $disallowProducts;
    }

    /**
     * @param $order
     */
    public function decreaseQtyLimit($order)
    {
        $itemCollection = $order->getAllItems();
        $data = [];

        foreach ($itemCollection as $item) {
            $data[$item->getProductId()] = $item->getQtyOrdered();
        }

        $flashSaleCollection = $this->flashSale->create()
            ->addFieldToFilter('product_id', ['in' => array_keys($data)]);

        foreach ($flashSaleCollection as $item) {
            if (isset($data[$item->getProductId()])) {
                $qtyLimit = $item->getQtyLimit() - $data[$item->getProductId()];
                if ($qtyLimit < 0) {
                    $qtyLimit = 0;
                }
                $item->setQtyLimit((int)$qtyLimit)->save();
            }
        }
    }
}
