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

namespace Plumrocket\PrivateSale\Controller\Adminhtml\Event;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterfaceFactory;
use Plumrocket\PrivateSale\Api\Data\FlashSaleInterface;
use Plumrocket\PrivateSale\Api\Data\FlashSaleInterfaceFactory;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;
use Plumrocket\PrivateSale\Model\Config\Source\EventType;
use Plumrocket\PrivateSale\Model\PriceCalculation;
use Plumrocket\PrivateSale\Model\ResourceModel\FlashSale\CollectionFactory;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupFactory;
use Magento\Framework\Math\Random;

class Save extends Action
{
    /**
     * @var PriceCalculation
     */
    protected $priceCalculation;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var EventInterfaceFactory
     */
    private $eventFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionFactory
     */
    private $flashSaleCollectionFactory;

    /**
     * @var FlashSaleInterfaceFactory
     */
    private $flashSaleFactory;

    /**
     * @var CustomerGroupFactory
     */
    private $customerGroupFactory;

    /**
     * @var Random
     */
    private $random;

    /**
     * Save constructor.
     * @param Context $context
     * @param EventRepositoryInterface $eventRepository
     * @param EventInterfaceFactory $eventFactory
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $flashSaleCollectionFactory
     * @param FlashSaleInterfaceFactory $flashSaleFactory
     * @param PriceCalculation $priceCalculation
     * @param CustomerGroupFactory $customerGroupFactory
     * @param Random $random
     */
    public function __construct(
        Context $context,
        EventRepositoryInterface $eventRepository,
        EventInterfaceFactory $eventFactory,
        StoreManagerInterface $storeManager,
        CollectionFactory $flashSaleCollectionFactory,
        FlashSaleInterfaceFactory $flashSaleFactory,
        PriceCalculation $priceCalculation,
        CustomerGroupFactory $customerGroupFactory,
        Random $random
    ) {
        parent::__construct($context);

        $this->eventRepository = $eventRepository;
        $this->eventFactory = $eventFactory;
        $this->storeManager = $storeManager;
        $this->flashSaleCollectionFactory = $flashSaleCollectionFactory;
        $this->flashSaleFactory = $flashSaleFactory;
        $this->priceCalculation = $priceCalculation;
        $this->customerGroupFactory = $customerGroupFactory;
        $this->random = $random;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $request = $this->getRequest();
        $id = $request->getParam('entity_id');
        $storeId = $request->getParam('store', Store::DEFAULT_STORE_ID);
        $store = $this->storeManager->getStore($storeId);
        $this->storeManager->setCurrentStore($store->getCode());
        $redirectBack = $request->getParam('back', false);
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $params = $request->getParams();
        $eventType = (int) $request->getParam('event_type');
        $params = $this->prepareData($params);

        try {
            if (! $id) {
                $params['entity_id'] = null;
                /** @var \Plumrocket\PrivateSale\Api\Data\EventInterface $event */
                $event = $this->eventFactory->create();
            } else {
                $event = $this->eventRepository->getById($id, $storeId);
            }

            if (EventType::PRODUCT === $eventType) {
                $params['category_event'] = null;
            } else {
                $params['product_event'] = null;
            }

            $params = $this->videoFieldsMapping($params, $event);
            $event->setData($params);

            if (isset($params['use_default'])) {
                foreach ($params['use_default'] as $attributeCode => $attributeValue) {
                    if ($attributeValue) {
                        $event->setData($attributeCode, null);
                    }
                }
            }

            $this->eventRepository->save($event);
            $id = $event->getId();
            $this->saveFlashSales($id);
            $this->priceCalculation->recalculationPrice();
            $this->messageManager->addSuccessMessage(__('Event successfully saved'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (FileSystemException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the event.'));
        }

        if ('edit' === $redirectBack) {
            $result->setPath('prprivatesale/event/edit', ['id' => $id, 'store' => $storeId]);
        } else {
            $result->setPath('prprivatesale/event/index');
        }

        return $result;
    }

    /**
     * @param $params
     * @return array
     */
    private function videoFieldsMapping($params, $event)
    {
        $values = [];
        $removed = '0';

        if (isset($params['event']['event_video']['images'])) {
            foreach ($params['event']['event_video']['images'] as $hash => $value) {
                $values[] = $value;

                if (isset($value['removed'])) {
                    $removed = $value['removed'];
                }
            }
        }

        if ($removed === '1') {
            $params['event_video'] = '';
        } else {
            $params['event_video'] = $values;
        }

        if (empty($params['event_video']) && ! empty($event->getVideo()) && $removed !== '1') {
            $params['event_video'] = $event->getVideo();
        }

        return $params;
    }

    /**
     * @param int $eventId
     * @throws \Exception
     */
    private function saveFlashSales(int $eventId)
    {
        $flashSaleData = $this->getRequest()->getParam('flash_sale_data', []);
        $flashSaleCollection = $this->getFlashSaleCollection($eventId);

        foreach ($flashSaleData as $data) {
            $salePrice = $data['sale_price'] ?? 0;
            $discount = $data['discount_amount_percent'] ?? 0;
            $qtyLimit = $data['flash_sale_qty_limit'];

            /** @var \Plumrocket\PrivateSale\Api\Data\FlashSaleInterface|null $currentItem */
            $currentItem = $flashSaleCollection->getItemByColumnValue(
                FlashSaleInterface::PRODUCT_ID,
                $data['product_id']
            );

            if (! $currentItem) {
                $currentItem = $this->flashSaleFactory->create()
                    ->setProductId((int) $data['product_id']);
                $flashSaleCollection->addItem($currentItem);
            } elseif (! $salePrice && ! $discount && $qtyLimit === '') {
                $flashSaleCollection->removeItemByKey($currentItem->getId());
                $currentItem->delete();
                continue;
            }

            $currentItem->setSalePrice((float) $salePrice)
                ->setDiscount((float) $discount)
                ->setQtyLimit((int) $qtyLimit)
                ->setEventId($eventId);
        }

        $flashSaleCollection->save();
    }

    /**
     * @param int $eventId
     * @return \Plumrocket\PrivateSale\Model\ResourceModel\FlashSale\Collection
     */
    private function getFlashSaleCollection(int $eventId)
    {
        $productIds = array_column($this->getRequest()->getParam('flash_sale_data', []), 'entity_id');
        $collection = $this->flashSaleCollectionFactory->create()
            ->addFieldToFilter(FlashSaleInterface::EVENT_ID, $eventId);

        if ($productIds) {
            $collection->addFieldToFilter(FlashSaleInterface::PRODUCT_ID, ['in' => $productIds]);
        }

        return $collection;
    }

    /**
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        if (! isset($data[EventInterface::EVENT_IMAGE])) {
            $data[EventInterface::EVENT_IMAGE] = null;
        }

        if (! isset($data['newsletter_image'])) {
            $data['newsletter_image'] = null;
        }

        if (! isset($data['small_image'])) {
            $data['small_image'] = null;
        }

        if (! isset($data['header_image'])) {
            $data['header_image'] = null;
        }

        if (isset($data['custom_permissions'])) {
            $checkedCustomerGroupsIds = [];

            foreach ($data['custom_permissions'] as $permission) {
                if (isset($permission['group'])) {
                    foreach ($permission['group'] as $item) {
                        $checkedCustomerGroupsIds[] = $item;
                    }
                }
            }

            $allCustomerGroupIds = $this->customerGroupFactory->create()->toOptionArray();

            $allDeny = [];
            foreach ($allCustomerGroupIds as $groupId) {
                if (!in_array($groupId['value'], $checkedCustomerGroupsIds, true)) {
                    $allDeny[] = $groupId['value'];
                }
            }

            if (! empty($allDeny)) {
                $randomString = '_' . $this->random->getRandomNumber() . '_';

                $data['custom_permissions'][$randomString . '1'] = [
                    'group' => $allDeny,
                    'label' => 'Browsing Event',
                    'status' => '0'
                ];

                $data['custom_permissions'][$randomString . '2'] = [
                    'label' => 'Show Product Prices',
                    'status' => '0'
                ];

                $data['custom_permissions'][$randomString . '3'] = [
                    'label' => 'Show Add to Cart Button',
                    'status' => '0'
                ];
            }
        }

        return $data;
    }
}
