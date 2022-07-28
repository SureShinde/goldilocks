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

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Cms\Helper\Page;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Helper\Config;
use Plumrocket\PrivateSale\Model\Config\Source\Eventlandingpage;
use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Plumrocket\PrivateSale\Model\Event\GetEventProducts;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Indexer;
use Plumrocket\PrivateSale\Model\Indexer\Product as EventProductsIndexer;

class Event extends AbstractModel implements IdentityInterface, EventInterface
{
    /**
     * @var string
     */
    const ENTITY = 'plumrocket_privatesale_event';

    /**
     * @var string
     */
    const CACHE_TAG = 'prev';

    /**
     * Sub directory for media
     *
     * @var string
     */
    const IMAGE_DIR = 'plumrocket/privatesale/event';

    /**
     * Dir for temporary images
     */
    const IMAGE_TMP_DIR = 'plumrocket/privatesale/tmp';

    /**
     * @var string
     */
    const DM_HOMEPAGE = 'HOMEPAGE';

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var CurrentDateTime
     */
    protected $currentDateTime;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var FileInfo
     */
    private $fileInfo;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var Page
     */
    private $pageHelper;

    /**
     * @var Url
     */
    private $customerUrl;

    /**
     * @var \Plumrocket\PrivateSale\Model\CacheInvalidator
     */
    private $cacheInvalidator;

    /**
     * @var \Plumrocket\PrivateSale\Model\Event\GetEventProducts
     */
    private $getEventProducts;

    /**
     * @param \Plumrocket\PrivateSale\Model\CacheInvalidator               $cacheInvalidator
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Plumrocket\PrivateSale\Model\FileInfo                       $fileInfo
     * @param \Plumrocket\PrivateSale\Helper\Config                        $configHelper
     * @param \Magento\Framework\Indexer\IndexerRegistry                   $indexerRegistry
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface             $categoryRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface              $productRepository
     * @param \Magento\Cms\Helper\Page                                     $pageHelper
     * @param \Magento\Customer\Model\Url                                  $customerUrl
     * @param \Magento\Framework\App\Http\Context                          $httpContext
     * @param \Plumrocket\PrivateSale\Model\CurrentDateTime                $currentDateTime
     * @param \Plumrocket\PrivateSale\Model\Event\GetEventProducts         $getEventProducts
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        CacheInvalidator $cacheInvalidator,
        Context $context,
        Registry $registry,
        FileInfo $fileInfo,
        Config $configHelper,
        IndexerRegistry $indexerRegistry,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        Page $pageHelper,
        Url $customerUrl,
        HttpContext $httpContext,
        CurrentDateTime $currentDateTime,
        GetEventProducts $getEventProducts,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->fileInfo = $fileInfo;
        $this->configHelper = $configHelper;
        $this->indexerRegistry = $indexerRegistry;
        $this->categoryRepository = $categoryRepository;
        $this->pageHelper = $pageHelper;
        $this->customerUrl = $customerUrl;
        $this->httpContext = $httpContext;
        $this->currentDateTime = $currentDateTime;
        $this->productRepository = $productRepository;
        $this->cacheInvalidator = $cacheInvalidator;
        $this->getEventProducts = $getEventProducts;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Event::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG, self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return (bool) $this->getData(self::IS_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return (int) $this->getData(self::IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return (string) $this->getData(self::EVENT_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getProductId(): int
    {
        return (int) $this->getData(self::PRODUCT_EVENT);
    }

    /**
     * @inheritDoc
     */
    public function getCategoryId(): int
    {
        return (int) $this->getData(self::CATEGORY_EVENT);
    }

    /**
     * @return array
     */
    public function getVideo(): array
    {
        return (array) $this->getData(self::EVENT_VIDEO);
    }

    /**
     * @inheritDoc
     */
    public function getActiveFrom(): string
    {
        return (string) $this->getData(self::EVENT_FROM);
    }

