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

namespace Plumrocket\PrivateSale\Model\File;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Uploader as MediaStorageUploader;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;

class Uploader extends MediaStorageUploader
{
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\File\Mime
     */
    private $fileMime;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Uploader constructor.
     *
     * @param $fileId
     * @param Database $coreFileStorageDb
     * @param Storage $coreFileStorage
     * @param NotProtectedExtension $validator
     * @param RequestInterface $request
     * @param Mime|null $fileMime
     * @param DirectoryList|null $directoryList
     */
    public function __construct(
        $fileId,
        Database $coreFileStorageDb,
        Storage $coreFileStorage,
        NotProtectedExtension $validator,
        RequestInterface $request,
        Mime $fileMime = null,
        DirectoryList $directoryList = null
    ) {
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_coreFileStorage = $coreFileStorage;
        $this->_validator = $validator;
        $this->directoryList= $directoryList ?: ObjectManager::getInstance()->get(DirectoryList::class);
        $this->fileMime = $fileMime ?: ObjectManager::getInstance()->get(Mime::class);
        $this->request = $request;
        $this->setUploadFileId($fileId);
    }

    /**
     * Used to check if uploaded file mime type is valid or not
     *
     * @param string[] $validTypes
     * @access public
     * @return bool
     */
    public function checkMimeType($validTypes = [])
    {
        if (! empty($validTypes) && count($validTypes) > 0) {
            if (! in_array($this->getMimeType(), $validTypes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    private function setUploadFileId($fileId)
    {
        /** Magento file uploader is not working when upload image from dynamic rows */
        $files = $this->request->getFiles()->toArray();
        $files[current(array_keys($files))] = current(current(current($files)));

        if (empty($files)) {
            throw new \DomainException('$files array is empty');
        }

        preg_match('/^(.*?)\[.\]\[(.*?)\]$/', $fileId, $file);

        if (is_array($file) && count($file) > 0 && !empty($file[0]) && !empty($file[1])) {
            array_shift($file);
            $this->_uploadType = self::MULTIPLE_STYLE;
            $fileAttributes = $files[$file[0]];
            $tmpVar = [];

            foreach ($fileAttributes as $attributeName => $attributeValue) {
                $tmpVar[$attributeName] = $attributeValue[$file[1]];
            }

            $fileAttributes = $tmpVar;
            $this->_file = $fileAttributes;
        } elseif (!empty($fileId) && isset($files[$fileId])) {
            $this->_uploadType = self::SINGLE_STYLE;
            $this->_file = $files[$fileId];
        } elseif ($fileId == '') {
            throw new \InvalidArgumentException(
                'Invalid parameter given. A valid $files[] identifier is expected.'
            );
        }
    }

    /**
     * @inheritDoc
     */
    private function getMimeType()
    {
        return $this->fileMime->getMimeType($this->_file['tmp_name']);
    }
}
