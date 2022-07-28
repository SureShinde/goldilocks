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

namespace Plumrocket\PrivateSale\Plugin\Model\Category\Attribute\Source;

use Plumrocket\PrivateSale\Model\Event;

class Mode
{
    /**
     *  After get all options
     *
     * @param \Magento\Catalog\Model\Category\Attribute\Source\Mode $subject
     * @param                                                       $result
     * @return array
     */
    public function afterGetAllOptions(
        \Magento\Catalog\Model\Category\Attribute\Source\Mode $subject,
        $result
    ) {
        $result[] = [
            'value' => Event::DM_HOMEPAGE,
            'label' => __('Private Sale & Flash Sale Event Homepage')
        ];

        return $result;
    }
}
