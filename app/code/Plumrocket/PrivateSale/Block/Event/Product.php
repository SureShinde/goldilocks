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
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Event;

use Plumrocket\PrivateSale\Helper\Timer as TimerHelper;

/**
 * @method $this setProduct($product)
 */
class Product extends AbstractEvent
{
    /**
     * @var \Plumrocket\PrivateSale\Model\Event
     */
    private $event;

    /**
     * @var string
     */
    private $productTimer;

    /**
     * @var string
     */
    protected $_template = 'event/item.phtml';

    /**
     * @inheritDoc
     */
    public function getEvent()
    {
        return $this->getEventByProduct();
    }

    /**
     * @return mixed
     */
    protected function getCurrentProduct()
    {
        if ($product = $this->getProduct()) {
            if (is_int($product)) {
                $product = $this->productRepository->getById($product);
            }

            return $product;
        }

        return $this->registry->registry('current_product');
    }

    /**
     * Retrieve product id
     * @return int
     */
    public function getItemId()
    {
        return $this->getCurrentProduct()->getId();
    }

    /**
     * Retrieve event end time
     * @return int
     */
    public function getEventEnd()
    {
        $event = $this->getEventByProduct();

        return $event ? strtotime($event->getActiveTo()) : 0;
    }

    /**
     * @return bool
     */
    public function isStaticDate()
    {
        return $this->getTimerLayout() === TimerHelper::STATIC_DATE;
    }

    /**
     * @return string
     */
    public function getStaticDate()
    {
        return $this->formatStaticDate($this->getEventEnd());
    }

    /**
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface|null
     */
    public function getEventByProduct()
    {
        if (! $this->event) {
            $this->event = $this->eventCatalog->getEventForProduct($this->getCurrentProduct());
        }

        return $this->event;
    }

    /**
     * @return string
     */
    public function getTimerLayout(): string
    {
        if (! $this->productTimer) {
            $this->productTimer = $this->timerHelper->getPageTimerFormat(TimerHelper::EVENT_PRODUCT);
        }

        return $this->productTimer;
    }

    /**
     * @param $template
     * @return bool
     */
    public function canShowHeader($template)
    {
        return $template === $this->eventHeaderStyle->getProductHeaderType();
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->getEventByProduct()
            ? $this->getEventByProduct()->getName() : '';
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        if ('template_2' === $this->eventHeaderStyle->getProductHeaderType()) {
            $this->getLayout()->setChild(
                'product.info.main',
                'privatesale.product.event',
                'privatesale.product.event'
            );
            $this->getLayout()->reorderChild(
                'product.info.main',
                'privatesale.product.event',
                10,
                false
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (! $this->getEventByProduct()) {
            return '';
        }

        return parent::_toHtml();
    }
}
