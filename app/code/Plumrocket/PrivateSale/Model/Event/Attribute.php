<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\Event;

use Magento\Catalog\Helper\Data;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Catalog\Model\Attribute\LockValidatorInterface;
use Plumrocket\PrivateSale\Model\Event;
use Magento\Catalog\Model\Category\Attribute as CategoryAttribute;

class Attribute extends \Magento\Eav\Model\Entity\Attribute
{
    const GLOBAL_PRICE_SCOPE = '1';

    const MODULE_NAME = 'Plumrocket_PrivateSale';

    /**
     * @var string
     */
    protected $_eventPrefix = Event::ENTITY . '_eav_attribute';

    /**
     * @var string
     */
    protected $_eventObject = 'attribute';

    /**
     * @var LockValidatorInterface
     */
    protected $attrLockValidator;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $attributesWithDynamicScope = ['enable', 'event_from', 'event_to'];

    /**
     * Attribute constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param LockValidatorInterface $attrLockValidator
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        LockValidatorInterface $attrLockValidator,
        DateTimeFormatterInterface $dateTimeFormatter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->attrLockValidator = $attrLockValidator;
        $this->scopeConfig = $scopeConfig;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $eavConfig,
            $eavTypeFactory,
            $storeManager,
            $resourceHelper,
            $universalFactory,
            $optionDataFactory,
            $dataObjectProcessor,
            $dataObjectHelper,
            $localeDate,
            $reservedAttributeList,
            $localeResolver,
            $dateTimeFormatter,
            null,
            null,
            $data
        );
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        try {
            $this->attrLockValidator->validate($this);
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            throw new \Magento\Framework\Exception\LocalizedException(__($exception->getMessage()));
        }

        $this->setData('modulePrefix', self::MODULE_NAME);
        return parent::beforeSave();
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave()
    {
        $this->_eavConfig->clear();
        return parent::afterSave();
    }

    /**
     * @return string
     */
    public function getScope()
    {
        $scope = (int)$this->_getData(CategoryAttribute::KEY_IS_GLOBAL);
        $attributeCode = $this->_getData('attribute_code');

        if ($scope === CategoryAttribute::SCOPE_GLOBAL
        || (in_array($attributeCode, $this->attributesWithDynamicScope)
            && $this->scopeConfig->getValue(Data::XML_PATH_PRICE_SCOPE) === self::GLOBAL_PRICE_SCOPE)
        ) {
           return CategoryAttribute::SCOPE_GLOBAL_TEXT;
        }

        if ($scope === CategoryAttribute::SCOPE_WEBSITE) {
           return CategoryAttribute::SCOPE_WEBSITE_TEXT;
        }

        return CategoryAttribute::SCOPE_STORE_TEXT;
    }

    /**
     * @param $scope
     * @return $this|Attribute
     */
    public function setScope($scope)
    {
        if ($scope === CategoryAttribute::SCOPE_GLOBAL_TEXT) {
            return $this->setData(CategoryAttribute::KEY_IS_GLOBAL, CategoryAttribute::SCOPE_GLOBAL);
        }

        if ($scope === CategoryAttribute::SCOPE_WEBSITE_TEXT) {
            return $this->setData(CategoryAttribute::KEY_IS_GLOBAL, CategoryAttribute::SCOPE_WEBSITE);
        }

        if ($scope === CategoryAttribute::SCOPE_STORE_TEXT) {
            return $this->setData(CategoryAttribute::KEY_IS_GLOBAL, CategoryAttribute::SCOPE_STORE);
        }

        //Ignore unrecognized scope
        return $this;
    }

    /**
     * @return int
     */
    public function getIsGlobal()
    {
        return (int) $this->_getData(CategoryAttribute::KEY_IS_GLOBAL);
    }

    /**
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getScope() === CategoryAttribute::SCOPE_GLOBAL;
    }

    /**
     * @return bool
     */
    public function isScopeWebsite()
    {
        return $this->getScope() === CategoryAttribute::SCOPE_WEBSITE;
    }

    /**
     * @return bool
     */
    public function isScopeStore()
    {
        return !$this->isScopeGlobal() && !$this->isScopeWebsite();
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $sortWeight = isset($this->_data['attribute_set_info'], current($this->_data['attribute_set_info'])['sort'])
            ? current($this->_data['attribute_set_info'])['sort']
            : 0;
    }
}
