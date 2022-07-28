<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model;

use Magento\Framework\Data\CollectionDataSourceInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract implements CollectionDataSourceInterface
{
    /**
     * @var string
     */
    protected $pathPrefix = 'ampreorder/';

    public const PREORDER_ENABLED = 'functional/enabled';
    public const DISABLE_POSITIVE_QTY = 'functional/disableforpositiveqty';
    public const ALLOW_EMPTY_QTY = 'functional/allowemptyqty';

    public const ADD_CART_BUTTON_TEXT = 'general/addtocartbuttontext';
    public const SHOW_PREORDER_NOTE = 'general/show_preorder_note';
    public const CART_MESSAGE = 'general/cart_message';
    public const BELOW_ZERO_MESSAGE = 'general/below_zero_message';
    public const NOTE_POSITION = 'general/note_position';
    public const ORDER_PREORDER_WARNING = 'general/orderpreorderwarning';
    public const DEFAULT_PREORDER_NOTE = 'general/defaultpreordernote';

    public const ADD_WARNING_TO_EMAIL = 'additional/addwarningtoemail';
    public const DISCOVER_COMPOSITE_OPTIONS = 'additional/discovercompositeoptions';

    public function isEnabled(): bool
    {
        return $this->isSetFlag(self::PREORDER_ENABLED);
    }

    public function getDefaultPreorderCartLabel(): string
    {
        return (string) $this->getValue(self::ADD_CART_BUTTON_TEXT);
    }

    public function isPreorderEnabled(): bool
    {
        return (bool) $this->getValue(self::PREORDER_ENABLED);
    }

    public function isDisableForPositiveQty(?int $websiteId = null): bool
    {
        return $this->isSetFlag(self::DISABLE_POSITIVE_QTY, $websiteId, ScopeInterface::SCOPE_WEBSITES);
    }

    public function isAllowEmpty(?int $websiteId = null): bool
    {
        return $this->isSetFlag(self::ALLOW_EMPTY_QTY, $websiteId, ScopeInterface::SCOPE_WEBSITES);
    }

    public function isPreOrderNoteShow(): bool
    {
        return $this->isSetFlag(self::SHOW_PREORDER_NOTE);
    }

    public function isWarningInEmail(): bool
    {
        return $this->isSetFlag(self::ADD_WARNING_TO_EMAIL);
    }

    public function getCartMessage(): string
    {
        return (string) $this->getValue(self::CART_MESSAGE);
    }

    public function getBelowZeroMessage(): string
    {
        return (string) $this->getValue(self::BELOW_ZERO_MESSAGE);
    }

    public function getPreorderNotePosition(): string
    {
        return (string) $this->getValue(self::NOTE_POSITION);
    }

    public function getOrderPreorderWarning(): string
    {
        return (string) $this->getValue(self::ORDER_PREORDER_WARNING);
    }

    public function getDefaultPreorderNote(): string
    {
        return (string) $this->getValue(self::DEFAULT_PREORDER_NOTE);
    }

    public function isDiscoverCompositeOptions(?int $websiteId = null): bool
    {
        return $this->isSetFlag(self::DISCOVER_COMPOSITE_OPTIONS, $websiteId, ScopeInterface::SCOPE_WEBSITES);
    }
}
