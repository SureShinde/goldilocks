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

namespace Plumrocket\PrivateSale\Observer\Adminhtml;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @since 5.0.0
 */
class LayoutLoadBeforeObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param \Magento\Framework\App\RequestInterface         $request
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        RequestInterface $request,
        ProductMetadataInterface $productMetadata
    ) {
        $this->request = $request;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.3.0', '<')) {
            if ('prprivatesale_event_edit' === $this->request->getFullActionName()) {
                /** @var \Magento\Framework\View\Layout\ProcessorInterface $update */
                $update = $observer->getEvent()->getLayout()->getUpdate();
                $update->addUpdate('<head><css src="Plumrocket_PrivateSale::css/magento-two-two-event.css"/></head>');
            }
        }
    }
}
