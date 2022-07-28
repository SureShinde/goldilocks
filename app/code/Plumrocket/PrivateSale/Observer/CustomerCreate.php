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

namespace Plumrocket\PrivateSale\Observer;

use Plumrocket\PrivateSale\Helper\Config;
use Magento\Framework\Event\ObserverInterface;
use Plumrocket\PrivateSale\Model\CatalogSession;
use Plumrocket\PrivateSale\Model\EventStatistics\Collect;

class CustomerCreate implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var CatalogSession
     */
    private $catalogSession;

    /**
     * @var Collect
     */
    private $collect;

    /**
     * CustomerCreate constructor.
     * @param CatalogSession $catalogSession
     * @param Config $configHelper
     * @param Collect $collect
     */
    public function __construct(
        CatalogSession $catalogSession,
        Config $configHelper,
        Collect $collect
    ) {
        $this->catalogSession = $catalogSession;
        $this->collect = $collect;
        $this->configHelper = $configHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (! $this->configHelper->isModuleEnabled()) {
            return;
        }

        $customer = $observer->getEvent()->getCustomer();
        $privateSaleId = $this->catalogSession->getCurrentPrivateSaleId();

        if ($customer && $customer->getId() && $privateSaleId) {
            $this->collect->customer(
                $privateSaleId,
                $customer->getId()
            );
        }
    }
}
