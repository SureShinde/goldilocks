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

namespace Plumrocket\PrivateSale\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Youtube preview image pattern
     */
    public const YOUTUBE_IMAGE_MEDIUM  = 'http://img.youtube.com/vi/_VIDEO_ID_/mqdefault.jpg';

    /**
     * Vimeo preview image api url
     */
    public const VIMEO_GET_VIDEO_IMG_URL = 'http://vimeo.com/api/v2/video/_VIDEO_ID_.json';

    public const VIMEO_EMBEDED_URL_SUFIX = 'https://player.vimeo.com/video/';

    public const YOUTUBE_EMBEDED_URL_SUFIX = 'https://www.youtube.com/embed/';

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    private $fileHandler;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Helper\Context            $context
     * @param \Magento\Framework\Filesystem\DriverInterface    $fileHandler
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\DriverInterface $fileHandler,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->fileHandler = $fileHandler;
        $this->serializer = $serializer;
    }

    /**
     * @param string $videoUrl
     * @return string|null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getPreviewImageUrl(string $videoUrl)
    {
        $url = '';

        if ($imageId = $this->getYoutubeVideoId($videoUrl)) {
            $url = $this->getYoutubePreviewImageUrl($imageId);
        } elseif ($imageId = $this->getVimeoId($videoUrl)) {
            $url = $this->getVimeoPreviewImageUrl($imageId);
        }

        return $url;
    }

    /**
     * @param string $youtubeUrl
     * @return string|null
     */
    public function getYoutubeVideoId(string $youtubeUrl)
    {
        $id = null;
        $regex = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';

        if (preg_match($regex, $youtubeUrl, $match)) {
            $id = $match[1] ?? null;
        }

        return $id;
    }

    /**
     * @param $vimeoUrl
     * @return string|null
     */
    public function getVimeoId($vimeoUrl)
    {
        $id = null;
        $regex = '/(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:[a-z]*\/)*([0-9]{6,11})[?]?.*/';

        if (preg_match($regex, $vimeoUrl, $match)) {
            $id = $match[1] ?? null;
        }

        return $id;
    }

    /**
     * @param string $youtubeId
     * @return string
     */
    public function getYoutubePreviewImageUrl(string $youtubeId)
    {
        return str_replace('_VIDEO_ID_', $youtubeId, self::YOUTUBE_IMAGE_MEDIUM);
    }

    /**
     * @param string $vimeoId
     * @return |null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getVimeoPreviewImageUrl(string $vimeoId)
    {
        $result = $this->fileHandler->fileGetContents(
            str_replace('_VIDEO_ID_', $vimeoId, self::VIMEO_GET_VIDEO_IMG_URL)
        );

        $result = $this->serializer->unserialize($result);

        return current($result)['thumbnail_medium'] ?? null;
    }

    /**
     * @param string $url
     * @return string
     */
    public function getEmbdedVideoUrl(string $url)
    {
        if ($imageId = $this->getYoutubeVideoId($url)) {
            $url = self::YOUTUBE_EMBEDED_URL_SUFIX . $imageId;
        } elseif ($imageId = $this->getVimeoId($url)) {
            $url = self::VIMEO_EMBEDED_URL_SUFIX . $imageId;
        }

         return $url;
    }

    /**
     * @param string $url
     * @return string|null
     */
    public function getVideoId(string $url)
    {
        return $this->getYoutubeVideoId($url) ?: $this->getVimeoId($url);
    }
}
