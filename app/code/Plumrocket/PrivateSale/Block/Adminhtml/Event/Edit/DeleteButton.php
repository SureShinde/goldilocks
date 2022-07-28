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

class DeleteButton extends AbstractButton
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        if (! $this->getEvent()) {
            return [];
        }

        return [
            'label' => __('Delete'),
            'class' => 'secondary',
            'on_click' => 'deleteConfirm(\'' . __('Are you sure you want to do this?') . '\', \''
                . $this->getUrl("prprivatesale/event/delete", ["id" => $this->getEvent()->getId()])
                . '\', {"data": {}})',
        ];
    }
}
