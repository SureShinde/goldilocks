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

namespace Plumrocket\PrivateSale\Model\Frontend;

use Magento\Framework\Exception\NoSuchEntityException;
use Plumrocket\PrivateSale\Helper\Config;
use Plumrocket\PrivateSale\Model\Event\GetEndedEventIdByProductId;
use Plumrocket\PrivateSale\Model\Event\GetEventIdByProductId;
use Plumrocket\PrivateSale\Model\Event\GetUpcomingEventIdByProductId;
use Plumrocket\PrivateSale\Model\EventRepository;

class ProductEventPermissions
{
    /**
     * Default permissions for actions
     */
    const ALLOW_ALL = [
        Config::BROWSING_EVENT => [
            'isAllowed' => true,
        ],
        Config::SHOW_PRODUCT_PRICES => [
            'isAllowed' => true,
            'message' => [
                'text' => '',
                'url' => '',
                'landingPage' => '',
            ],
        ],
        Config::SHOW_ADD_TO_CART    => [
            'isAllowed' => true,
            'message' => [
                'text' => '',
                'url' => '',
                'landingPage' => '',
            ],
        ],
    ];

    /**
     * @var EventRepository
     */
    private $eventsRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var GetEventIdByProductId
     */
    private $getEventIdByProductId;

    /**
     * @var GetUpcomingEventIdByProductId
     */
    private $getUpcomingEventIdByProductId;

    /**
     * @var GetEndedEventIdByProductId
     */
    private $getEndedEventIdByProductId;

    /**
     * @var array[]
     */
    private $calculatedPermission = [];

    /**
     * Permissions constructor.
     *
     * @param \Plumrocket\PrivateSale\Model\Event\GetEndedEventIdByProductId    $getEndedEventIdByProductId
     * @param \Plumrocket\PrivateSale\Model\Event\GetUpcomingEventIdByProductId $getUpcomingEventIdByProductId
     * @param \Plumrocket\PrivateSale\Model\EventRepository                     $eventsRepository
     * @param \Plumrocket\PrivateSale\Helper\Config                             $config
     * @param \Plumrocket\PrivateSale\Model\Event\GetEventIdByProductId         $getEventIdByProductId
     */
    public function __construct(
        GetEndedEventIdByProductId $getEndedEventIdByProductId,
        GetUpcomingEventIdByProductId $getUpcomingEventIdByProductId,
        EventRepository $eventsRepository,
        Config $config,
        GetEventIdByProductId $getEventIdByProductId
    ) {
        $this->eventsRepository = $eventsRepository;
        $this->config = $config;
        $this->getEventIdByProductId = $getEventIdByProductId;
        $this->getUpcomingEventIdByProductId = $getUpcomingEventIdByProductId;
        $this->getEndedEventIdByProductId = $getEndedEventIdByProductId;
    }

