<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model;

use Magento\Framework\App\ScopeInterface;

class EditableConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    public const IS_EDITABLE = 'is_editable';
    public const RULE_ACTIVATION = 'rule_activation';
    public const PERIOD = 'period';
    public const ORDER_STATUS = 'order_status';
    public const EMAIL = 'admin_email';
    public const ADMIN_IDENTITY = 'admin_identity';
    public const IDENTITY = 'identity';
    public const ADMIN_EMAIL_TEMPLATE = 'admin_email_template';
    public const EMAIL_TEMPLATE = 'email_template';

    /**
     * xpath prefix of module (section)
     *
     * @var string
     */
    protected $pathPrefix = 'amdeliverydate/date_field/';

    /**
     * @param int|ScopeInterface|null $store
     * @return bool
     */
    public function isEditable($store = null): bool
    {
        return $this->isSetFlag(self::IS_EDITABLE, $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     * @return string
     */
    public function getRuleActivation($store = null): string
    {
        return $this->getValue(self::RULE_ACTIVATION, $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     * @return int
     */
    public function getPeriod($store = null): int
    {
        return (int)$this->getValue(self::PERIOD, $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     * @return string[] array of order status codes
     */
    public function getOrderStatuses($store = null): array
    {
        $orderStatuses = (string)$this->getValue(self::ORDER_STATUS, $store);

        return $this->parseArray($orderStatuses);
    }

    /**
     *
     * @param int|ScopeInterface|null $store
     * @return string[] array of emails
     */
    public function getAdminEmail($store = null): array
    {
        $emails = (string)$this->getValue(self::EMAIL, $store);

        return $this->parseArray($emails);
    }

    /**
     * @param int|ScopeInterface|null $store
     * @return string
     */
    public function getIdentity($store = null): string
    {
        return (string)$this->getValue(self::IDENTITY, $store);
    }

    /**
     * @param int|ScopeInterface|null $store
     * @return string
     */
    public function getEmailTemplate($store = null): string
    {
        return (string)$this->getValue(self::EMAIL_TEMPLATE, $store);
    }

    /**
     * @param string $fromString
     * @return array
     */
    private function parseArray(string $fromString): array
    {
        $values = explode(',', $fromString);
        foreach ($values as $key => $value) {
            $value = trim($value);
            if (empty($value)) {
                unset($values[$key]);
                continue;
            }
        }

        return $values;
    }
}
