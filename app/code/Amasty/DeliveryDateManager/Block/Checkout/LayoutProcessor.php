<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Checkout;

use Amasty\DeliveryDateManager\Block\Component\CalendarComponent;
use Amasty\DeliveryDateManager\Block\Component\CommentComponent;
use Amasty\DeliveryDateManager\Block\Component\ComponentInterface;
use Amasty\DeliveryDateManager\Block\Component\TimeSelectComponent;
use Amasty\DeliveryDateManager\Model\CheckoutConfigProvider;
use Amasty\DeliveryDateManager\Model\ConfigProvider;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\DeliveryOrderData;
use Amasty\DeliveryDateManager\Model\DeliveryQuote\Get;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Stdlib\ArrayManager;

class LayoutProcessor implements LayoutProcessorInterface
{
    public const DATA_SCOPE_NS = 'amdeliverydate';

    public const STORAGE_SECTION_NAME = 'amasty-deliverydate';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var DeliveryOrderData
     */
    protected $deliveryDate;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var Get
     */
    private $getDeliveryQuote;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ComponentInterface[]
     */
    private $components;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        Session $checkoutSession,
        DeliveryOrderData $deliveryDate,
        ArrayManager $arrayManager,
        Get $getDeliveryQuote,
        ConfigProvider $configProvider,
        array $components = []
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->deliveryDate = $deliveryDate;
        $this->arrayManager = $arrayManager;
        $this->getDeliveryQuote = $getDeliveryQuote;
        $this->configProvider = $configProvider;
        $this->components = $components;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    public function process($jsLayout)
    {
        $storeId = (int)$this->checkoutSession->getQuote()->getStoreId();

        if (!$this->configProvider->isEnabled($storeId)) {
            return $this->arrayManager->remove(
                'components/checkout/children/steps/children/shipping-step/children/' .
                'shippingAddress/children/shippingAdditional/children/amasty-delivery-date',
                $jsLayout
            );
        }

        $deliveryDatePath = 'components/checkout/children/steps/children/shipping-step/children/' .
            'shippingAddress/children/shippingAdditional/children/amasty-delivery-date/children';
        $deliveryDateChildren = $this->arrayManager->get(
            $deliveryDatePath,
            $jsLayout,
            []
        );

        $elements = [];
        foreach ($this->components as $component) {
            if (!$component->isEnabled($storeId)) {
                unset($deliveryDateChildren[$component->getName()]);
                continue;
            }

            $elements[$component->getName()] = $component->getComponent($storeId);
        }

        if (!empty($elements)) {
            $this->setValues($elements);

            foreach ($elements as $key => $element) {
                if (!isset($deliveryDateChildren[$key])) {
                    $deliveryDateChildren[$key] = [];
                }

                $deliveryDateChildren[$key] += $element;
            }

            $jsLayout = $this->arrayManager->set(
                $deliveryDatePath,
                $jsLayout,
                $deliveryDateChildren
            );
        }

        return $jsLayout;
    }

    /**
     * Load and set elements value
     *
     * @param array $elements
     */
    private function setValues(array &$elements): void
    {
        $shippingAddressId = $this->checkoutSession->getQuote()->getShippingAddress()->getId();
        if (!$shippingAddressId) {
            return;
        }
        $deliveryDateQuote = $this->getDeliveryQuote->getByAddressId((int)$shippingAddressId);
        if (!$deliveryDateQuote->getDeliveryQuoteId()) {
            return;
        }
        if (isset($elements[CalendarComponent::NAME]) && $deliveryDateQuote->getDate()) {
            $date = new \Zend_Date($deliveryDateQuote->getDate(), \Zend_Date::ISO_8601);

            $elements[CalendarComponent::NAME]['config']['value'] = $date
                ->toString(CheckoutConfigProvider::OUTPUT_DATE_FORMAT);
            $elements[CalendarComponent::NAME]['config']['shiftedValue'] = $date
                ->toString($elements[CalendarComponent::NAME]['config']['pickerDefaultDateFormat']);
        }
        if (isset($elements[TimeSelectComponent::NAME]) && $deliveryDateQuote->getTimeIntervalId()) {
            $elements[TimeSelectComponent::NAME]['value'] = $deliveryDateQuote->getTimeIntervalId();
        }
        if (isset($elements[CommentComponent::NAME]) && $deliveryDateQuote->getComment()) {
            $elements[CommentComponent::NAME]['value'] = $deliveryDateQuote->getComment();
        }
    }
}
