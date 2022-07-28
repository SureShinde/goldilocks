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

use Magento\PageCache\Model\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use Magento\Framework\App\Cache\Type\Block as BlockCache;
use Magento\PageCache\Model\Cache\Type as PageCache;

class CacheInvalidator
{
    /**
     * @var Config
     */
    private $pageCacheConfig;

    /**
     * @var TypeListInterface
     */
    private $typeList;

    /**
     * CacheInvalidator constructor.
     * @param Config $pageCacheConfig
     * @param TypeListInterface $typeList
     */
    public function __construct(
        Config $pageCacheConfig,
        TypeListInterface $typeList
    ) {
        $this->pageCacheConfig = $pageCacheConfig;
        $this->typeList = $typeList;
    }

    /**
     * @return void
     */
    public function invalidateCaches()
    {
        if ($this->pageCacheConfig->isEnabled()) {
            $this->typeList->invalidate(PageCache::TYPE_IDENTIFIER);
        }

        foreach ($this->getCacheList() as $typeCode) {
            $this->typeList->invalidate($typeCode);
        }
    }

    /**
     * @return array
     */
    private function getCacheList()
    {
        return [
            ConfigCache::TYPE_IDENTIFIER,
            BlockCache::TYPE_IDENTIFIER
        ];
    }
}
