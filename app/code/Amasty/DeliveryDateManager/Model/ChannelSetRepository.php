<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model;

class ChannelSetRepository
{
    private $storage = [];

    /**
     * @var ChannelSetCollector
     */
    private $channelSetCollector;

    /**
     * @var DeliveryChannelScope\ScopeRegistry
     */
    private $scopeRegistry;

    /**
     * @var Cache\ObjectCache
     */
    private $cache;

    public function __construct(
        \Amasty\DeliveryDateManager\Model\ChannelSetCollector $channelSetCollector,
        \Amasty\DeliveryDateManager\Model\DeliveryChannelScope\ScopeRegistry $scopeRegistry,
        \Amasty\DeliveryDateManager\Model\Cache\ObjectCache $cache
    ) {
        $this->channelSetCollector = $channelSetCollector;
        $this->scopeRegistry = $scopeRegistry;
        $this->cache = $cache;
    }

    /**
     * @return ChannelSetResults
     */
    public function getByScope(): ChannelSetResults
    {
        $key = $this->scopeRegistry->getCacheKey();
        if (!isset($this->storage[$key])) {
            $channelSet = $this->cache->load($key);

            if ($channelSet === null) {
                $channelSet = $this->channelSetCollector->collectChannelSet();
                $this->cache->save($channelSet, $key, [ChannelSetResults::CACHE_TAG]);
            }

            $this->storage[$key] = $channelSet;
        }

        return $this->storage[$key];
    }
}
