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
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Ui\DataProvider\Event\Form\Modifier;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Plumrocket\Base\Model\IsModuleInMarketplace;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Api\Data\EventInterfaceFactory;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;
use Plumrocket\PrivateSale\Helper\MagentoVersionChecker;
use Plumrocket\PrivateSale\Model\Config\Source\Eventlandingpage;
use Plumrocket\PrivateSale\Model\ResourceModel\Event;

class Eav implements ModifierInterface
{
    /**
     * @var EventRepositoryInterface
     */
    protected $eventRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var
     */
    private $attributes;

    /**
     * @var Event
     */
    private $eventResource;

    /**
     * @var EventInterfaceFactory
     */
    private $eventFactory;

    /**
     * EAV attribute properties to fetch from meta storage
     * @var array
     */
    private $metaProperties = [
        'dataType' => 'frontend_input',
        'required' => 'is_required',
        'label' => 'frontend_label',
        'sortOrder' => 'sortOrder',
        'notice' => 'note',
        'prefer' => 'prefer',
        'default' => 'default_value'
    ];

    /**
     * @var array
     */
    private $additionalAttributes = [
        'image' => [
            'allowedExtensions' => 'jpg jpeg gif png',
            'maxFileSize' => 2097152,
            'uploaderConfig' => [
                'url' => 'prprivatesale/event_image/upload'
            ]
        ],
        'wysiwyg' => [
            'wysiwyg' => true,
            'template' => 'ui/form/field',
            'wysiwygConfigData' => [
                'height' => '150px'
            ]
        ]
    ];

    /**
     * @var Eventlandingpage
     */
    private $eventlandingpageOptions;

    /**
     * @var ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

    /**
     * @var MagentoVersionChecker
     */
    private $versionChecker;

    /**
     * @var \Plumrocket\Base\Model\IsModuleInMarketplace
     */
    private $isModuleInMarketplace;

    /**
     * @param \Magento\Framework\App\RequestInterface                      $request
     * @param \Magento\Framework\Stdlib\ArrayManager                       $arrayManager
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\Event            $eventResource
     * @param \Plumrocket\PrivateSale\Api\EventRepositoryInterface         $eventRepository
     * @param \Plumrocket\PrivateSale\Api\Data\EventInterfaceFactory       $eventFactory
     * @param \Plumrocket\PrivateSale\Model\Config\Source\Eventlandingpage $eventlandingpageOptions
     * @param \Magento\Catalog\Model\Attribute\ScopeOverriddenValue        $scopeOverriddenValue
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Plumrocket\PrivateSale\Helper\MagentoVersionChecker         $versionChecker
     * @param \Plumrocket\Base\Model\IsModuleInMarketplace                 $isModuleInMarketplace
     */
    public function __construct(
        RequestInterface $request,
        ArrayManager $arrayManager,
        Event $eventResource,
        EventRepositoryInterface $eventRepository,
        EventInterfaceFactory $eventFactory,
        Eventlandingpage $eventlandingpageOptions,
        ScopeOverriddenValue $scopeOverriddenValue,
        StoreManagerInterface $storeManager,
        MagentoVersionChecker $versionChecker,
        IsModuleInMarketplace $isModuleInMarketplace
    ) {
        $this->request = $request;
        $this->arrayManager = $arrayManager;
        $this->eventResource = $eventResource;
        $this->eventFactory = $eventFactory;
        $this->eventRepository = $eventRepository;
        $this->eventlandingpageOptions = $eventlandingpageOptions;
        $this->scopeOverriddenValue = $scopeOverriddenValue;
        $this->storeManager = $storeManager;
        $this->versionChecker = $versionChecker;
        $this->isModuleInMarketplace = $isModuleInMarketplace;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->prepareMeta($meta);
        return $this->addUseDefaultValueCheckbox($meta);
    }

