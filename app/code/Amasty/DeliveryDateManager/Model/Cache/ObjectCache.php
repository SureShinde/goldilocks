<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Cache;

use Amasty\DeliveryDateManager\Api\Data\ChannelSetResultsInterface;
use Magento\Framework\Serialize\SerializerInterface;

class ObjectCache
{
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ChannelSetHydrator
     */
    private $hydrator;

    /**
     * @var string
     */
    private $cacheIdPrefix;

    /**
     * @var array
     */
    private $cacheTags;

    /**
     * @var null
     */
    private $cacheLifetime;

    public function __construct(
        \Magento\Framework\Cache\FrontendInterface $cache,
        SerializerInterface $serializer,
        ChannelSetHydrator $hydrator,
        $cacheIdPrefix = '',
        array $cacheTags = [],
        $cacheLifetime = null
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->hydrator = $hydrator;
        $this->cacheIdPrefix = $cacheIdPrefix;
        $this->cacheTags = $cacheTags;
        $this->cacheLifetime = $cacheLifetime;
    }

    /**
     * @param string $cacheKey
     *
     * @return ChannelSetResultsInterface|null
     */
    public function load(string $cacheKey): ?ChannelSetResultsInterface
    {
        $data = $this->cache->load($this->getIdentifierByCackeKey($cacheKey));
        if ($data === false) {
            return null;
        }

        $data = $this->serializer->unserialize($data);

        return $this->hydrator->hydrate($data);
    }

    /**
     * @param ChannelSetResultsInterface $object
     * @param string $cacheKey
     * @param array $cacheTags
     */
    public function save(ChannelSetResultsInterface $object, string $cacheKey, array $cacheTags = []): void
    {
        $cacheTags = array_merge($cacheTags, $this->cacheTags);
        $data = $this->hydrator->extract($object);
        $this->cache->save(
            $this->serializer->serialize($data),
            $this->getIdentifierByCackeKey($cacheKey),
            $cacheTags,
            $this->cacheLifetime
        );
    }

    private function getIdentifierByCackeKey(string $cacheKey)
    {
        return $this->cacheIdPrefix . $cacheKey;
    }
}
