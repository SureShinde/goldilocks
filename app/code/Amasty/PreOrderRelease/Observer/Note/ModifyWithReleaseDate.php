<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Observer\Note;

use Amasty\PreOrderRelease\Model\ConfigProvider;
use Amasty\PreOrderRelease\Model\Product\GetReleaseDate;
use Amasty\PreOrderRelease\Model\Source\ChangeBackorders;
use Amasty\PreOrderRelease\Model\Source\NoteState;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ModifyWithReleaseDate implements ObserverInterface
{
    public const RELEASE_DATE_ATTRIBUTE = '{release_date}';

    /**
     * @var GetReleaseDate
     */
    private $getReleaseDate;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        GetReleaseDate $getReleaseDate,
        TimezoneInterface $timezone,
        DateTimeFactory $dateTimeFactory,
        ConfigProvider $configProvider
    ) {
        $this->getReleaseDate = $getReleaseDate;
        $this->timezone = $timezone;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->configProvider = $configProvider;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $transport = $observer->getEvent()->getTransport();
        $note = $transport->getValue();

        if ($note && strpos($note, self::RELEASE_DATE_ATTRIBUTE) !== false) {
            $releaseDate = $this->getReleaseDate->execute($transport->getProduct());
            if ($this->configProvider->isReleaseDateEnabled() && $releaseDate) {
                $releaseDateTimestamp = $this->dateTimeFactory->create($releaseDate)->getTimestamp();
                $currentDateTimestamp = $this->timezone->scopeTimeStamp();
                if ($releaseDateTimestamp <= $currentDateTimestamp
                    && $this->configProvider->getNewBackordersValue() === ChangeBackorders::NO
                ) {
                    $note = $this->getNoteForExpiredReleaseDate();
                }
            } else {
                $note = '';
            }
        }

        $transport->setValue($note);
    }

    private function getNoteForExpiredReleaseDate(): string
    {
        if ($this->configProvider->getReleaseNoteState() === NoteState::REPLACED_WITH_DEFAULT) {
            $note = $this->configProvider->getDefaultPreorderNote();
            if (strpos($note, self::RELEASE_DATE_ATTRIBUTE) !== false) {
                $note = '';
            }
        } else {
            $note = '';
        }

        return $note;
    }
}
