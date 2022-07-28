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
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Controller\Adminhtml\Event;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterfaceFactory;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Duplicate extends Action
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var EventInterfaceFactory
     */
    private $eventFactory;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param EventRepositoryInterface $eventRepository
     * @param EventInterfaceFactory $eventFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        EventRepositoryInterface $eventRepository,
        EventInterfaceFactory $eventFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->eventRepository = $eventRepository;
        $this->eventFactory = $eventFactory;
        $this->context = $context;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $storeId = $this->getRequest()->getParam('store', Store::DEFAULT_STORE_ID);
        $store = $this->storeManager->getStore($storeId);
        $this->storeManager->setCurrentStore($store->getCode());

        try {
            $eventModelData = $this->eventRepository->getById($id)->getData();
            $eventModelData['entity_id'] = null;
            $eventModelData['enable'] = Boolean::VALUE_NO;
            $duplicateModel = $this->eventFactory->create();
            $duplicateModel->addData($eventModelData);
            $this->eventRepository->save($duplicateModel);
            $this->messageManager->addSuccessMessage(__('The event has been duplicated.'));
            $result->setPath('*/*/edit', ['id' => $duplicateModel->getId()]);
        } catch (NoSuchEntityException $e) {
            $result->setPath('*/*/');
            $this->messageManager->addErrorMessage(__('We can\'t find a event to duplicate.'));
        }

        return $result;
    }
}
