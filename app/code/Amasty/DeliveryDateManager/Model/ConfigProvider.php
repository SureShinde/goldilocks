<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    /**
     * xpath prefix of module (section)
     *
     * @var string
     */
    protected $pathPrefix = 'amdeliverydate/';

    /**
     * xpath group parts
     */
    public const GENERAL_BLOCK = 'general/';
    public const DATE_BLOCK = 'date_field/';
    public const TIME_BLOCK = 'time_field/';
    public const COMMENT_BLOCK = 'comment_field/';
    public const EMAIL_REMINDER_BLOCK = 'reminder/';
    public const DISPLAY_ON = 'show';
    public const INCLUDE = 'include';

    public const FIRSTDAY_PATH = 'general/locale/firstday';

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    public function __construct(ScopeConfigInterface $scopeConfig, BlockRepositoryInterface $blockRepository)
    {
        parent::__construct($scopeConfig);
        $this->blockRepository = $blockRepository;
    }

    /**
     * @param int|ScopeInterface|null $store
     *
     * @return bool
     */
    public function isEnabled($store = null): bool
    {
        return $this->isSetFlag(static::GENERAL_BLOCK . 'enabled', $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     *
     * @return bool
     */
    public function isOnlyWorkdays($store = null): bool
    {
        return $this->isSetFlag(static::DATE_BLOCK . 'only_workdays', $store);
    }

    /**
     * @return string|null
     */
    public function getDeliveryRulesBlock(): ?string
    {
        if ($cmsBlockId = (string)$this->getDeliveryRulesBlockId()) {
            $cmsBlock = $this->blockRepository->getById($cmsBlockId);

            return $cmsBlock->getContent();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getDeliveryRulesBlockId(): ?string
    {
        return $this->getValue(static::GENERAL_BLOCK . 'delivery_rules_block');
    }

    /** CALENDAR/DATE FIELD CONFIGURATIONS */

    /**
     * @param int|ScopeInterface|null $store
     *
     * @return bool
     */
    public function isDateRequired($store = null): bool
    {
        return $this->isSetFlag(static::DATE_BLOCK . 'required', $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     *
     * @return bool
     */
    public function isEnabledDefaultDate($store = null): bool
    {
        return $this->isSetFlag(static::DATE_BLOCK . 'enabled_default', $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     *
     * @return string|null
     */
    public function getDatePlaceholderText($store = null): ?string
    {
        return $this->getValue(static::DATE_BLOCK . 'date_placeholder_text', $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     *
     * @return string|null
     */
    public function getDateNote($store = null): ?string
    {
        return $this->getValue(static::DATE_BLOCK . 'note', $store);
    }

    /** TIME CONFIGURATIONS */

    /**
     * @param null $store
     *
     * @return bool
     */
    public function isTimeEnabled($store = null): bool
    {
        return $this->isSetFlag(static::TIME_BLOCK . 'enabled_time', $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     *
     * @return bool
     */
    public function isTimeRequired($store = null): bool
    {
        return $this->isSetFlag(static::TIME_BLOCK . 'required', $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     *
     * @return bool
     */
    public function isEnabledDefaultTime($store = null): bool
    {
        return $this->isSetFlag(static::TIME_BLOCK . 'enabled_default', $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     *
     * @return string|null
     */
    public function getTimePlaceholderText($store = null): ?string
    {
        return $this->getValue(static::TIME_BLOCK . 'time_placeholder_text', $store);
    }

    /**
     * @return string|null
     */
    public function getTimeNote(): ?string
    {
        return $this->getValue(static::TIME_BLOCK . 'note');
    }

    /** COMMENT CONFIGURATIONS */

    /**
     * @param null $store
     *
     * @return bool
     */
    public function isCommentEnabled($store = null): bool
    {
        return $this->isSetFlag(static::COMMENT_BLOCK . 'enabled_comment', $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     *
     * @return bool
     */
    public function isCommentRequired($store = null): bool
    {
        return $this->isSetFlag(static::COMMENT_BLOCK . 'required', $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     *
     * @return string|null
     */
    public function getCommentNote($store = null): ?string
    {
        return $this->getValue(static::COMMENT_BLOCK . 'note', $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     *
     * @return int
     */
    public function getCommentMaxLength($store = null): int
    {
        return (int)$this->getValue(self::COMMENT_BLOCK . 'maxlength', $store);
    }

    /** EMAIL/REMINDER CONFIGURATIONS */

    /**
     * @return bool
     */
    public function isReminderEnabled(): bool
    {
        return $this->isSetGlobalFlag(self::EMAIL_REMINDER_BLOCK . 'enabled_reminder');
    }

    /**
     * @param int $store
     *
     * @return string|null
     */
    public function getReminderRecipient($store = null): ?string
    {
        return $this->getValue(self::EMAIL_REMINDER_BLOCK . 'recipient_email', $store);
    }

    /**
     * @param int $store
     *
     * @return string|null
     */
    public function getReminderSender($store = null): ?string
    {
        return $this->getValue(self::EMAIL_REMINDER_BLOCK . 'reminder_sender', $store);
    }

    /**
     * @param int $store
     *
     * @return string|null|int
     */
    public function getReminderTemplate($store = null)
    {
        return $this->getValue(self::EMAIL_REMINDER_BLOCK . 'email_template', $store);
    }

    /**
     * @param int $store
     *
     * @return int
     */
    public function getReminderTimeBefore($store = null): int
    {
        return (int)$this->getValue(self::EMAIL_REMINDER_BLOCK . 'time_before', $store);
    }

    /**
     * @param int|null $store
     *
     * @return array
     */
    public function getDateDisplayOn(int $store = null): array
    {
        return array_merge(
            explode(',', $this->getValue(self::DATE_BLOCK . self::DISPLAY_ON, $store)),
            explode(',', $this->getValue(self::DATE_BLOCK . self::INCLUDE, $store))
        );
    }

    /**
     * @param int|null $store
     *
     * @return array
     */
    public function getTimeDisplayOn(int $store = null): array
    {
        return array_merge(
            explode(',', $this->getValue(self::TIME_BLOCK . self::DISPLAY_ON, $store)),
            explode(',', $this->getValue(self::TIME_BLOCK . self::INCLUDE, $store))
        );
    }

    /**
     * @param int|null $store
     *
     * @return array
     */
    public function getCommentDisplayOn(int $store = null): array
    {
        return array_merge(
            explode(',', $this->getValue(self::COMMENT_BLOCK . self::DISPLAY_ON, $store)),
            explode(',', $this->getValue(self::COMMENT_BLOCK . self::INCLUDE, $store))
        );
    }

    /**
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $store
     * @return int
     */
    public function getFirstDayOfWeek($store = null): int
    {
        return (int)$this->scopeConfig->getValue(self::FIRSTDAY_PATH, StoreScopeInterface::SCOPE_STORE, $store);
    }
}
