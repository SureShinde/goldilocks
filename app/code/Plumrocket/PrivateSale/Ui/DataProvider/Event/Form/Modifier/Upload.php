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

namespace Plumrocket\PrivateSale\Ui\DataProvider\Event\Form\Modifier;

use Magento\Framework\Exception\FileSystemException;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterfaceFactory;

class Upload implements ModifierInterface
{
    /**
     * @var EventInterfaceFactory
     */
    protected $eventFactory;

    /**
     * Upload constructor.
     * @param EventInterfaceFactory $eventFactory
     */
    public function __construct(EventInterfaceFactory $eventFactory)
    {
        $this->eventFactory = $eventFactory;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data)
    {
        /** @var \Plumrocket\PrivateSale\Api\Data\EventInterface $event */
        $event = $this->eventFactory->create();

        foreach ($data as & $eventData) {
            $eventData['newsletter_image'] = $this->tryGetImageData($event, $eventData['newsletter_image'] ?? '');
            $eventData['event_image']      = $this->tryGetImageData($event, $eventData['event_image'] ?? '');
            $eventData['header_image']     = $this->tryGetImageData($event, $eventData['header_image'] ?? '');
            $eventData['small_image']      = $this->tryGetImageData($event, $eventData['small_image'] ?? '');
        }

        return $data;
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @param string                                          $image
     * @return array|string
     */
    public function tryGetImageData(EventInterface $event, $image)
    {
        if ($image) {
            try {
                return [$event->getImageData($image)];
            } catch (FileSystemException $fileSystemException) {
                return '';
            }
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
