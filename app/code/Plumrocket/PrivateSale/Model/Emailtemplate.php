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
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\PrivateSale\Model;

use Magento\Framework\Model\AbstractModel;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory;

class Emailtemplate extends AbstractModel
{
    /**
     * @var \Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection
     */
    protected $eventCollection;

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ResourceModel\Event\CollectionFactory
     */
    protected $eventCollectionFactory;

    /**
     * Emailtemplate constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param CollectionFactory $eventCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        CollectionFactory $eventCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->eventCollectionFactory = $eventCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Plumrocket\PrivateSale\Model\ResourceModel\Emailtemplate::class);
    }

    /**
     * Retrieve event collection
     *
     * @return \Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection
     */
    public function getEventsCollection()
    {
        $eventsIds = $this->getEventsIds();

        if (null === $this->eventCollection && $eventsIds) {
            $this->eventCollection = $this->eventCollectionFactory->create()
                ->setStoreId($this->getStoreId())
                ->addFieldToFilter('entity_id', ['in' => $eventsIds])
                ->addAttributeToSelect('*');
        }

        return $this->eventCollection;
    }

    /**
     * @return array|null
     */
    public function getEventsIds()
    {
        return $this->getData('events_ids');
    }

    /**
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterface $event
     * @return null|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEmailImage($event)
    {
        $image = $event->getNewsletterImage() ?: $event->getImage();
        return $image ? $event->getImageUrl($image) : null;
    }

    /**
     * Load events by criteria
     * @param string $date
     * @param int $storeId
     * @return \Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection
     */
    public function loadEventsByCriteria($date = null, $storeId = null)
    {
        /** @var \Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection $collection */
        $collection = $this->eventCollectionFactory->create();

        if (null === $storeId) {
            $storeId = $this->getStoreId();
        }

        if ($date === null) {
            $date = date('Y-m-d H:i:s');
        } else {
            $date = date('Y-m-d H:i:s', strtotime($date));
        }

        $collection->setStoreId((int) $storeId);
        $collection->addAttributeToSelect('event_name');

        return $collection->addAttributeToFilter([
            ['attribute' => 'event_from', 'lt' => $date],
            ['attribute' => 'event_from', 'is' => new \Zend_Db_Expr('NULL')]
        ], null, 'left')->addAttributeToFilter([
            ['attribute' => 'event_to', 'gt' => $date],
            ['attribute' => 'event_to', 'is' => new \Zend_Db_Expr('NULL')]
        ], null, 'left')->addAttributeToFilter('enable', 1, 'left');
    }

    /**
     * @param $events
     * @return array
     */
    public function eventsToOptions($events)
    {
        $result = [];
        foreach ($events as $event) {
            $result[] = [
                'value' => $event->getId(),
                'label' => $event->getName(),
            ];
        }
        return $result;
    }
}
