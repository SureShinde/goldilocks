<?php

declare(strict_types=1);

namespace Amasty\Preorder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @deprecated
 * @see \Amasty\Preorder\Plugin\Sales\Model\Service\OrderServicePlugin
 */
class OrderPlaceAfter implements ObserverInterface
{
    /**
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
    }
}
