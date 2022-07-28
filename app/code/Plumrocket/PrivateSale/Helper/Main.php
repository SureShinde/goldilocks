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
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\PrivateSale\Helper;

class Main extends \Plumrocket\Base\Helper\Base
{
    final public function getCustomerKey()
    {
        $customerKey = '96261d5be535678d2e8e109821ffbcdd56f1e46752';

        if (method_exists($this, 'getTrueCustomerKey')) {
            return $this->getTrueCustomerKey($customerKey);
        }

        return $customerKey;
    }
}
