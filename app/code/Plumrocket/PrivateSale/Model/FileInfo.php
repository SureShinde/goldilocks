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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class FileInfo
{
    const IMAGE_DIR = 'plumrocket/privatesale';

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * FileInfo constructor.
     *
     * @param Filesystem $fileSystem
     * @param Mime $mime
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Filesystem $fileSystem, Mime $mime, StoreManagerInterface $storeManager)
    {
        $this->fileSystem = $fileSystem;
        $this->mime = $mime;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $fileName
     * @return array
     * @throws FileSystemException
     */
    public function getStaticData(string $fileName): array
    {
        return $this->getMediaDirectory()->stat($fileName);
    }

    /**
     * @param string $fileName
     * @return string
     * @throws FileSystemException
     */
    public function getMimeType(string $fileName): string
    {
        $absoluteFilePath = $this->getMediaDirectory()->getAbsolutePath($fileName);
        return $this->mime->getMimeType($absoluteFilePath);
    }

    /**
     * @param string $fileName
     * @return bool
     * @throws FileSystemException
     */
    public function isExist(string $fileName): bool
    {
        return $this->getMediaDirectory()->isExist($fileName);
    }

    /**
     * Gets image name from $value array.
     *
     * @param array $value
     * @return string
     */
    public function getUploadedImageName(array $value): string
    {
        if (isset($value[0]['name'])) {
            return $value[0]['name'];
        }

        return '';
    }

    /**
     * @param string $fileName
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function delete(string $fileName): bool
    {
        if (! $fileName) {
            return false;
        }

        return $this->getMediaDirectory()->delete($fileName);
    }


    /**
     * Check if temporary file is available for new image upload.
     *
     * @param array $value
     * @return bool
     */
    public function isTmpFileAvailable(array $value): bool
    {
        return isset($value[0]['tmp_name']);
    }

    /**
     * @param string $fileName
     * @return false|string
     */
    public function getFileNameFromMediaDir(string $fileName)
    {
        return substr(
            $fileName,
            strpos($fileName, DirectoryList::MEDIA) + strlen(DirectoryList::MEDIA)
        );
    }

    /**
     * @param $fileName
     */
    public function fileResidesOutsidePrivateSaleDir($fileUrl)
    {
        return false === strpos($this->getFileNameFromMediaDir($fileUrl), self::IMAGE_DIR);
    }

    /**
     * Retrieve url of image
     *
     * @param string $imageName
     * @return string|null
     */
    public function getImageUrl(string $imageName): string
    {
        $url = '';

        try {
            if ($this->isExist($imageName)) {
                $store = $this->storeManager->getStore();
                $mediaBaseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
                $url = $mediaBaseUrl . ltrim($imageName, '/');
            }
        } catch (FileSystemException $e) {
            return $url;
        } catch (NoSuchEntityException $e) {
            return $url;
        }

        return $url;
    }

    /**
     * @param string $imageName
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getImageData(string $imageName)
    {
        $stat = $this->getStaticData($imageName);

        return [
            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            'name' => basename($imageName),
            'url' => $this->getImageUrl($imageName),
            'size' => $stat['size'] ?? 0,
            'type' => $this->getMimeType($imageName),
        ];
    }

    /**
     * @return WriteInterface
     * @throws FileSystemException
     */
    private function getMediaDirectory(): WriteInterface
    {
        return $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
    }
}
