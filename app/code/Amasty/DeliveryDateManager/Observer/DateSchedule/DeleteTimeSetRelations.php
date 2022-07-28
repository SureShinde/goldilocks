<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Observer\DateSchedule;

use Amasty\DeliveryDateManager\Api\Data\DateScheduleInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\TimeInterval\Set;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class DeleteTimeSetRelations implements ObserverInterface
{
    /**
     * @var Set
     */
    private $setResource;

    public function __construct(
        Set $setResource
    ) {
        $this->setResource = $setResource;
    }

    /**
     * Event name 'amasty_deliverydate_dateschedule_delete_after'
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        $dataObject = $observer->getDataObject();

        if ($dataObject instanceof DateScheduleInterface) {
            $this->setResource->deleteSchedulesRelation([$dataObject->getScheduleId()]);
        }
    }
}
