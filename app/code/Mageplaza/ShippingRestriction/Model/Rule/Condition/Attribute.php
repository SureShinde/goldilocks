<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ShippingRestriction
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingRestriction\Model\Rule\Condition;

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime as AbstractDateTime;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\ShippingRestriction\Helper\Data;

/**
 * Class Attribute
 * @package Mageplaza\ShippingRestriction\Model\Rule\Condition
 */
class Attribute extends AbstractCondition
{
    /**
     * @var array
     */
    private $quoteAttributes = [
        'remote_ip',
    ];

    /**
     * @var array
     */
    private $customerAttributes = [
        'email',
        'firstname',
        'lastname',
        'middlename',
        'dob',
        'taxvat',
        'gender',
        'prefix',
        'suffix',
        'website_id',
        'created_in',
        'created_at',
        'updated_at'
    ];

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Attribute constructor.
     *
     * @param Context $context
     * @param EavConfig $eavConfig
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        Data $helperData,
        array $data = []
    ) {
        $this->eavConfig       = $eavConfig;
        $this->storeManager    = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->helperData      = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'website_id'      => __('Associate To Website'),
            'created_in'      => __('Created In'),
            'customer_id'     => __('Customer ID'),
            'remote_ip'       => __('Customer IP'),
            'dob'             => __('Date Of Birth'),
            'email'           => __('Email'),
            'firstname'       => __('First Name'),
            'gender'          => __('Gender'),
            'lastname'        => __('Last Name'),
            'middlename'      => __('Middle Name/Initial'),
            'prefix'          => __('Name Prefix'),
            'suffix'          => __('Name Suffix'),
            'taxvat'          => __('Tax/Vat Number'),
            'created_at'      => __('Created At'),
            'updated_at'      => __('Updated At')
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'customer_id':
            case 'taxvat':
                return 'numeric';
            case 'dob':
            case 'created_at':
            case 'updated_at':
                return 'date';
            case 'website_id':
            case 'created_in':
            case 'gender':
                return 'select';
        }

        return 'string';
    }

    /**
     * Check if attribute value should be explicit
     *
     * @return bool
     */
    public function getExplicitApply()
    {
        return $this->getInputType() === 'date';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if ($this->getInputType() === 'date') {
            return $this->getInputType();
        }

        switch ($this->getAttribute()) {
            case 'website_id':
            case 'created_in':
            case 'gender':
                return 'select';
        }

        return 'text';
    }

    /**
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'website_id':
                    try {
                        $options = [];
                        foreach ($this->storeManager->getWebsites() as $website) {
                            $options[] = [
                                'label' => $website->getName(),
                                'value' => $website->getId()
                            ];
                        }
                    } catch (Exception $e) {
                        $options = [];
                    }
                    break;
                case 'created_in':
                    try {
                        $options = [];
                        foreach ($this->storeManager->getStores() as $store) {
                            $options[] = [
                                'label' => $store->getName(),
                                'value' => $store->getName()
                            ];
                        }
                    } catch (Exception $e) {
                        $options = [];
                    }
                    break;
                case 'gender':
                    try {
                        $options = $this->eavConfig->getAttribute('customer', 'gender')
                            ->getSource()->getAllOptions();
                    } catch (Exception $e) {
                        $options = [];
                    }
                    break;
                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }

        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param AbstractModel $model
     *
     * @return bool
     * @throws Exception
     */
    public function validate(AbstractModel $model)
    {
        foreach ($this->quoteAttributes as $quoteAttribute) {
            $model->setData($quoteAttribute, $model->getQuote()->getData($quoteAttribute));
        }

        $customer = $this->customerFactory->create()->load($model->getCustomerId());

        if ($customer->getId()) {
            foreach ($this->customerAttributes as $customerAttribute) {
                $model->setData($customerAttribute, $customer->getData($customerAttribute));
                if (in_array($customerAttribute, ['created_at', 'updated_at'], true)) {
                    $date = $this->helperData->getConvertedDate($customer->getData($customerAttribute));
                    $model->setData($customerAttribute, $date->format('Y-m-d H:i:s'));
                }

                if ($customerAttribute === 'dob') {
                    $dateTime = (new DateTime($customer->getData($customerAttribute), new DateTimeZone('UTC')))
                        ->setTime(00, 00, 00);
                    $model->setData($customerAttribute, $dateTime->format('Y-m-d H:i:s'));
                }
            }
        }

        return parent::validate($model);
    }

    /**
     * @inheritdoc
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        $default = parent::getDefaultOperatorInputByType();

        $default['date'] = ['==', '!=', '>=', '<='];

        return $default;
    }

    /**
     * @return AbstractElement|AbstractCondition
     * @throws Exception
     */
    public function getValueElement()
    {
        $elementParams = [
            'name'               => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][value]',
            'value'              => $this->getValue(),
            'values'             => $this->getValueSelectOptions(),
            'value_name'         => $this->getValueName(),
            'after_element_html' => $this->getValueAfterElementHtml(),
            'explicit_apply'     => $this->getExplicitApply(),
            'data-form-part'     => $this->getFormName()
        ];
        if ($this->getInputType() === 'date') {
            $elementParams['input_format'] = AbstractDateTime::DATE_INTERNAL_FORMAT;
            $elementParams['date_format']  = AbstractDateTime::DATE_INTERNAL_FORMAT;
            $elementParams['placeholder']  = AbstractDateTime::DATE_INTERNAL_FORMAT;
            $elementParams['autocomplete'] = 'off';
            $elementParams['readonly']     = 'true';
            $elementParams['value_name']   =
                (new DateTime($elementParams['value'], new DateTimeZone($this->_localeDate->getConfigTimezone())))
                    ->format('Y-m-d');

            if ($this->getAttribute() !== 'dob') {
                $elementParams['time_format']  = 'HH:mm:ss';
                $elementParams['value_name']   =
                    (new DateTime($elementParams['value'], new DateTimeZone($this->_localeDate->getConfigTimezone())))
                        ->format('Y-m-d H:i:s');
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__value',
            $this->getValueElementType(),
            $elementParams
        )->setRenderer(
            $this->getValueElementRenderer()
        );
    }
}
