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

namespace Plumrocket\PrivateSale\Model\Config\Source;

use Magento\Cms\Model\Config\Source\Page;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Eventlandingpage extends AbstractSource
{
    /**
     * Use Default Config
     */
    const USE_DEFAULT = 0;

    /**
     * Use Login Page
     */
    const LOGIN_PAGE = 1;

    /**
     * Use Registration Page
     */
    const REGISTRATION_PAGE = 2;

    /**
     * @var Page
     */
    private $pageSource;

    /**
     * Eventlandingpage constructor.
     *
     * @param Page $pageSource
     */
    public function __construct(Page $pageSource)
    {
        $this->pageSource = $pageSource;
    }

    /**
     * Retrieve all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = [
            [
                'label' => __('Use Config Settings'),
                'value' => self::USE_DEFAULT
            ],
            [
                'label' => __('Login Page'),
                'value' => self::LOGIN_PAGE
            ],
            [
                'label' => __('Registration Page'),
                'value' => self::REGISTRATION_PAGE
            ]
        ];

        $cmsPages = $this->pageSource->toOptionArray();

        return array_merge($options, $cmsPages);
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
