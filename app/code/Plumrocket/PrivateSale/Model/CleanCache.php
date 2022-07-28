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

namespace Plumrocket\PrivateSale\Model;

use Magento\Framework\App\Cache\Frontend\Pool as FrontendPool;

class CleanCache
{
    /**
     * @var FrontendPool
     */
    private $frontendCachePool;

    /**
     * CacheClear constructor.
     * @param FrontendPool $frontendCachePool
     */
    public function __construct(
        FrontendPool $frontendCachePool
    ) {
        $this->frontendCachePool = $frontendCachePool;
    }

    /**
     * Clear Cache
     */
    public function cleanCache()
    {
        foreach ($this->frontendCachePool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
