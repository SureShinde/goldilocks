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

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Plumrocket\PrivateSale\Helper\Config;
use Plumrocket\PrivateSale\Model\Event;
use Plumrocket\PrivateSale\Model\Event\Catalog;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\UrlInterface;
use Plumrocket\PrivateSale\Model\Frontend\QtyLimit;
use Magento\Framework\Message\ManagerInterface;

class EventCheck implements ObserverInterface
{
    /**
     * Registry
     * @var Registry
     */
    protected $registry;

    /**
     * @var Catalog
     */
    private $eventCatalog;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var QtyLimit
     */
    private $limit;

    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * EventCheck constructor.
     * @param Registry $registry
     * @param Catalog $eventCatalog
     * @param Config $configHelper
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param QtyLimit $limit
     * @param ManagerInterface $manager
     */
    public function __construct(
        Registry $registry,
        Catalog $eventCatalog,
        Config $configHelper,
        ResponseFactory $responseFactory,
        UrlInterface $url,
        QtyLimit $limit,
        ManagerInterface $manager
    ) {
        $this->registry = $registry;
        $this->eventCatalog = $eventCatalog;
        $this->configHelper = $configHelper;
        $this->url = $url;
        $this->responseFactory = $responseFactory;
        $this->limit = $limit;
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (! $this->configHelper->isModuleEnabled()) {
            return;
        }

        $actionName = $observer->getFullActionName();

        if (in_array($actionName, ['catalog_category_view', 'catalog_product_view'])) {
            $category = $this->registry->registry('current_category');
            $product = $this->registry->registry('current_product');
            $layout = $observer->getLayout();

            if ($actionName === 'catalog_category_view' && $category) {
                //If display type for current category eq flash sale homepage, than set template of private sale
                if (Event::DM_HOMEPAGE === $category->getDisplayMode()) {
                    //add handle for homepage
                    $layout->getUpdate()->addHandle('plumrocket_privatesale_homepage_default');
                } elseif ($this->eventCatalog->isExistsEventForCategory($category)) {
                    //If enabled events for category add handle to layout
                    $layout->getUpdate()->addHandle('plumrocket_privatesale_category_view');
                }
            } elseif ($actionName === 'catalog_product_view'
                && $product
                && $this->eventCatalog->isExistsEventForProduct($product)
            ) {
                $layout->getUpdate()->addHandle('plumrocket_privatesale_product_view');
            }
        }

        if ($actionName === 'checkout_index_index') {
            $checkLimit = $this->limit->check();

            if (! empty($checkLimit)) {
                $redirectionUrl = $this->url->getUrl('checkout/cart/index');
                $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();

                return;
            }
        }

        if ($actionName === 'checkout_cart_index') {
            foreach ($this->limit->check() as $productName) {
                $this->manager->addErrorMessage(__('%1 is sold out.', $productName));
            }

            return;
        }
    }
}
