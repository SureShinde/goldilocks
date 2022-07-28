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

namespace Plumrocket\PrivateSale\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\JsonEncoded;

class CustomPermissions extends JsonEncoded
{
    /**
     * @inheritDoc
     */
    public function beforeSave($object)
    {
        $customerGroups = [];
        $attributeName = $this->getAttribute()->getName();
        $customPermissions = (array) $object->getData($attributeName);

        foreach ($customPermissions as $permission) {
            if (isset($permission['group'])) {
                foreach ($permission['group'] as $groupId) {
                    if (in_array($groupId, $customerGroups)) {
                        throw new \Magento\Framework\Validator\Exception(
                            __('Value of "Customer Group" must be unique')
                        );
                    }

                    $customerGroups[]= $groupId;
                }
            }
        }

        return parent::beforeSave($object);
    }
}
