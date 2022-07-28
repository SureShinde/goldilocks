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
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Adminhtml\Event\Edit;

use Plumrocket\PrivateSale\Model\Config\Source\EventType;

class PreviewButton extends AbstractButton
{
    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $event = $this->getEvent();

        if (! $event) {
            return [];
        }

        $eventType = $event->getType();
        $id = null;

        switch ($eventType) {
            case EventType::CATEGORY:
                $route = 'prprivatesale/preview/category';
                $id = $event->getCategoryId();
                break;
            case EventType::PRODUCT:
                $route = 'prprivatesale/preview/product';
                $id = $event->getProductId();
                break;
        }

        if ($id) {
            return [
                'id' => 'privatesale_preview',
                'label' => __('Preview'),
                'on_click' => "window.open('". $this->getUrl(
                    $route,
                    ['id' => $id, 'store' => $this->getRequestParam('store')]
                ) . "')",
                'class' => 'privatesale-preview',
            ];
        }

        return [];
    }
}
