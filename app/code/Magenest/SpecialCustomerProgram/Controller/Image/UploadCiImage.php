<?php
/**
 * @copyright Copyright (c) magenest.com, Inc. (https://www.magenest.com)
 */

namespace Magenest\SpecialCustomerProgram\Controller\Image;

use Exception;
use Magenest\SpecialCustomerProgram\Helper\File;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\MediaStorage\Model\File\Uploader;

class UploadCiImage extends Action
{
    /**
     * @var ReadInterface
     */
    protected $_mediaDirectory;

    /**
     * @var File
     */
    protected $_fileHelper;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Filesystem $filesystem
     * @param File $fileHelper
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        File $fileHelper
    ) {
        $this->_fileHelper     = $fileHelper;
        $this->_mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            /** @var Uploader $uploader */
            $uploader = $this->_objectManager->create(
                'Magento\MediaStorage\Model\File\Uploader',
                ['fileId' =>  'file']
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);

            $result            = $uploader->save($this->_mediaDirectory->getAbsolutePath($this->_fileHelper->getBaseTmpMediaPath()));
            $result['correct'] = explode('/', $result['file']);
            $result['correct'] = end($result['correct']);
            $result['url']     = $this->_fileHelper->getTmpMediaUrl($result['correct']);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $result  = ['message' => __($message), 'error' => true];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
