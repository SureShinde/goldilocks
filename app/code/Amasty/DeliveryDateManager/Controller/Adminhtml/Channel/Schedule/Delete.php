<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Controller\Adminhtml\Channel\Schedule;

use Amasty\DeliveryDateManager\Model\DateSchedule\Delete as DateScheduleDeleter;
use Amasty\DeliveryDateManager\Model\DateSchedule\Get;
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
    public const ADMIN_RESOURCE = 'Amasty_DeliveryDateManager::channel_schedules';

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
    private $scheduleGetter;

    /**
     * @var DateScheduleDeleter
     */
    private $scheduleDeleter;

    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        Get $scheduleGetter,
        DateScheduleDeleter $scheduleDeleter
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->scheduleGetter = $scheduleGetter;
        $this->scheduleDeleter = $scheduleDeleter;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $message = '';
        $error = false;

        if ($id = (int)$this->getRequest()->getParam('id')) {
            try {
                $schedule = $this->scheduleGetter->execute($id);
                $this->scheduleDeleter->execute($schedule);
                $message = __('You deleted the date schedule.');
            } catch (\Exception $e) {
                $error = true;
                $message = __('We can\'t delete the date schedule right now.');
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