    /**
     * @param int $productId
     * @return mixed
     */
    public function canBrowse(int $productId)
    {
        return $this->getActionPermissions(Config::BROWSING_EVENT, $productId)['isAllowed'];
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function canShowPrice(int $productId): bool
    {
        return $this->getActionPermissions(Config::SHOW_PRODUCT_PRICES, $productId)['isAllowed'];
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getPriceMessageData(int $productId): array
    {
        return $this->getActionPermissions(Config::SHOW_PRODUCT_PRICES, $productId)['message'];
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function canShowAddToCart(int $productId): bool
    {
        return $this->getActionPermissions(Config::SHOW_ADD_TO_CART, $productId)['isAllowed'];
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getAddToCartMessageData(int $productId): array
    {
        return $this->getActionPermissions(Config::SHOW_ADD_TO_CART, $productId)['message'];
    }

    /**
     * @param int $action
     * @param int $productId
     * @return array
     */
    public function getActionPermissions(int $action, int $productId): array
    {
        if (! isset($this->calculatedPermission[$productId])) {
            $this->calculatedPermission[$productId] = $this->calculatePermission($productId);
        }

        return $this->calculatedPermission[$productId][$action];
    }

    /**
     * @param int $productId
     * @return array|\bool[][]
     */
    private function calculatePermission(int $productId): array
    {
        if (! $this->config->isModuleEnabled()) {
            return self::ALLOW_ALL;
        }

        $activeEventId = $this->getEventIdByProductId->execute($productId);
        if ($activeEventId) {
            return $this->checkPrivateEventPermissions($activeEventId);
        }

        $upcomingEventId = $this->getUpcomingEventIdByProductId->execute($productId);
        if ($upcomingEventId) {
            return $this->checkUpcomingEventPermissions($upcomingEventId);
        }

        $endedEventId = $this->getEndedEventIdByProductId->execute($productId);
        if ($endedEventId) {
            return $this->checkEndedEventPermissions($endedEventId);
        }

        return self::ALLOW_ALL;
    }

    /**
     * @param int $eventId
     * @return array|\bool[][]
     */
    private function checkPrivateEventPermissions(int $eventId): array
    {
        try {
            $event = $this->eventsRepository->getById($eventId);

            if ($event->isEventPrivate()) {
                $privateSaleLandingPageUrl = $event->getPrivateSaleLandingPageUrl();
                $privateSaleLandingPage = $event->getPrivateSaleLandingPage();

                return $this->createPermissions(
                    $event->canCustomerGroupMakeActionOnPrivateSale(Config::BROWSING_EVENT),
                    $event->canCustomerGroupMakeActionOnPrivateSale(Config::SHOW_PRODUCT_PRICES),
                    [
                        'text' => __('Join To See Price'),
                        'url' => $privateSaleLandingPageUrl,
                        'landingPage' => $privateSaleLandingPage,
                    ],
                    $event->canCustomerGroupMakeActionOnPrivateSale(Config::SHOW_ADD_TO_CART),
                    [
                        'text' => __('Join To Buy Product'),
                        'url' => $privateSaleLandingPageUrl,
                        'landingPage' => $privateSaleLandingPage,
                    ]
                );
            }

            return self::ALLOW_ALL;
        } catch (NoSuchEntityException $e) {
            return self::ALLOW_ALL;
        }
    }

    /**
     * @param int $upcomingEventId
     * @return array|\bool[][]
     */
    private function checkUpcomingEventPermissions(int $upcomingEventId): array
    {
        try {
            $event = $this->eventsRepository->getById($upcomingEventId);
        } catch (NoSuchEntityException $e) {
            return self::ALLOW_ALL;
        }

        return $this->createPermissions(
            $event->canMakeActionBeforeEventStarts(Config::BROWSING_EVENT),
            $event->canMakeActionBeforeEventStarts(Config::SHOW_PRODUCT_PRICES),
            __('Event Is Coming Soon'),
            $event->canMakeActionBeforeEventStarts(Config::SHOW_ADD_TO_CART),
            __('Event Is Coming Soon')
        );
    }

    /**
     * @param int $endedEventId
     * @return array|\bool[][]
     */
    private function checkEndedEventPermissions(int $endedEventId): array
    {
        try {
            $event = $this->eventsRepository->getById($endedEventId);
        } catch (NoSuchEntityException $e) {
            return self::ALLOW_ALL;
        }

        return $this->createPermissions(
            $event->canMakeActionAfterEventEnds(Config::BROWSING_EVENT),
            $event->canMakeActionAfterEventEnds(Config::SHOW_PRODUCT_PRICES),
            __('Event Has Ended'),
            $event->canMakeActionAfterEventEnds(Config::SHOW_ADD_TO_CART),
            __('Event Has Ended')
        );
    }

    /**
     * @param bool $canBrowse
     * @param bool $canSeePrice
     * @param      $priceMessage
     * @param bool $canAddToCart
     * @param      $addToCartMessage
     * @return array|\bool[][]
     */
    private function createPermissions(
        bool $canBrowse,
        bool $canSeePrice,
        $priceMessage,
        bool $canAddToCart,
        $addToCartMessage
    ): array {
        $permissions = self::ALLOW_ALL;

        $permissions[Config::BROWSING_EVENT]['isAllowed'] = $canBrowse;

        if (! $canSeePrice) {
            $permissions[Config::SHOW_PRODUCT_PRICES]['isAllowed'] = false;
            $permissions[Config::SHOW_PRODUCT_PRICES]['message'] = is_array($priceMessage)
                ? $priceMessage
                : [
                    'text'        => $priceMessage,
                    'url'         => '',
                    'landingPage' => '',
                ];
        }

        if (! $canAddToCart) {
            $permissions[Config::SHOW_ADD_TO_CART]['isAllowed'] = false;
            if (! $permissions[Config::SHOW_PRODUCT_PRICES]['message']['text']) {
                $permissions[Config::SHOW_ADD_TO_CART]['message'] = is_array($addToCartMessage)
                    ? $addToCartMessage
                    : [
                        'text'        => $addToCartMessage,
                        'url'         => '',
                        'landingPage' => '',
                    ];
            }
        }

        return $permissions;
    }
}
