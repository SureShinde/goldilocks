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

namespace Plumrocket\PrivateSale\Observer\Checkout;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\OrderRepository;
use Plumrocket\PrivateSale\Model\EventStatistics\Collect;
use Plumrocket\PrivateSale\Helper\Config as ConfigHelper;
use Plumrocket\PrivateSale\Model\Frontend\QtyLimit;

class PlaceOrder implements ObserverInterface
{
    /**
     * @var OrderRepository
     */
    private $order;

    /**
     * @var Collect
     */
    private $eventStatisticsCollect;

    /**
     * @var ConfigHelper
     */
    private $config;

    /**
     * @var QtyLimit
     */
    private $qtyLimit;

    /**
     * PlaceOrder constructor.
     * @param OrderRepository $order
     * @param Collect $eventStatisticsCollect
     * @param ConfigHelper $config
     * @param QtyLimit $qtyLimit
     */
    public function __construct(
        OrderRepository $order,
        Collect $eventStatisticsCollect,
        ConfigHelper $config,
        QtyLimit $qtyLimit
    ) {
        $this->order = $order;
        $this->eventStatisticsCollect = $eventStatisticsCollect;
        $this->config = $config;
        $this->qtyLimit = $qtyLimit;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (! $this->config->isModuleEnabled()) {
            return;
        }

        $orderIds = $observer->getEvent()->getOrderIds();

        foreach($orderIds as $orderId) {
            $order = $this->order->get($orderId);

            if ($order) {
                $this->eventStatisticsCollect->execute($order);
                $this->qtyLimit->decreaseQtyLimit($order);
            }
        }
    }
}
