<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model;

use Amasty\DeliveryDateManager\Model\DeliveryChannelScope\ScopeRegistry;
use Amasty\DeliveryDateManager\Model\DeliveryDate\DateFormatProvider;
use Amasty\DeliveryDateManager\Model\Validator\IsBackOrderInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provide array settings for checkout page
 */
class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * In what format the calendar will send date
     * valuest from calendar to server should be in this format
     */
    public const OUTPUT_DATE_FORMAT = 'yyyy-MM-dd';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var DeliveryChannelScope\ScopeRegistry
     */
    private $scopeRegistry;

    /**
     * @var DateFormatProvider
     */
    private $dateFormatProvider;

    /**
     * @var IsBackOrderInterface
     */
    private $isBackOrder;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ConfigProvider $configProvider,
        ScopeRegistry $scopeRegistry,
        DateFormatProvider $dateFormatProvider,
        IsBackOrderInterface $isBackOrder,
        DateTime $dateTime,
        StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->scopeRegistry = $scopeRegistry;
        $this->dateFormatProvider = $dateFormatProvider;
        $this->isBackOrder = $isBackOrder;
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        // get channel set with default scope values (now it only store id)
        $this->scopeRegistry->reset();

        return [
            'amasty' => [
                'deliverydate' => [
                    'moduleEnabled' => (int)$this->configProvider->isEnabled(),
                    'deliveryRulesBlock' => $this->configProvider->getDeliveryRulesBlock(),
                    'isPreselectDay' => $this->configProvider->isEnabledDefaultDate(),
                    'isPreselectTime' => $this->configProvider->isEnabledDefaultTime(),
                    'isBackorder' => $this->isBackOrder->execute(),
                    'gmtOffset' => $this->dateTime->getGmtOffset('hours'),
                    'isOnlyWorkdays' => $this->configProvider->isOnlyWorkdays(),
                    'firstDay' => $this->configProvider->getFirstDayOfWeek($this->storeManager->getStore())
                ]
            ]
        ];
    }

    /**
     * In what format the calendar will show on front.
     * in calendar.js always long year format on frontend.
     *
     * @return string
     */
    public function getPickerDateFormat(): string
    {
        $format = $this->dateFormatProvider->getDateFormat();

        // convert short year to long format. For calendar.js
        return preg_replace('/y{2,}/s', 'yyyy', $format);
    }

    /**
     * Calendar input format
     * Values from server to calendar should be in this format
     *
     * @return string
     */
    public function getInputDateFormat(): string
    {
        return self::OUTPUT_DATE_FORMAT;
    }

    /**
     * Calendar input format
     * Values to server from calendar should be in this format
     *
     * @return string
     */
    public function getOutputDateFormat(): string
    {
        return self::OUTPUT_DATE_FORMAT;
    }
}
