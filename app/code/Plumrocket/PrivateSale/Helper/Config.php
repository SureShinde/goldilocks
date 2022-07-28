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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const BROWSING_EVENT = 0;
    const SHOW_PRODUCT_PRICES = 1;
    const SHOW_ADD_TO_CART = 2;

    const LAST_FLUSH_DATE_PATH = '/cron/last_flush';

    const CONFIG_SECTION = 'prprivatesale';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $generalPermissionsBeforeEventStarts;

    /**
     * @var array
     */
    private $generalPermissionsAfterEventEnds;

    /**
     * @var array
     */
    private $privateSalePermissionsByCustomerGroup;

    /**
     * Config constructor.
     *
     * @param Context $context
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
    }

    /**
     * @param null|string $storeId
     * @return bool
     */
    public function isModuleEnabled($storeId = null): bool
    {
        return (bool) $this->getConfig('/general/enabled', $storeId);
    }

    /**
     * @param null|string $storeId
     * @return string
     */
    public function getWebsiteIndexPage($storeId = null, $scope = ScopeInterface::SCOPE_STORE): string
    {
        return (string) $this->scopeConfig->getValue('web/default/cms_home_page', $scope, $storeId);
    }

    /**
     * @param null|string $storeId
     * @return bool
     */
    public function isEventStatisticEnabled($storeId = null): bool
    {
        return (bool) $this->getConfig('/main/event_statistic', $storeId);
    }

    /**
     * @param null|string $storeId
     * @return int
     */
    public function getCookieLifetime($storeId = null): int
    {
        return (int) $this->getConfig('/main/cookie_lifetime', $storeId);
    }

    /**
     * @param null $storeId
     * @return int
     */
    public function getComingSoonDays($storeId = null): int
    {
        return (int) $this->getConfig('/main/coming_soon_days', $storeId);
    }

    /**
     * @param null $storeId
     * @return int
     */
    public function getEndingSoonDays($storeId = null): int
    {
        return (int) $this->getConfig('/main/ending_soon_days', $storeId);
    }

    /**
     * @param int $action
     * @param null|string $storeId
     * @return bool
     */
    public function canMakeActionBeforeEventStarts(int $action, $storeId = null): bool
    {
        $permissions = $this->getGeneralPermissionBeforeEventStarts($storeId);
        return $this->isActionAllowed($permissions, $action);
    }

    /**
     * @param int $action
     * @param null|string $storeId
     * @return bool
     */
    public function canMakeActionAfterEventEnds(int $action, $storeId = null): bool
    {
        $permissions = $this->getGeneralPermissionAfterEventEnds($storeId);
        return $this->isActionAllowed($permissions, $action);
    }

    /**
     * @param int $groupId
     * @param int $action
     * @param null|string $storeId
     * @return bool
     */
    public function canCustomerGroupMakeActionOnPrivateSale(int $action, int $groupId, $storeId = null): bool
    {
        $permissions = [];
        $privateSalePermissions = array_chunk($this->getPrivateSalePermission($storeId), 3);

        foreach ($privateSalePermissions as $key => $data) {
            /** customer group exists only in first row */
            $firstElem = current($data);

            if (isset($firstElem['group']) && in_array($groupId, $firstElem['group'])) {
                $permissions = $data;
                break;
            }
        }

        return $this->isActionAllowed($permissions, $action);
    }

    /**
     * @param null|string $storeId
     * @return string
     */
    public function getPrivateSaleLandingPage($storeId = null): string
    {
        return (string) $this->getConfig('/permission/private_sale/landing_page', $storeId);
    }

    /**
     * @param null|string $storeId
     * @return array
     */
    public function getDisallowedCustomerGroupsInSearch($storeId = null): array
    {
        return explode(
            ',',
            (string) $this->getConfig('/permission/private_sale/disallow_search_by_group', $storeId)
        );
    }

    /**
     * @param null|string $storeId
     * @return array
     */
    public function getGeneralPermissionBeforeEventStarts($storeId = null): array
    {
        if (null === $this->generalPermissionsBeforeEventStarts) {
            $this->generalPermissionsBeforeEventStarts
                =  $this->getConfig('/permission/general/before_starts', $storeId) ?: [];

            if (is_string($this->generalPermissionsBeforeEventStarts)) {
                $this->generalPermissionsBeforeEventStarts = (array) $this->serializer->unserialize(
                    $this->generalPermissionsBeforeEventStarts
                );
            }
        }

        return $this->generalPermissionsBeforeEventStarts;
    }

    /**
     * @param null|string $storeId
     * @return array
     */
    public function getGeneralPermissionAfterEventEnds($storeId = null): array
    {
        if (null === $this->generalPermissionsAfterEventEnds) {
            $this->generalPermissionsAfterEventEnds
                = $this->getConfig('/permission/general/after_ends', $storeId) ?: [];

            if (is_string($this->generalPermissionsAfterEventEnds)) {
                $this->generalPermissionsAfterEventEnds = (array) $this->serializer->unserialize(
                    $this->generalPermissionsAfterEventEnds
                );
            }
        }

        return $this->generalPermissionsAfterEventEnds;
    }

    /**
     * @param null|string $storeId
     * @return array
     */
    public function getPrivateSalePermission($storeId = null): array
    {
        if (null === $this->privateSalePermissionsByCustomerGroup) {
            $privateSalePermissions
                = $this->getConfig('/permission/private_sale/customer_group', $storeId) ?: [];

            if (is_string($privateSalePermissions)) {
                $privateSalePermissions = (array) $this->serializer->unserialize($privateSalePermissions);
            }

            $this->privateSalePermissionsByCustomerGroup = $privateSalePermissions;
        }

        return $this->privateSalePermissionsByCustomerGroup;
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getHomepageTimer($storeId = null): string
    {
        return $this->getConfig('/design/timer/homepage_timer', $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getEventCategoryTimer($storeId = null): string
    {
        return $this->getConfig('/design/timer/event_category_timer', $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getProductTimer($storeId = null): string
    {
        return $this->getConfig('/design/timer/product_timer', $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getShoppingCartTimer($storeId = null): string
    {
        return $this->getConfig('/design/timer/shopping_cart_timer', $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getCategoryEventStyle($storeId = null): string
    {
        return $this->getConfig('/design/category_event_header/style', $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getProductEventStyle($storeId = null): string
    {
        return $this->getConfig('/design/product_event_header/style', $storeId);
    }

    /**
     * Receive magento config value
     *
     * @param string      $path
     * @param string|int  $store
     * @param string|null $scope
     * @return mixed
     */
    public function getConfig($path, $store = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::CONFIG_SECTION . $path, $scope, $store);
    }

    /**
     * @param $permissions
     * @param $action
     * @return bool
     */
    private function isActionAllowed($permissions, $action)
    {
        $actionKey = array_keys($permissions);
        return isset($actionKey[$action], $permissions[$actionKey[$action]]['status'])
            && $permissions[$actionKey[$action]]['status'];
    }
}
