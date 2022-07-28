<?php

namespace Amasty\StorePickupWithLocator\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 */
class ConfigProvider extends ConfigProviderAbstract
{
    /**
     * xpath prefix of module (section)
     *
     * @var string
     */
    protected $pathPrefix = 'storepickup_locator/';

    /**
     * xpath group parts
     */
    public const GENERAL_BLOCK = 'general/';
    public const DATE_SETTINGS_BLOCK = 'date_settings/';
    public const TIME_SETTINGS_BLOCK = 'time_settings/';
    public const CURBSIDE_PICKUP_SETTINGS_BLOCK = 'curbside_pickup_settings/';

    /**
     * xpath field parts
     */
    public const FIELD_ENABLED = 'enabled';
    public const FIELD_CHECK_PRODUCT_AVAILABILITY = 'check_product_availability';
    public const FIELD_TEMPLATE = 'shipping_template';
    public const ALLOW_SAME_DAY = 'allow_same_day';
    public const SAME_DAY_CUTOFF_TIME = 'same_day_cutoff_time';
    public const MIN_TIME_ORDER = 'min_time_order';
    public const MIN_TIME_BACKORDER = 'min_time_backorder';
    public const SHIPPING_INFO_AREA = 'display_shipping_info';
    public const CHECKBOX_ENABLED = 'checkbox_enabled';
    public const CHECKBOX_LABEL = 'checkbox_label';
    public const DISPLAY_CURBSIDE_CONDITIONS = 'display_curbside_conditions';
    public const CONDITIONS_LABEL = 'conditions_label';
    public const COMMENTS_ENABLED = 'comments_enabled';
    public const COMMENT_PLACEHOLDER = 'comment_placeholder';
    public const COMMENT_FIELD_REQUIRED = 'comment_field_required';
    public const LABELS_ENABLED = 'labels_enabled';
    public const LABEL_TEXT = 'label_text';
    public const PICKUP_BANNER = 'pickup_banner';

    /**
     * xpath full parts
     */
    public const MAIN_EXTENSION_PATH = 'carriers/amstorepickup/active';
    public const CARRIER_TITLE_PATH = 'carriers/amstorepickup/title';
    public const SECTION_LIFETIME = 'customer/online_customers/section_data_lifetime';

    /**
     * @var TimeHandler
     */
    private $timeHandler;

    public function __construct(ScopeConfigInterface $scopeConfig, TimeHandler $timeHandler)
    {
        parent::__construct($scopeConfig);
        $this->timeHandler = $timeHandler;
    }

    /**
     * @return bool
     */
    public function isStorePickupEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::MAIN_EXTENSION_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isCheckProductAvailability(): bool
    {
        return $this->isSetFlag(self::GENERAL_BLOCK . self::FIELD_CHECK_PRODUCT_AVAILABILITY);
    }

    /**
     * @return string
     */
    public function getStoreTemplate(): string
    {
        return (string)$this->getValue(self::GENERAL_BLOCK . self::FIELD_TEMPLATE);
    }

    /**
     * @return bool
     */
    public function isPickupDateEnabled(): bool
    {
        return $this->isSetFlag(self::DATE_SETTINGS_BLOCK . self::FIELD_ENABLED);
    }

    /**
     * @return bool
     */
    public function isPickupTimeEnabled(): bool
    {
        return $this->isSetFlag(self::TIME_SETTINGS_BLOCK . self::FIELD_ENABLED);
    }

    /**
     * @return bool
     */
    public function isSameDayAllowed(): bool
    {
        return $this->isSetFlag(self::DATE_SETTINGS_BLOCK . self::ALLOW_SAME_DAY);
    }

    /**
     * @return int
     */
    public function getSameDayCutOff(): int
    {
        return strtotime(
            $this->timeHandler->getDate() . ' ' .
            $this->getValue(self::TIME_SETTINGS_BLOCK . self::SAME_DAY_CUTOFF_TIME)
        );
    }

    /**
     * @return float
     */
    public function getMinTimeOrder(): float
    {
        return (float)$this->getValue(self::TIME_SETTINGS_BLOCK . self::MIN_TIME_ORDER);
    }

    /**
     * @return float
     */
    public function getMinTimeBackorder(): float
    {
        return (float)$this->getValue(self::TIME_SETTINGS_BLOCK . self::MIN_TIME_BACKORDER);
    }

    /**
     * @return int
     */
    public function getExpirableSectionLifetime(): int
    {
        return (int)$this->scopeConfig->getValue(self::SECTION_LIFETIME);
    }

    /**
     * @return int
     */
    public function areaForShippingInfo()
    {
        return (int)$this->getValue(self::GENERAL_BLOCK . self::SHIPPING_INFO_AREA);
    }

    /**
     * @return string
     */
    public function getCarrierTitle()
    {
        return (string)$this->scopeConfig->getValue(self::CARRIER_TITLE_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isCurbsideCheckboxEnabled($storeId = null): bool
    {
        return $this->isSetFlag(
            self::CURBSIDE_PICKUP_SETTINGS_BLOCK . self::CHECKBOX_ENABLED,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCurbsideCheckboxLabel($storeId = null): string
    {
        return (string)$this->getValue(
            self::CURBSIDE_PICKUP_SETTINGS_BLOCK . self::CHECKBOX_LABEL,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isDisplayCurbsideConditions($storeId = null): bool
    {
        return $this->isSetFlag(
            self::CURBSIDE_PICKUP_SETTINGS_BLOCK . self::DISPLAY_CURBSIDE_CONDITIONS,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCurbsideConditionsLabel($storeId = null): string
    {
        return (string)$this->getValue(
            self::CURBSIDE_PICKUP_SETTINGS_BLOCK . self::CONDITIONS_LABEL,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isCurbsideCommentsEnabled($storeId = null): bool
    {
        return $this->isSetFlag(
            self::CURBSIDE_PICKUP_SETTINGS_BLOCK . self::COMMENTS_ENABLED,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCurbsideCommentPlaceholder($storeId = null): string
    {
        return (string)$this->getValue(
            self::CURBSIDE_PICKUP_SETTINGS_BLOCK . self::COMMENT_PLACEHOLDER,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isCurbsideCommentRequired($storeId = null): bool
    {
        return $this->isSetFlag(
            self::CURBSIDE_PICKUP_SETTINGS_BLOCK . self::COMMENT_FIELD_REQUIRED,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isCurbsideLabelsEnabled($storeId = null): bool
    {
        return $this->isSetFlag(
            self::CURBSIDE_PICKUP_SETTINGS_BLOCK . self::LABELS_ENABLED,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCurbsideLabelText($storeId = null): string
    {
        return (string)$this->getValue(
            self::CURBSIDE_PICKUP_SETTINGS_BLOCK . self::LABEL_TEXT,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCurbsidePickupBannerCode($storeId = null): string
    {
        return (string)$this->getValue(
            self::CURBSIDE_PICKUP_SETTINGS_BLOCK . self::PICKUP_BANNER,
            $storeId
        );
    }
}
