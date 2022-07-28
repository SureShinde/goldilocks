<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\IntervalSet;

use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set as TimeSetResource;
use Amasty\DeliveryDateManager\Model\TimeInterval\Set\Get;
use Amasty\DeliveryDateManager\Model\TimeInterval\Set\ProcessRelatedTimesDelete;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::channel_intervals';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Get
     */
    private $timeSetGetter;

    /**
     * @var TimeSetResource
     */
    private $timeSetResource;

    /**
     * @var ProcessRelatedTimesDelete
     */
    private $processRelatedTimesDeleter;

    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        Get $timeSetGetter,
        TimeSetResource $timeSetResource,
        ProcessRelatedTimesDelete $processRelatedTimesDeleter
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->timeSetGetter = $timeSetGetter;
        $this->timeSetResource = $timeSetResource;
        $this->processRelatedTimesDeleter = $processRelatedTimesDeleter;
    }

    /**
     * Delete time interval action
     *
     * @return Json
     */
    public function execute(): Json
    {
        $message = '';
        $error = false;

        if ($id = (int)$this->getRequest()->getParam('id')) {
            try {
                $timeSet = $this->timeSetGetter->execute($id);
                $this->processRelatedTimesDeleter->processDelete([], $timeSet->getTimeIds());
                $this->timeSetResource->delete($timeSet);
                $message = __('You deleted the time interval set.');
            } catch (\Exception $e) {
                $error = true;
                $message = __('We can\'t delete the time interval set right now.');
                $this->logger->critical($e);
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'error' => $error,
            ]
        );

        return $resultJson;
    }
}
