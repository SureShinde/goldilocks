<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\SpecialCustomerProgram\Helper;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class File
 * @package Magenest\CustomB2bRegistration\Helper
 */
class File extends AbstractHelper
{
    /**
     * @var string
     */
    protected $_templateMediaPath;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Database
     */
    protected $_coreFileStorageDatabase;

    /**
     * @var WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Database $coreFileStorageDatabase
     * @param Filesystem $filesystem
     * @param string $templateMediaPath
     * @throws FileSystemException
     */
    public function __construct(
        Context               $context,
        StoreManagerInterface $storeManager,
        Database              $coreFileStorageDatabase,
        Filesystem            $filesystem,
        string                $templateMediaPath = 'SpecialCustomerProgram/CI/file'
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_templateMediaPath = $templateMediaPath;
        $this->_coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Read and remove images which create from before 3 days ago
     *
     * @param null $path
     * @throws FileSystemException
     */
    public function removeTmpFiles($path = null)
    {
        if (is_null($path)) {
            $path = $this->getBaseTmpMediaPath();
        }

        $items = $this->_mediaDirectory->read($path);
        foreach ($items as $item) {
            if ($this->_mediaDirectory->isDirectory($item)) {
                $this->removeTmpFiles($item);
            } else {
                $file = $this->_mediaDirectory->getAbsolutePath($item);
                if (filemtime($file) < strtotime('-3days')) {
                    $this->_mediaDirectory->delete($item);
                }
            }
        }

        if (!sizeof($items)) {
            $this->_mediaDirectory->delete($path);
        }
    }

    /**
     * Filesystem directory path of temporary product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseTmpMediaPath()
    {
        return $this->_templateMediaPath . '/tmp';
    }

    /**
     * Remove file
     *
     * @param $file
     */
    public function removeFile($file)
    {
        $this->_mediaDirectory->delete($this->getMediaPath($file));
    }

    /**
     * Get media path
     *
     * @param $file
     * @return string
     */
    public function getMediaPath($file)
    {
        return $this->getBaseMediaPath() . '/' . $this->_prepareFile($file);
    }

    /**
     * Get base media path
     *
     * @return string
     */
    public function getBaseMediaPath()
    {
        return $this->_templateMediaPath;
    }

    /**
     * Prepare file
     *
     * @param string $file
     * @return string
     */
    protected function _prepareFile($file)
    {
        $file = Uploader::getDispretionPath($file) . '/' . $file;

        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * Checking file for moving and move it
     *
     * @param $tmpFileName
     * @return string
     * @throws LocalizedException
     *
     */
    public function moveFileFromTmp($tmpFileName)
    {
        $baseTmpImagePath = $this->getTmpMediaPath($tmpFileName);
        $baseImagePath = $this->_getNotDuplicatedFilename($baseTmpImagePath);

        try {
            $this->_coreFileStorageDatabase->copyFile($baseTmpImagePath, $baseImagePath);
            $this->_mediaDirectory->renameFile($baseTmpImagePath, $baseImagePath);
        } catch (Exception $e) {
            throw new LocalizedException(__('Something went wrong while saving the file(s).'));
        }

        return pathinfo($baseImagePath)['basename'];
    }

    /**
     * Part of URL of temporary product images
     * relatively to media folder
     *
     * @param string $file
     * @return string
     */
    public function getTmpMediaPath($file)
    {
        return $this->getBaseTmpMediaPath() . '/' . $this->_prepareFile($file);
    }

    /**
     * Get filename which is not duplicated with other files in media temporary and media directories
     *
     * @param string $fileName
     * @return string
     */
    protected function _getNotDuplicatedFilename($fileName)
    {
        $fileMediaName = Uploader::getNewFileName($this->_mediaDirectory->getAbsolutePath($fileName));
        $fileMediaName = $this->getBaseMediaPath() . Uploader::getDispretionPath($fileMediaName) . '/' . $fileMediaName;

        if ($fileMediaName != $fileName) {
            return $this->_getNotDuplicatedFilename($fileMediaName);
        }

        return $fileMediaName;
    }

    /**
     * Get full file options
     *
     * @param $file
     * @return array
     */
    public function getFullFileOptions($file)
    {
        try {
            $size = $this->_mediaDirectory->stat($this->getMediaPath($file))['size'];
            $type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->_mediaDirectory->getAbsolutePath() . $this->getMediaPath($file));
        } catch (Exception $e) {
            $size = 0;
            $type = '';
        }

        return [
            'correct' => $file,
            'file' => $this->_prepareFile($file),
            'type' => $type,
            'url' => $this->getMediaUrl($file),
            'size' => $size
        ];
    }

    /**
     * Get media url
     *
     * @param string $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->getBaseMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * Get base media url
     *
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $this->getBaseMediaPath();
    }

    /**
     * Get tmp media url
     *
     * @param string $file
     * @return string
     */
    public function getTmpMediaUrl($file)
    {
        return $this->getBaseTmpMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * Get base tmp media url
     *
     * @return string
     */
    public function getBaseTmpMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $this->getBaseTmpMediaPath();
    }
}
