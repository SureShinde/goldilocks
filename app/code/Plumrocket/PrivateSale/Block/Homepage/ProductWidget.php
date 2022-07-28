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

namespace Plumrocket\PrivateSale\Block\Homepage;

use Magento\Catalog\Block\Product\Widget\NewWidget;

/**
 * @method string getProductCountLimit()
 * @method string getBlockTitle()
 *
 */
class ProductWidget extends NewWidget
{
    /**
     * @var \Plumrocket\PrivateSale\Model\Event\Catalog
     */
    private $eventCatalog;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Config
     */
    private $configHelper;

    /**
     * ProductWidget constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Plumrocket\PrivateSale\Model\Event\Catalog $eventCatalog
     * @param \Plumrocket\PrivateSale\Helper\Config $configHelper
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        \Plumrocket\PrivateSale\Model\Event\Catalog $eventCatalog,
        \Plumrocket\PrivateSale\Helper\Config $configHelper,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct(
            $context,
            $productCollectionFactory,
            $catalogProductVisibility,
            $httpContext,
            $data,
            $serializer
        );
        $this->eventCatalog = $eventCatalog;
        $this->configHelper = $configHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCollection()
    {
        $flashsaleId = $this->getFlashsaleId();
        $productLimit = (int) $this->getProductCountLimit() ?: 5;

        /**
         * Reindex has all ids regardless of visibility, but for widget we need only visible products.
         * Therefore we extend limit for searching in index's table
         *
         * If you have some problem with this logic, you should add column with product visibility to the index table
         */
        $limitForIndexTable = $productLimit * 30;

        if ($flashsaleId) {
            $productIdsOnSale = $this->eventCatalog->getProductIdsByEvent($flashsaleId, $limitForIndexTable);
        } else {
            $productIdsOnSale = $this->eventCatalog->getProductIdsOnSale($limitForIndexTable);
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $this->_addProductAttributesAndPrices($collection);
        $collection->getSelect()->order(new \Zend_Db_Expr('RAND()'));

        return $collection->setPageSize($productLimit)
            ->addFieldToFilter('entity_id', ['in' => $productIdsOnSale]);
    }

    /**
     * Replaced widget title
     * @param string $html
     * @param string $widgetTitle
     * @return string
     */
    public function replaceWidgetTitle($html, $widgetTitle)
    {
        return preg_replace('#(?<=<strong role="heading" aria-level="2">).*?(?=<\/strong>)#', $widgetTitle, $html);
    }

    /**
     * @return int
     */
    public function getFlashsaleId(): int
    {
        return (int) $this->getData('flashsale_id');
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (! $this->configHelper->isModuleEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @param string $html
     * @return string
     */
    protected function _afterToHtml($html)
    {
        $widgetTitle = $this->getBlockTitle() ?? '';
        return $this->replaceWidgetTitle($html, $widgetTitle);
    }
}
