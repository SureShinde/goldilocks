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

namespace Plumrocket\PrivateSale\Block\Adminhtml\Event\Helper\Form\Gallery;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\MediaStorage\Helper\File\Storage\Database;

class Content extends \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content
{
    /**
     * @var array
     */
    protected $mediaAttributeCodes = ['image'];

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    private $attribute;

    /**
     * @var \Plumrocket\PrivateSale\Api\EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var Database
     */
    private $fileStorageDatabase;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    /**
     * Content constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attribute
     * @param \Plumrocket\PrivateSale\Api\EventRepositoryInterface $eventRepository
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param Database $fileStorageDatabase
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attribute,
        \Plumrocket\PrivateSale\Api\EventRepositoryInterface $eventRepository,
        \Magento\Framework\Serialize\Serializer\Json $json,
        Database $fileStorageDatabase,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $jsonEncoder,
            $mediaConfig,
            $data,
            null,
            null
        );

        $this->eventRepository = $eventRepository;
        $this->attribute = $attribute;
        $this->json = $json;

        $this->fileStorageDatabase = $fileStorageDatabase
            ?: ObjectManager::getInstance()->get(Database::class);
    }

    /**
     * @return array
     */
    public function getImageTypes()
    {
        $imageTypes = [];

        foreach ($this->getMediaAttributes() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */

            $value = ''; //empty

            $imageTypes[$attribute->getAttributeCode()] = [
                'code' => $attribute->getAttributeCode(),
                'value' => $value,
                'label' => $attribute->getFrontend()->getLabel(),
                'scope' => __('[STORE VIEW]'),
                'name' => 'event[' . $attribute->getAttributeCode() . ']',
            ];
        }

        return $imageTypes;
    }

    /**
     * @return array|\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function getMediaAttributes()
    {
        return $this->attribute
            ->addFieldToFilter('attribute_code', ['in' => [$this->mediaAttributeCodes]])
            ->load();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImagesJson()
    {
        $eventId = $this->getRequest()->getParam('id');

        if (! $eventId) {
            return '[]';
        }

        $eventModel = $this->eventRepository->getById($eventId);

        if ($eventModel && $eventVideo = $eventModel->getVideo()) {
            if (is_array($eventVideo) && isset($eventVideo[0])) {
                $videoParam = $eventVideo[1] ?? $eventVideo[0];

                if (is_array($videoParam)) {
                    $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
                    $videoParam['url'] = $this->_mediaConfig->getTmpMediaUrl($videoParam['file']);

                    if ($this->fileStorageDatabase->checkDbUsage() &&
                        !$mediaDir->isFile($this->_mediaConfig->getTmpMediaUrl($videoParam['file']))
                    ) {
                        $this->fileStorageDatabase->saveFileToFilesystem(
                            $this->_mediaConfig->getTmpMediaUrl($videoParam['file'])
                        );
                    }

                    return '[' . $this->json->serialize($videoParam) . ']';
                }
            }
        }

        return '[]';
    }

    /**
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $block = parent::_prepareLayout();
        $this->_template = 'Plumrocket_PrivateSale::form/event/video.phtml';

        return $block;
    }
}
