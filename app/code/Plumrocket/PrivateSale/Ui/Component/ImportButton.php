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

namespace Plumrocket\PrivateSale\Ui\Component;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;
use Plumrocket\PrivateSale\Model\GetCurrentEventService;

class ImportButton extends AbstractComponent
{
    /**
     * Component name
     */
    const NAME = 'importButton';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var GetCurrentEventService
     */
    private $getCurrentEvent;

    /**
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param GetCurrentEventService $getCurrentEvent
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        GetCurrentEventService $getCurrentEvent,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->getCurrentEvent = $getCurrentEvent;
    }

    /**
     * @inheritDoc
     */
    public function getComponentName()
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        $config['submitUrl'] = $this->urlBuilder->getUrl(
            $config['submitUrl'],
            ['event_id' => $this->getCurrentEvent->getEventId()]
        );

        if (isset($config['options'])) {
            $options = [];
            $config['options'] = array_slice($config['options'], 2);

            foreach ($config['options'] as $option) {
                if ('select' === $option['type']) {
                    $option['options'] = array_values($option['options']);
                }

                $options[] = $option;
            }

            $config['options'] = $options;
            $this->setData('config', $config);
        }
        parent::prepare();
    }
}
