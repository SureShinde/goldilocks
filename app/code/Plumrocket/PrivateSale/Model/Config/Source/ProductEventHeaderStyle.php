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

namespace Plumrocket\PrivateSale\Model\Config\Source;

use Magento\Framework\View\Asset\Repository;

class ProductEventHeaderStyle extends AbstractOptionSource
{
    const TEMPLATE_1 = 'template_1';
    const TEMPLATE_2 = 'template_2';
    const TEMPLATE_3 = 'template_3';

    /**
     * @var Repository
     */
    private $viewAssetRepository;

    /**
     * ReviewPageStructure constructor.
     *
     * @param Repository $viewAssetRepository
     */
    public function __construct(Repository $viewAssetRepository)
    {
        $this->viewAssetRepository = $viewAssetRepository;
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return [
            self::TEMPLATE_1 => __('Template 1'),
            self::TEMPLATE_2 => __('Template 2'),
            self::TEMPLATE_3 => __('Template 3')
        ];
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Template 1'),
                'value' => self::TEMPLATE_1,
                'image' => $this->viewAssetRepository->getUrl(
                    'Plumrocket_PrivateSale::images/event/header/style/category/pr-v1.jpg'
                ),
            ],
            [
                'label' => __('Template 2'),
                'value' => self::TEMPLATE_2,
                'image' => $this->viewAssetRepository->getUrl(
                    'Plumrocket_PrivateSale::images/event/header/style/category/pr-v2.jpg'
                ),
            ],
            [
                'label' => __('Template 3'),
                'value' => self::TEMPLATE_3,
                'image' => $this->viewAssetRepository->getUrl(
                    'Plumrocket_PrivateSale::images/event/header/style/category/pr-v3.jpg'
                ),
            ]
        ];
    }
}
