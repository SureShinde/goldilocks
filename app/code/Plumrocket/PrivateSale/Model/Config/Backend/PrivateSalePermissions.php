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

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\Config\Backend;

use Plumrocket\PrivateSale\Model\Config\Backend\Serialized\ArraySerialized;

class PrivateSalePermissions extends ArraySerialized
{
    /**
     * @inheritDocs
     */
    public function beforeSave()
    {
        $values = (array) $this->getValue();
        $customerGroups = [];

        foreach ($values as $value) {
            if (isset($value['group'])) {
                foreach ($value['group'] as $groupId) {
                    if (in_array($groupId, $customerGroups)) {
                        throw new \Magento\Framework\Validator\Exception(
                            __('Value of "Customer Group" in field "Permission By Customer Group" must be unique')
                        );
                    }

                    $customerGroups[]= $groupId;
                }
            }
        }

        return parent::beforeSave();
    }
}
