<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Controller\Adminhtml\Notification;

use Amasty\PreOrderRelease\Model\Notification\ProcessCollection;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

class Send extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Amasty_PreOrderRelease::send';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var ProcessCollection
     */
    private $processCollection;

    public function __construct(
        CollectionFactory $collectionFactory,
        Filter $filter,
        ProcessCollection $processCollection,
        Context $context
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->processCollection = $processCollection;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setRefererUrl();

        try {
            /** @var Collection $collection */
            $collection = $this->filter->getCollection($this->collectionFactory->create());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Could not create products collection.'));
            return $redirect;
        }

        try {
            $products = $this->processCollection->execute($collection);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $redirect;
        }

        if (empty($products)) {
            $this->messageManager->addErrorMessage('Selected products do not meet the notification criteria.');
        } elseif (count($products) !== count($collection)) {
            $this->messageManager->addComplexErrorMessage(
                'addReleaseNotificationErrorMessage',
                [
                    'product_skus' => array_map(function ($product) {
                        return $product->getSku();
                    }, $products)
                ]
            );
        } else {
            $this->messageManager->addSuccessMessage('Notifications have been sent.');
        }

        return $redirect;
    }
}
