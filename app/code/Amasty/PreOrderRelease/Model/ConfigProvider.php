<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model;

use Amasty\PreOrderRelease\Model\Source\ReleaseDateFormat;

class ConfigProvider extends \Amasty\Preorder\Model\ConfigProvider
{
    public const RELEASE_ORDER_STATUSES = 'release_notification/order_status';
    public const RELEASE_EMAIL_TEMPLATE = 'release_notification/email_template';
    public const RELEASE_EMAIL_SENDER = 'release_notification/email_sender';
    public const RELEASE_DATE_ENABLED = 'release_date/enabled';
    public const RELEASE_DATE_ATTRIBUTE = 'release_date/attribute';
    public const RELEASE_DATE_FORMAT = 'release_date/format';
    public const RELEASE_CHANGE_BACKORDERS = 'release_date/change_backorders';
    public const RELEASE_NOTE_STATE = 'release_date/note_state';

    public function getReleaseEmailTemplate(int $storeId): string
    {
        return (string) $this->getValue(self::RELEASE_EMAIL_TEMPLATE, $storeId);
    }

    public function getReleaseEmailSender(int $storeId): string
    {
        return (string) $this->getValue(self::RELEASE_EMAIL_SENDER, $storeId);
    }

    public function getReleaseOrderStatuses(): array
    {
        return explode(',', (string) $this->getValue(self::RELEASE_ORDER_STATUSES));
    }

    public function isReleaseDateEnabled(): bool
    {
        return $this->isSetFlag(self::RELEASE_DATE_ENABLED);
    }

    public function getReleaseDateAttribute(): string
    {
        return (string) $this->getValue(self::RELEASE_DATE_ATTRIBUTE);
    }

    public function getReleaseDateFormat(): ?string
    {
        $releaseDateFormat = (string) $this->getValue(self::RELEASE_DATE_FORMAT);

        return $releaseDateFormat !== ReleaseDateFormat::DEFAULT_FORMAT
            ? $releaseDateFormat
            : null;
    }

    public function getNewBackordersValue(): int
    {
        return (int) $this->getValue(self::RELEASE_CHANGE_BACKORDERS);
    }

    public function getReleaseNoteState(): int
    {
        return (int) $this->getValue(self::RELEASE_NOTE_STATE);
    }
}
