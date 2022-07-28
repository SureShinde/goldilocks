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

namespace Plumrocket\PrivateSale\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\PrivateSale\Helper\Timer as TimerHelper;

class Timer extends AbstractHelper
{
    const DISABLED = 'disable';
    const COUNTDOWN_DAYS_HOURS = 'days_hours';
    const COUNTDOWN_ALL = 'days_hours_minutes_seconds';
    const STATIC_DATE = 'static';

    const HOMEPAGE = 'homepage';
    const EVENT_CATEGORY = 'category';
    const EVENT_PRODUCT = 'product';
    const SHOPPING_CART = 'cart';

    const STATIC_DATE_FORMAT = "l F d, Y \\a\\t g a";
    const ONE_DAY_IN_SECONDS = 86400;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Timer constructor.
     *
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Plumrocket\PrivateSale\Helper\Config      $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);

        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @param string $page
     * @return string
     */
    public function getPageTimerFormat(string $page): string
    {
        $currentStoreId = $this->storeManager->getStore()->getId();

        switch ($page) {
            case self::HOMEPAGE:
                $type = $this->config->getHomepageTimer($currentStoreId);
                break;
            case self::EVENT_CATEGORY:
                $type = $this->config->getEventCategoryTimer($currentStoreId);
                break;
            case self::EVENT_PRODUCT:
                $type = $this->config->getProductTimer($currentStoreId);
                break;
            case self::SHOPPING_CART:
                $type = $this->config->getShoppingCartTimer($currentStoreId);
                break;
            default:
                $type = self::DISABLED;
        }

        return $type;
    }

    /**
     * @param $type
     * @return \Magento\Framework\Phrase|string
     */
    public function getTimerLabel(string $type)
    {
        switch ($type) {
            case TimerHelper::COUNTDOWN_DAYS_HOURS:
                return __('Ends in');
            case TimerHelper::COUNTDOWN_ALL:
                return __('Ending in');
            default:
                return '';
        }
    }

    /**
     * @return string[][]
     */
    public function getCountdownLayouts(): array
    {
        return [
            'homepage' => [
                self::COUNTDOWN_DAYS_HOURS => [
                    'default' => '{hn} {hl}',
                    'dynamic' => [
                        [
                            'format' => '{dn} {dl} {h<}and {hn} {hl}{h>}',
                            'minTime' => self::ONE_DAY_IN_SECONDS
                        ],
                        [
                            'format' => '{dn} {dl}',
                            'minTime' => self::ONE_DAY_IN_SECONDS * 3
                        ],
                    ],
                ],
                self::COUNTDOWN_ALL => '{d<}{dn} {dl}, {d>}{hnn}:{mnn}:{snn}',
            ],
            'catalog' => [
                self::COUNTDOWN_DAYS_HOURS => [
                    'default' => '<span>Event ends in</span> {hn} {hl}',
                    'dynamic' => [
                        [
                            'format' => '<span>Event ends in</span> {dn} {dl}',
                            'minTime' => self::ONE_DAY_IN_SECONDS * 3
                        ],
                        [
                            'format' => '<span>Event ends in</span> {dn} {dl} {h<}and {hn} {hl}{h>}',
                            'minTime' => self::ONE_DAY_IN_SECONDS
                        ],
                    ],
                ],
                self::COUNTDOWN_ALL => '<span>Ending in</span> {d<}{dn} {dl}, {d>}{hnn}:{mnn}:{snn}',
            ],
        ];
    }
}
