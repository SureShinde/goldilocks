<?php
/**
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license/  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory;
use Plumrocket\PrivateSale\Model\Store\FrontendWebsites;

class Status extends Column
{
    /**
     * @var FrontendWebsites
     */
    private $websites;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $eventCollectionsByWebsite = [];

    /**
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param FrontendWebsites $websites
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        FrontendWebsites $websites,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->websites = $websites;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $currentField = $this->getData('name');

            if (in_array($currentField, ['enable', 'status'])) {
                foreach ($dataSource['data']['items'] as & $item) {
                    $data = [];
                    $isSingleStoreMode = $this->storeManager->isSingleStoreMode();
                    if ($isSingleStoreMode) {
                        if ($currentField === 'enable') {
                            $data[] = ['status' => $item['enable'], 'single_store_mode' => (int) $isSingleStoreMode];
                        } elseif ($currentField === 'status') {
                            $data[] = ['status' => $item['status'], 'single_store_mode' => (int) $isSingleStoreMode];
                        }

                    } else {
                        $websites = $this->websites->getList();
                        foreach ($websites as $website) {
                            $collection = $this->getEventCollectionByWebsite($website->getDefaultGroup()
                                ->getDefaultStoreId());
                            $event = $collection->getItemById($item['entity_id']);
                            if ($currentField === 'enable') {
                                $status = $event->isEnabled();
                                $data[] = [
                                    'status' => $status ? '1' : '0',
                                    'website' => $website->getName(),
                                    'single_store_mode' => (int) $isSingleStoreMode
                                ];
                            } elseif ($currentField === 'status') {
                                $status = $event->getStatus();
                                $data[] = [
                                    'status' => $status,
                                    'website' => $website->getName(),
                                    'single_store_mode' => (int) $isSingleStoreMode
                                ];
                            }
                        }
                    }

                    $item[$currentField] = $data;
                }
            }
        }

        return parent::prepareDataSource($dataSource);
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    private function getEventCollectionByWebsite($websiteId)
    {
        if (empty($this->eventCollectionsByWebsite[$websiteId])) {
            $collection = $this->collectionFactory->create()
                ->setStoreId($websiteId)
                ->addAttributeToSelect('*')
                ->addStatusToCollection();

            $this->eventCollectionsByWebsite[$websiteId] = $collection;
        }

        return $this->eventCollectionsByWebsite[$websiteId];
    }
}
