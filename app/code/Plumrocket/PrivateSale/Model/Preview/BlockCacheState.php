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

namespace Plumrocket\PrivateSale\Model\Preview;

use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Type\Block;
use Plumrocket\PrivateSale\Helper\Preview;

/**
 * Disable block cache for changing prices on preview
 *
 * @since v5.0.0
 */
class BlockCacheState implements StateInterface
{
    /**
     * @var \Plumrocket\PrivateSale\Helper\Preview
     */
    private $preview;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $magentoCacheState;

    /**
     * DisableFrontendCaches constructor.
     *
     * @param \Plumrocket\PrivateSale\Helper\Preview      $preview
     * @param \Magento\Framework\App\Cache\StateInterface $magentoCacheState
     */
    public function __construct(
        Preview $preview,
        StateInterface $magentoCacheState
    ) {
        $this->preview = $preview;
        $this->magentoCacheState = $magentoCacheState;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled($cacheType): bool
    {
        if (Block::TYPE_IDENTIFIER === $cacheType && $this->preview->isAllowToChangeData()) {
            return false;
        }

        return $this->magentoCacheState->isEnabled($cacheType);
    }

    /**
     * @inheritDoc
     */
    public function setEnabled($cacheType, $isEnabled)
    {
        if (Block::TYPE_IDENTIFIER === $cacheType && $this->preview->isAllowToChangeData()) {
            return;
        }

        $this->magentoCacheState->setEnabled($cacheType, $isEnabled);
    }

    /**
     * @inheritDoc
     */
    public function persist()
    {
        $this->magentoCacheState->persist();
    }
}
