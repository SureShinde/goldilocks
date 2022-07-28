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

namespace Plumrocket\PrivateSale\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class TimelineActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'prprivatesale/event/edit';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                switch ($item['event_type']) {
                    case EventType::CATEGORY:
                        $route = isset($item[EventInterface::CATEGORY_EVENT]) ? 'prprivatesale/preview/category' : '';
                        $id = $item[EventInterface::CATEGORY_EVENT] ?? 0;
                        break;
                    case EventType::PRODUCT:
                        $route = 'prprivatesale/preview/product';
                        $id = $item[EventInterface::PRODUCT_EVENT];
                        break;
                }
                $item[$this->getData('name')] = [
                    'edit' => [
                        'href' => $this->urlBuilder->getUrl(
                            static::URL_PATH_EDIT,
                            ['id' => $item['entity_id']]
                        ),
                        'label' => __('View/Edit')
                    ],
                    'preview' => [
                        'href' => $this->urlBuilder->getUrl(
                            $route,
                            ['id' => $id]
                        ),
                        'target' => 'blank',
                        'label' => __('Preview')
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
