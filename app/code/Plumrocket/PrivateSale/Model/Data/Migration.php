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
 * @package     Plumrocket Private Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\Data;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterfaceFactory;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;
use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Plumrocket\PrivateSale\Model\Config\Source\SplashPageAccess;
use Plumrocket\PrivateSale\Model\Splashpage;
use Psr\Log\LoggerInterface;
use Plumrocket\PrivateSale\Model\ResourceModel\SplashPageImage\CollectionFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

class Migration
{
    /**
     * @var array
     */
    protected $attributes = [
        'privatesale_email_image' => 'event_image',
        'privatesale_date_start' => 'event_from',
        'privatesale_date_end' => 'event_to',
        'privatesale_before_event_start' => '',
        'privatesale_event_end' => '',
        'privatesale_private_event' => 'is_event_private',
        'privatesale_restrict_cgroup' => '',
        'privatesale_event_landing' => 'event_landing_page',
        'name' => ''
    ];

    /**
     * @var array
     */
    private $splashPageFieldMapping = [
        'enabled_page' => 'enabled',
        'become_text' => 'form_text',
        'launching_text' => 'confirmation_text',
        'enabled_registration' => 'access',
    ];

    /**
     * @var array
     */
    private $splashPageValueMapping = [
        'enabled_registration' => [
            '1' => SplashPageAccess::REGISTER,
            '0' => SplashPageAccess::LOGIN_AND_REGISTER,
        ],
        'is_new_files' => [
            '1' => '0'
        ],
    ];

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var EventInterfaceFactory
     */
    private $eventFactory;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Splashpage
     */
    private $splashpage;

    /**
     * @var CollectionFactory
     */
    private $splashpageImageFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var File
     */
    private $file;

    /**
     * Migration constructor.
     * @param File $file
     * @param Filesystem $filesystem
     * @param CollectionFactory $splashpageImageFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param EventInterfaceFactory $eventFactory
     * @param EventRepositoryInterface $eventRepository
     * @param Splashpage $splashpage
     * @param LoggerInterface $logger
     */
    public function __construct(
        File $file,
        Filesystem $filesystem,
        CollectionFactory $splashpageImageFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        EventInterfaceFactory $eventFactory,
        EventRepositoryInterface $eventRepository,
        Splashpage $splashpage,
        LoggerInterface $logger
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eventFactory = $eventFactory;
        $this->eventRepository = $eventRepository;
        $this->logger = $logger;
        $this->splashpage = $splashpage;
        $this->splashpageImageFactory = $splashpageImageFactory;
        $this->filesystem = $filesystem;
        $this->file = $file;
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function migrationEventsProcess()
    {
        try {
            return $this->migrateProductData() && $this->migrateCategoryData();
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * @throws \Exception
     */
    public function migrationSplashPageProcess()
    {
        $splashPageData = (array) $this->splashpage->getData();

        foreach ($splashPageData as $key => $value) {
            $newFieldName = $this->getMappedField($key);
            $splashPageData[$newFieldName] = $this->getMappedValue($key, $value);

            if ($newFieldName !== $key) {
                unset($splashPageData[$key]);
            }
        }

        if (! empty($splashPageData)) {
            $this->splashpage->setData($splashPageData)->save();
        }

        $this->migrationSplashPageImage();
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    protected function migrateCategoryData()
    {
        $data = [];

        $categoryCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect(array_keys($this->attributes))
            ->addAttributeToFilter([
                ['attribute' => 'privatesale_date_start', 'neq' => null],
                ['attribute' => 'privatesale_date_end', 'neq' => null],
                ['attribute' => 'privatesale_private_event', 'neq' => 0]
            ]);

        foreach ($categoryCollection as $category) {
            $categoryData = $this->retrieveEventData($category);
            $categoryData['category_event'] = $category->getId();
            $categoryData[EventInterface::EVENT_NAME] = $category->getName();
            $categoryData[EventInterface::EVENT_TYPE] = EventType::CATEGORY;

            $data[] = $categoryData;
        }

        return empty($data) ? false : $this->saveEvents($data);
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    protected function migrateProductData()
    {
        $data = [];

        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect(array_keys($this->attributes))
            ->addAttributeToFilter([
                ['attribute' => 'privatesale_date_start', 'neq' => null],
                ['attribute' => 'privatesale_date_end', 'neq' => null],
                ['attribute' => 'privatesale_private_event', 'neq' => 0]
            ]);

        foreach ($productCollection as $product) {
            $productData = $this->retrieveEventData($product);
            $productData['product_event'] = $product->getId();
            $productData[EventInterface::EVENT_NAME] = $product->getName();
            $productData[EventInterface::EVENT_TYPE] = EventType::PRODUCT;

            $data[] = $productData;
        }

        return count($data) > 0 ? $this->saveEvents($data) : false;
    }

    /**
     * @param $entity
     * @return array
     */
    private function retrieveEventData($entity)
    {
        $data = [];

        foreach (array_keys($this->attributes) as $attribute) {
            if ($this->attributes[$attribute] && $attributeData = $entity->getData($attribute)) {
                $data[$this->attributes[$attribute]] = $attributeData;
            }
        }

        return $data;
    }

    /**
     * @param $dataAttributes
     * @return bool
     */
    private function saveEvents($dataAttributes)
    {
        try {
            /** @var \Plumrocket\PrivateSale\Api\Data\EventInterface $event */
            $event = $this->eventFactory->create();

            foreach ($dataAttributes as $dataAttribute) {
                $event->addData($dataAttribute);
                $this->eventRepository->save($event);
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return false;
    }

    /**
     * @param $fieldName
     * @param $value
     * @return mixed
     */
    private function getMappedValue($fieldName, $value)
    {
        if (isset($this->splashPageValueMapping[$fieldName][$value])) {
            return $this->splashPageValueMapping[$fieldName][$value];
        }

        return $value;
    }

    /**
     * @param string $field
     * @return string
     */
    private function getMappedField(string $field)
    {
        if (isset($this->splashPageFieldMapping[$field])) {
            return $this->splashPageFieldMapping[$field];
        }

        return $field;
    }

    /**
     * @var void
     */
    private function migrationSplashPageImage()
    {
        $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $splashPageImageCollection = $this->splashpageImageFactory->create();

        try {
            $this->file->createDirectory($mediaPath . Splashpage::IMAGE_DIR);
        } catch (FileSystemException $e) {
            $this->logger->error($e->getMessage());
        }

        foreach ($splashPageImageCollection as $splashPageImage) {
            try {
                $oldImagePath = $mediaPath . 'splashpage' . $splashPageImage->getName();
                if ($this->file->isExists($oldImagePath)) {
                    $fileNameArray = explode('/', $splashPageImage->getName());
                    $fileName = end($fileNameArray);
                    $newImagePath = $mediaPath . Splashpage::IMAGE_DIR . '/' . $fileName;
                    if ($this->file->rename($oldImagePath, $newImagePath)) {
                        $splashPageImage->setName('/' . Splashpage::IMAGE_DIR . '/' . $fileName)->save();
                    }
                }
            } catch (FileSystemException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
