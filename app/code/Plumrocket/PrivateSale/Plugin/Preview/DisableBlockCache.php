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
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Plugin\Preview;

use Magento\Framework\View\Element\Context;
use Plumrocket\PrivateSale\Helper\Config;
use Plumrocket\PrivateSale\Model\Preview\BlockCacheState;

/**
 * Disable block cache for changing prices on preview
 *
 * We use onw class because plugin on \Magento\Framework\App\Cache\StateInterface::isEnabled will create loop
 * and eat all you memory
 *
 * @since v5.0.0
 */
class DisableBlockCache
{
    /**
     * @var \Plumrocket\PrivateSale\Model\Preview\BlockCacheState
     */
    private $blockCacheState;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Config
     */
    private $config;

    /**
     * DisableBlockCache constructor.
     *
     * @param \Plumrocket\PrivateSale\Model\Preview\BlockCacheState $blockCacheState
     * @param \Plumrocket\PrivateSale\Helper\Config                 $config
     */
    public function __construct(
        BlockCacheState $blockCacheState,
        Config $config
    ) {
        $this->blockCacheState = $blockCacheState;
        $this->config = $config;
    }

    /**
     * @param \Magento\Framework\View\Element\Context $subject
     * @param                                         $result
     * @return \Plumrocket\PrivateSale\Model\Preview\BlockCacheState
     */
    public function afterGetCacheState(
        Context $subject,
        $result
    ) {
        return $this->config->isModuleEnabled() ? $this->blockCacheState : $result;
    }
}