    /**
     * @param $meta
     * @return array
     */
    protected function prepareMeta($meta)
    {
        $meta = array_replace_recursive(
            $meta,
            $this->prepareFieldsMeta(
                $this->getFieldsMap(),
                $this->getAttributesMeta()
            )
        );

        $note = $this->isModuleInMarketplace->execute('PrivateSale')
            ? ''
            : 'Please note: you can display popup login & registration if you have <a target="_blank"
 href="https://store.plumrocket.com/popup-login-magento2-extension.html">Plumrocket Popup Login extension</a>
 installed.';

        $meta['private_sale']['children'] = [
            'event_landing_page' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => 'text',
                            'formElement' => 'select',
                            'componentType' => Field::NAME,
                            'label' => __('Private Event Landing Page'),
                            'scopeLabel' => '[STORE VIEW]',
                            'globalScope' => false,
                            'sortOrder' => 40,
                            'options' => $this->eventlandingpageOptions->toOptionArray(),
                            'template' => 'Plumrocket_PrivateSale/form/field',
                            'notice' => sprintf('Prevent unauthorized access to the private event by redirecting to a
                            login page, registration page, or CMS page. %s', $note)
                        ],
                    ]
                ]
            ]
        ];

        return $meta;
    }

    /**
     * Disable fields if they are using default values.
     *
     * @param array $meta
     * @return array
     */
    protected function addUseDefaultValueCheckbox(array $meta)
    {
        $attributes = $this->getAttributes();

        try {
            $event = $this->eventRepository->getById($this->request->getParam('id'));
        } catch (NoSuchEntityException $e) {
            /** @var \Plumrocket\PrivateSale\Api\Data\EventInterface $event */
            $event = $this->eventFactory->create();
        }

        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            $canDisplayUseDefault = $attribute->getScope() === EavAttributeInterface::SCOPE_STORE_TEXT
                && Store::DEFAULT_STORE_ID !== (int) $this->request->getParam('store', Store::DEFAULT_STORE_ID);
            $attributePath = $this->arrayManager->findPath($attributeCode, $meta);

            if ('event_video' === $attributeCode && ! $attributePath) {
                $attributePath = 'content/children/event_video';
            }

            if (! $attributePath || ! $canDisplayUseDefault) {
                continue;
            }

            $meta = $this->arrayManager->merge(
                [$attributePath, 'arguments/data/config'],
                $meta,
                [
                    'service' => [
                        'template' => 'ui/form/element/helper/service',
                    ],
                    'disabled' => ! $this->scopeOverriddenValue->containsValue(
                        EventInterface::class,
                        $event,
                        $attributeCode,
                        $this->request->getParam('store', Store::DEFAULT_STORE_ID)
                    )
                ]
            );
        }

        return $meta;
    }

    /**
     * Prepare fields meta based on xml declaration of form and fields metadata
     *
     * @param array $fieldsMap
     * @param array $fieldsMeta
     * @return array
     */
    private function prepareFieldsMeta($fieldsMap, $fieldsMeta)
    {
        $result = [];
        foreach ($fieldsMap as $fieldSet => $fields) {
            foreach ($fields as $field) {
                if (isset($fieldsMeta[$field])) {
                    $config = $fieldsMeta[$field];

                    if ($field === 'date_container') {
                        $result[$fieldSet]['children'][$field]['children'] = $config;
                        continue;
                    }

                    $result[$fieldSet]['children'][$field]['arguments']['data']['config'] = $config;
                }
            }
        }
        return $result;
    }

    /**
     * Retrieve attributes
     */
    private function getAttributes()
    {
        if (null === $this->attributes) {
            $this->attributes = $this->eventResource
                ->loadAllAttributes($this->eventFactory->create())
                ->getSortedAttributes();
        }

        return $this->attributes;
    }

    /**
     * @return array
     */
    private function getFieldsMap()
    {
        return [
            'general' => [
                'enable',
                'event_name',
                'date_container',
            ],
            'content' => [
                'event_image',
                'small_image',
                'newsletter_image',
                'header_image',
                'event_description',
            ],
            'banner' => [
                'enable_banner',
                'banner_position',
                'banner_template',
            ],
        ];
    }

    /**
     * @return array
     */
    private function getAttributesMeta()
    {
        $meta = [];
        $attributes = $this->getAttributes();
        $formTypeElement = $this->formElement();

        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();

            if ($code === 'event_from') {
                $meta['date_container']['event_from']['arguments']['data']['config']['scopeLabel']
                    = $this->getScopeLabel($attribute);
                continue;
            }

            // use getDataUsingMethod, since some getters are defined and apply additional processing of returning value
            foreach ($this->metaProperties as $metaName => $origName) {
                $value = $attribute->getDataUsingMethod($origName);

                if ('prefer' === $origName && 'checkbox' === $meta[$code]['formElement']) {
                    $value = 'toggle';
                    $meta[$code]['valueMap'] = [
                        'true' => '1',
                        'false' => '0',
                    ];
                }

                if ('required' === $metaName && $value) {
                    $validationRules['required-entry'] = $value;
                    $metaName = 'validation';
                    $value = $validationRules;
                }

                $meta[$code][$metaName] = $value;

                if ('frontend_input' === $origName) {
                    $meta[$code]['formElement'] = $formTypeElement[$value] ?? $value;

                    if (isset($this->additionalAttributes[$value])) {
                        $meta[$code] += $this->additionalAttributes[$value];
                    }
                }

                if ($attribute->usesSource()) {
                    $meta[$code]['options'] = $attribute->getSource()->getAllOptions();
                }
            }

            $meta[$code]['scopeLabel'] = $this->getScopeLabel($attribute);
            $meta[$code]['componentType'] = Field::NAME;
        }

        return $meta;
    }

    /**
     * Retrieve scope label
     *
     * @param $attribute
     * @return \Magento\Framework\Phrase|string
     */
    private function getScopeLabel($attribute)
    {
        if ($this->storeManager->isSingleStoreMode()) {
            return '';
        }

        switch ($attribute->getScope()) {
            case ProductAttributeInterface::SCOPE_GLOBAL_TEXT:
                return __('[GLOBAL]');
            case ProductAttributeInterface::SCOPE_WEBSITE_TEXT:
                return __('[WEBSITE]');
            case ProductAttributeInterface::SCOPE_STORE_TEXT:
                return __('[STORE VIEW]');
        }

        return '';
    }

    /**
     * @return string[]
     */
    private function formElement()
    {
        $formElement = [
            'text' => 'input',
            'boolean' => 'checkbox',
            'image' => 'imageUploader',
        ];

        if ($this->versionChecker->isOldVersion()) {
            $formElement['image'] = 'fileUploader';
        }

        return $formElement;
    }
}
