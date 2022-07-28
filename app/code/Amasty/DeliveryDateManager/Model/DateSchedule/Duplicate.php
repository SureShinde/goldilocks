<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DateSchedule;

use Amasty\DeliveryDateManager\Model\ModalDuplicateResolver\ResolverInterface;

class Duplicate implements ResolverInterface
{
    /**
     * @var Get
     */
    private $scheduleGetter;

    /**
     * @var Save
     */
    private $scheduleSaver;

    /**
     * @var DateScheduleDataFactory
     */
    private $scheduleFactory;

    public function __construct(
        Get $scheduleGetter,
        Save $scheduleSaver,
        DateScheduleDataFactory $scheduleFactory
    ) {
        $this->scheduleGetter = $scheduleGetter;
        $this->scheduleSaver = $scheduleSaver;
        $this->scheduleFactory = $scheduleFactory;
    }

    /**
     * @param int $scheduleId
     * @return int
     */
    public function execute(int $scheduleId): int
    {
        /** @var DateScheduleData $mainSchedule */
        $mainSchedule = $this->scheduleGetter->execute($scheduleId);

        /** @var DateScheduleData $newSchedule */
        $newSchedule = $this->scheduleFactory->create();
        $newSchedule->setData($mainSchedule->getData());
        $newSchedule->setScheduleId(null);
        $newSchedule->setName('Copy of ' . $mainSchedule->getName());
        $newSchedule = $this->scheduleSaver->execute($newSchedule);

        return $newSchedule->getScheduleId();
    }
}
