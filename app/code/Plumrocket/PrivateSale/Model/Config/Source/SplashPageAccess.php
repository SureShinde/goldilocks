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

class SplashPageAccess extends AbstractOptionSource
{
    const LOGIN_AND_REGISTER = 1;
    const LOGIN = 2;
    const REGISTER = 3;

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            self::LOGIN_AND_REGISTER => __('Login & Registration'),
            self::LOGIN => __('Login Only'),
            self::REGISTER => __('Registration Only')
        ];
    }
}
