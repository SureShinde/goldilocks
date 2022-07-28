<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Adminhtml\Sales\Order\Create;

use Amasty\DeliveryDateManager\Block\Adminhtml\Sales\Order\Renderer\Date;
use Amasty\DeliveryDateManager\Block\Adminhtml\Sales\Order\Renderer\Time;
use Amasty\DeliveryDateManager\Model\Config\Source\Show;
use Amasty\DeliveryDateManager\Model\ConfigDisplay;
use Amasty\DeliveryDateManager\Model\ConfigProvider;
use Amasty\DeliveryDateManager\Model\DeliveryDate\DateFormatProvider;
use Magento\Framework\Data\Form\Element\Collection;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Prepare Delivery Fields for Place Order
 * Output via Plugin
 * @see \Amasty\DeliveryDateManager\Plugin\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form\AddDeliveryFieldsBlock
 */
class Deliverydate extends Template
{
    /**
     * @var string
     */
    public $_template = 'Amasty_DeliveryDateManager::delivery_create.phtml';

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var DateFormatProvider
     */
    private $dateFormatProvider;

    /**
     * @var ConfigDisplay
     */
    private $configDisplay;

    /**
     * @var Fieldset
     */
    private $fieldset;

    public function __construct(
        Context $context,
        FormFactory $formFactory,
        DateTime $date,
        ConfigProvider $configProvider,
        DateFormatProvider $dateFormatProvider,
        ConfigDisplay $configDisplay,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->date = $date;
        $this->configProvider = $configProvider;
        $this->dateFormatProvider = $dateFormatProvider;
        $this->configDisplay = $configDisplay;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabledModule(): bool
    {
        return $this->configProvider->isEnabled();
    }

    /**
     * @return Collection
     */
    public function getFormElements()
    {
        $form = $this->formFactory->create();
        $form->setHtmlIdPrefix('amasty_deliverydate_');
        $this->fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Delivery Date'),
                'class' => 'amasty-deliverydate-fieldset'
            ]
        );

        if ($this->configDisplay->isDateDisplayOn(Show::ORDER_CREATE)) {
            $this->addDeliveryDateField();
        }

        if ($this->configProvider->isTimeEnabled()
            && $this->configDisplay->isTimeDisplayOn(Show::ORDER_CREATE)
        ) {
            $this->addDeliveryTimeField('from');
            $this->addDeliveryTimeField('to');
        }

        if ($this->configProvider->isCommentEnabled()
            && $this->configDisplay->isCommentDisplayOn(Show::ORDER_CREATE)) {
            $this->addDeliveryCommentField();
        }

        return $form->getElements();
    }

    private function addDeliveryDateField(): void
    {
        $this->fieldset->addField(
            'date',
            Date::class,
            [
                'label' => __('Delivery Date'),
                'name' => 'amdeliverydate[date]',
                'style' => 'width: 40%',
                'required' => false
            ]
        );
    }

    private function addDeliveryTimeField(string $period): void
    {
        $this->fieldset->addField(
            'time_' . $period,
            Time::class,
            [
                'label' => __('Delivery Time ' . ucwords($period)),
                'name' => 'amdeliverydate[time_' . $period . ']',
                'style' => 'width: 40%',
                'required' => false
            ]
        );
    }

    private function addDeliveryCommentField(): void
    {
        $this->fieldset->addField(
            'comment',
            'textarea',
            [
                'label' => __('Delivery Comments'),
                'title' => __('Delivery Comments'),
                'name' => 'amdeliverydate[comment]',
                'required' => false,
                'style' => 'width: 40%'
            ]
        );
    }
}