    /**
     * @inheritDoc
     */
    public function getActiveTo(): string
    {
        return (string) $this->getData(self::EVENT_TO);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string) $this->getData(self::EVENT_DESCRIPTION);
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return (string) $this->getData(self::EVENT_IMAGE);
    }

    /**
     * @return string
     */
    public function getHeaderImage(): string
    {
        return (string) $this->getData(self::HEADER_IMAGE);
    }

    /**
     * @inheritDoc
     */
    public function getType(): int
    {
        return (int) $this->getData(self::EVENT_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): string
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): string
    {
        return (string) $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setEventName(string $name): EventInterface
    {
        $this->setData(self::EVENT_NAME, $name);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setEventType(int $type): EventInterface
    {
        $this->setData(self::EVENT_TYPE, $type);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $date): EventInterface
    {
        $this->setData(self::CREATED_AT, $date);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(string $date): EventInterface
    {
        $this->setData(self::UPDATED_AT, $date);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return (int) $this->getData(self::PRIORITY);
    }

    /**
     * @inheritDoc
     */
    public function setPriority(int $priority): EventInterface
    {
        $this->setData(self::PRIORITY, $priority);
        return $this;
    }

    /**
     * @param $imageName
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getImageData(string $imageName)
    {
        return $this->fileInfo->getImageData($imageName);
    }

    /**
     * @param int $action
     * @param null|string $storeId
     * @return bool
     */
    public function canMakeActionBeforeEventStarts(int $action, $storeId = null): bool
    {
        $permissions = $this->getData('event_permissions')
            ? $this->configHelper->getGeneralPermissionBeforeEventStarts($storeId)
            : (array) $this->getData('before_event_starts');

        return $this->isActionAllowed($permissions, $action);
    }

    /**
     * @param int $action
     * @param null|string $storeId
     * @return bool
     */
    public function canMakeActionAfterEventEnds(int $action, $storeId = null): bool
    {
        $permissions = $this->getData('event_permissions')
            ? $this->configHelper->getGeneralPermissionAfterEventEnds($storeId)
            : (array) $this->getData('after_event_ends');

        return $this->isActionAllowed($permissions, $action);
    }

    /**
     * @param int $groupId
     * @param int $action
     * @param null|string $storeId
     * @return bool
     */
    public function canCustomerGroupMakeActionOnPrivateSale(int $action, int $groupId = null, $storeId = null): bool
    {
        $permissions = [];

        if (null === $groupId) {
            $groupId = $this->getCustomerGroupId();
        }

        $privateSalePermissions = $this->getData('private_event_permissions')
            ? $this->configHelper->getPrivateSalePermission($storeId)
            : $this->getData('custom_permissions');

        $privateSalePermissions = array_chunk($privateSalePermissions, 3);

        foreach ($privateSalePermissions as $key => $data) {
            /** customer group exists only in first row */
            $firstElement = current($data);

            if (isset($firstElement['group'])
                && is_array($firstElement['group'])
                && in_array($groupId, $firstElement['group'])
            ) {
                $permissions = $data;
                break;
            }
        }

        return $this->isActionAllowed($permissions, $action);
    }

    /**
     * Retrieve url of image
     *
     * @param string $imageName
     * @return string
     */
    public function getImageUrl(string $imageName): string
    {
        return $this->fileInfo->getImageUrl($imageName);
    }

    /**
     * @return \Magento\Catalog\Api\Data\CategoryInterface|\Magento\Catalog\Api\Data\ProductInterface|null
     */
    public function getCatalogEntity()
    {
        try {
            if ($this->isCategoryEvent() && $categoryId = $this->getCategoryId()) {
                $entity = $this->categoryRepository->get($categoryId);
            } elseif ($productId = $this->getProductId()) {
                $entity = $this->productRepository->getById($productId);
            } else {
                $entity = null;
            }
        } catch (NoSuchEntityException $e) {
            $entity = null;
        }

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function isEventPrivate(): bool
    {
        return (bool) $this->getData('is_event_private');
    }

    /**
     * @inheritDoc
     */
    public function isProductEvent(): bool
    {
        return (int) $this->getData('event_type') === EventType::PRODUCT
            && $this->getData('product_event');
    }

    /**
     * @inheritDoc
     */
    public function isCategoryEvent(): bool
    {
        return (int) $this->getData('event_type') === EventType::CATEGORY
            && $this->getData('category_event');
    }

    /**
     * @inheritDoc
     */
    public function getPrivateSaleLandingPage(): string
    {
        $landingPage = $this->getData('event_landing_page');

        return $landingPage === (string) Eventlandingpage::USE_DEFAULT
            ? $this->configHelper->getPrivateSaleLandingPage()
            : $this->getData('event_landing_page');
    }

    /**
     * @return string
     */
    public function getPrivateSaleLandingPageUrl()
    {
        $redirectPage = $this->getPrivateSaleLandingPage();

        switch ($redirectPage) {
            case Eventlandingpage::LOGIN_PAGE:
                $url = $this->customerUrl->getLoginUrl();
                break;
            case Eventlandingpage::REGISTRATION_PAGE:
                $url = $this->customerUrl->getRegisterUrl();
                break;
            default:
                $url = $this->pageHelper->getPageUrl($redirectPage);
        }

        return $url;
    }

    /**
     * @inheritDoc
     */
    public function afterSave()
    {
        // This optimization is not working with Products and Discounts, but can be useful in future
//        if ($this->dataHasChangedFor(self::IS_PRIVATE)
//            || $this->dataHasChangedFor('enable')
//            || $this->dateHasChangedFor(self::EVENT_TO)
//            || $this->dateHasChangedFor(self::EVENT_FROM)
//            || $this->dataHasChangedFor(self::CATEGORY_EVENT)
//            || $this->dataHasChangedFor(self::PRODUCT_EVENT)
//            || $this->dataHasChangedFor(self::EVENT_TYPE)
//            || $this->dataHasChangedFor('priority')
//        ) {
            $this->_getResource()->addCommitCallback([$this, 'reindex']);
//        }

        $this->cacheInvalidator->invalidateCaches();

        return parent::afterSave();
    }

    /**
     * @inheritDoc
     */
    public function afterDelete()
    {
        $this->_getResource()->addCommitCallback([$this, 'reindex']);
        $this->cacheInvalidator->invalidateCaches();

        return parent::afterDelete();
    }

    /**
     * Make reindex products of event
     */
    public function reindex()
    {
        $entityToEventIndexer = $this->indexerRegistry->get(Indexer::INDEX_NAME);
        $entityToEventIndexer->reindexRow($this->getId());

        $eventsToProductsIndexer = $this->indexerRegistry->get(EventProductsIndexer::INDEXER_ID);
        if ($eventsToProductsIndexer->isScheduled()) {
            $eventsToProductsIndexer->invalidate();
        } elseif ($productIds = $this->getEventProducts->execute($this)) {
            $eventsToProductsIndexer->reindexList($productIds);
        }
    }

    /**
     * @param $field
     * @return bool
     */
    public function dateHasChangedFor($field)
    {
        $origData = strtotime((string) $this->getOrigData($field));
        $currentData = strtotime((string) $this->getData($field));

        return date('Y-m-d H:i:s', (int) $origData) != date('Y-m-d H:i:s', (int) $currentData);
    }

    /**
     * @param $permissions
     * @param $action
     * @return bool
     */
    private function isActionAllowed($permissions, $action)
    {
        $actionKey = array_keys($permissions);

        return isset($actionKey[$action], $permissions[$actionKey[$action]]['status'])
            && $permissions[$actionKey[$action]]['status'];
    }

    /**
     * @return int
     */
    private function getCustomerGroupId(): int
    {
        return (int) $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);
    }
}
