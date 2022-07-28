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

class SplashPage extends AbstractOptionSource
{
    const PRIVATE_SALE_PAGE = 1;

    const MAGENTO_LOGIN_PAGE = 2;

    const MAGENTO_REGISTRATION_PAGE = 3;

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            self::PRIVATE_SALE_PAGE => __('Private Sale - Splash Page'),
            self::MAGENTO_LOGIN_PAGE => __('Magento Login Page'),
            self::MAGENTO_REGISTRATION_PAGE => __('Magento Registration Page'),
        ];
    }
}
