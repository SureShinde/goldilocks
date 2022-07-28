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
use Magento\Framework\Filesystem;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class SplashPageImage extends AbstractModel
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var FileInfo
     */
    protected $fileInfo;

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SplashPageImage constructor.
     *
     * @param Context $context
     * @param ImageUploader $imageUploader
     * @param Filesystem $fileSystem
     * @param FileInfo $fileInfo
     * @param Registry $registry
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ImageUploader $imageUploader,
        Filesystem $fileSystem,
        FileInfo $fileInfo,
        Registry $registry,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->fileSystem = $fileSystem;
        $this->fileInfo = $fileInfo;
        $this->imageUploader = $imageUploader;
        $this->logger = $logger;

        parent::__construct(
            $context,
            $registry,
            null,
            null,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\SplashPageImage::class);
    }

    /**
     * @param string|array $name
     * @return $this
     */
    public function setName($name)
    {
        if (is_array($name)) {
            if ($this->fileInfo->fileResidesOutsidePrivateSaleDir($name['url'])) {
                $name = $this->fileInfo->getFileNameFromMediaDir($name['url']);
            } else {
                $name = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath()
                    . \Plumrocket\PrivateSale\Model\SplashPage::IMAGE_DIR
                    . DIRECTORY_SEPARATOR
                    . $name['name'];

                $name = $this->fileInfo->getFileNameFromMediaDir($name);
            }
        }

        $this->setData('name', $name);

        return $this;
    }

    /**
     * Load files
     * @return self
     */
    public function loadImage(array $value)
    {
        if ($this->fileInfo->isTmpFileAvailable($value)
            && $imageName = $this->fileInfo->getUploadedImageName($value)
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
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getImageData()
    {
        return $this->fileInfo->getImageData((string) $this->getName());
    }

    /**
     * Retrieve url of image
     *
     * @param null $imageName
     * @return string|null
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrl()
    {
        return $this->fileInfo->getImageUrl((string) $this->getName());
    }
}
