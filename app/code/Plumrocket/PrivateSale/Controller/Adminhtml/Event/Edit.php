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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;
use Plumrocket\PrivateSale\Model\GetCurrentEventService;

class Edit extends Action
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param EventRepositoryInterface $eventRepository
     * @param StoreManagerInterface $storeManager
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        Context $context,
        EventRepositoryInterface $eventRepository,
        StoreManagerInterface $storeManager,
        SessionManagerInterface $sessionManager
    ) {
        parent::__construct($context);
        $this->eventRepository = $eventRepository;
        $this->storeManager = $storeManager;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $storeId = (int) $this->getRequest()->getParam('store', null);
        $store = $this->storeManager->getStore($storeId);
        $this->storeManager->setCurrentStore($store->getCode());
        $id = (int) $this->getRequest()->getParam('id');
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        try {
            if ($id) {
                $this->sessionManager->setData(GetCurrentEventService::SESSION_NAME, $id);
                $title = $this->eventRepository->getById($id, $storeId)->getName();
            } else {
                $title = __('Add New Event');
            }
        } catch (NoSuchEntityException $e) {
            $title = __('Add New Event');
        }

        $result->getConfig()->getTitle()->prepend($title);
        $result->addBreadcrumb($title, $title);

        return $result;
    }
}
