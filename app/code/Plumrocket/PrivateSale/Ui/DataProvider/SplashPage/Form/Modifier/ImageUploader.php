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

namespace Plumrocket\PrivateSale\Ui\DataProvider\SplashPage\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Plumrocket\PrivateSale\Helper\MagentoVersionChecker;

class ImageUploader implements ModifierInterface
{
    /**
     * @var MagentoVersionChecker
     */
    private $versionChecker;

    /**
     * ImageUploader constructor.
     * @param MagentoVersionChecker $versionChecker
     */
    public function __construct(
        MagentoVersionChecker $versionChecker
    ) {
        $this->versionChecker = $versionChecker;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta)
    {
        $typeElement = 'imageUploader';
        $elementTmpl = 'ui/form/element/uploader/image';

        if ($this->versionChecker->isOldVersion()) {
            $typeElement = 'fileUploader';
            $elementTmpl = 'ui/form/element/uploader/uploader';
        }

        $meta['media']['children']['images']['children']['record']['children']['image'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => $typeElement,
                        'componentType' => $typeElement,
                        'sortOrder' => 0,
                        'elementTmpl' => $elementTmpl,
                        'uploaderConfig' => [
                            'url' => 'prprivatesale/splashpage_image/upload'
                        ],
                    ],
                ]
            ]
        ];

        return $meta;
    }
}
