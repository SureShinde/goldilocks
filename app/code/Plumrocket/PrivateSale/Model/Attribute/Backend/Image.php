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

namespace Plumrocket\PrivateSale\Model\Attribute\Backend;

use Magento\Catalog\Model\ImageUploader;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Plumrocket\PrivateSale\Model\FileInfo;
use Psr\Log\LoggerInterface;

class Image extends AbstractBackend
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var array|null
     */
    private $uploadImageData;

    /**
     * @var FileInfo
     */
    private $fileHandler;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * SplashPageImage constructor.
     *
     * @param ImageUploader $imageUploader
     * @param FileInfo $fileHandler
     * @param Filesystem $fileSystem
     * @param LoggerInterface $logger
     */
    public function __construct(
        ImageUploader $imageUploader,
        FileInfo $fileHandler,
        Filesystem $fileSystem,
        LoggerInterface $logger
    ) {
        $this->imageUploader = $imageUploader;
        $this->logger = $logger;
        $this->fileHandler = $fileHandler;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $value = $object->getData($attributeName);

        if (is_array($value)) {
            if ($this->fileHandler->fileResidesOutsidePrivateSaleDir($value[0]['url'])) {
                $name = $this->fileHandler->getFileNameFromMediaDir($value[0]['url']);
            } else {
                $name = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath()
                    . \Plumrocket\PrivateSale\Model\Event::IMAGE_DIR
                    . DIRECTORY_SEPARATOR
                    . $value[0]['name'];

                $name = $this->fileHandler->getFileNameFromMediaDir($name);
            }

            $this->setUploadedImageData($value);
            $object->setData($attributeName, $name);
        }

        return parent::beforeSave($object);
    }

    /**
     * @inheritDoc
     */
    public function afterSave($object)
    {
        $value = $this->getUploadedImageData();

        if ($this->fileHandler->isTmpFileAvailable($value)
            && $imageName = $this->fileHandler->getUploadedImageName($value)
        ) {
            try {
                $this->imageUploader->moveFileFromTmp($imageName);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function beforeDelete($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $value = (string) $object->getData($attributeCode);

        // remove only from our directory
        if ($value && strpos($value, '/plumrocket/') === 0 && $this->fileHandler->isExist($value)) {
            $this->fileHandler->delete($value);
        }

        return parent::beforeDelete($object);
    }

    /**
     * Use upload image data after save
     *
     * @return array
     */
    private function getUploadedImageData()
    {
        return (array) $this->uploadImageData;
    }

    /**
     * Set upload image data before save
     *
     * @param array $data
     * @return $this
     */
    private function setUploadedImageData(array $data)
    {
        $this->uploadImageData = $data;
        return $this;
    }
}
