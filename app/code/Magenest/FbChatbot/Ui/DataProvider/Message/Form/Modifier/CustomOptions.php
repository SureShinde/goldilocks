<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\FbChatbot\Ui\DataProvider\Message\Form\Modifier;

use Magenest\FbChatbot\Ui\Component\Form\ButtonActionTypes;
use Magenest\FbChatbot\Ui\Component\Form\CategoryLevels;
use Magenest\FbChatbot\Ui\Component\Form\MessageName;
use Magenest\FbChatbot\Ui\Component\Form\MessageTypes;
use Magenest\FbChatbot\Ui\Component\Listing\Columns\ButtonTypes;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class CustomOptions implements ModifierInterface
{
    const FORM_NAME = 'message_form';
    /**#@+
     * Group values
     */
    const MESSAGE_TYPES = 'message_types';
    const GROUP_CUSTOM_OPTIONS_PREVIOUS_NAME = 'search-engine-optimization';
    const GROUP_CUSTOM_OPTIONS_DEFAULT_SORT_ORDER = 31;
    /**#@-*/

    /**#@+
     * Button values
     */
    const BUTTON_ADD = 'button_add';
    /**#@-*/

    /**#@+
     * Container values
     */
    const CONTAINER_HEADER_NAME = 'container_header';
    const CONTAINER_OPTION = 'container_option';
    const CONTAINER_COMMON_NAME = 'container_common';
    const CONTAINER_MESSAGE_TYPE = 'container_message_type';
    /**#@-*/

    /**#@+
     * Grid values
     */
    const GRID_OPTIONS_NAME = 'options';
    const GRID_TYPE_BUTTON_NAME = 'values';
    /**#@-*/

    /**#@+
     * Field values
     */
    const FIELD_OPTION_ID = 'option_id';
    const FIELD_TITLE_NAME = 'title';
    const FIELD_INCLUDE_BUTTONS_NAME = 'include_button';
    const FIELD_MESSAGE_TYPE_NAME = 'message_type';
    const FIELD_CATEGORY_LEVEL_NAME = 'category_level';
    const FIELD_PRODUCT_NAME = 'product_name';
    const FIELD_TEXT_NAME = 'text';
    const FIELD_IMAGE_NAME = 'image';
    const FIELD_SORT_ORDER_NAME = 'sort_order';
    const FIELD_IS_DELETE = 'is_delete';
    const FIELD_BUTTON_TYPE = 'button_type';
    const FIELD_BUTTON_ACTION = 'button_action';
    const FIELD_BUTTON_LABEL = 'button_label';
    /**#@-*/


    /**
     * @var array
     * @since 101.0.0
     */
    protected $meta = [];

    /**
     * @var ButtonTypes
     */
    private $buttonTypes;

    /**
     * @var MessageTypes
     */
    private $messageTypes;

    /**
     * @var CategoryLevels
     */
    private $categoryLevels;

    /**
     * @var ButtonActionTypes
     */
    private $buttonActionTypes;

    /**
     * @var MessageName
     */
    private $messageName;

    /**
     * CustomOptions constructor.
     * @param ButtonTypes $buttonTypes
     * @param ButtonActionTypes $buttonActionTypes
     * @param MessageTypes $messageTypes
     * @param CategoryLevels $categoryLevels
     * @param MessageName $messageName
     */
    public function __construct(
        ButtonTypes $buttonTypes,
        ButtonActionTypes $buttonActionTypes,
        MessageTypes $messageTypes,
        CategoryLevels $categoryLevels,
        MessageName $messageName
    )
    {
        $this->buttonTypes = $buttonTypes;
        $this->buttonActionTypes = $buttonActionTypes;
        $this->messageTypes = $messageTypes;
        $this->categoryLevels = $categoryLevels;
        $this->messageName = $messageName;
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {

    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->createCustomOptionsPanel();

        return $this->meta;
    }

    /**
     * Create "Customizable Options" panel
     *
     * @return $this
     * @since 101.0.0
     */
    protected function createCustomOptionsPanel()
    {
        $this->meta = array_replace_recursive(
            $this->meta,
            [
                static::MESSAGE_TYPES => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Message Content'),
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::MESSAGE_TYPES,
                                'collapsible' => false,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $this->meta,
                                    static::GROUP_CUSTOM_OPTIONS_PREVIOUS_NAME,
                                    static::GROUP_CUSTOM_OPTIONS_DEFAULT_SORT_ORDER
                                ),
                            ],
                        ],
                    ],
                    'children' => [
                        static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                        static::GRID_OPTIONS_NAME => $this->getOptionsGridConfig(30)
                    ]
                ]
            ]
        );

        return $this;
    }

    /**
     * Retrieve next group sort order
     *
     * @param array $meta
     * @param array|string $groupCodes
     * @param int $defaultSortOrder
     * @param int $iteration
     * @return int
     * @since 101.0.0
     */
    protected function getNextGroupSortOrder(array $meta, $groupCodes, $defaultSortOrder, $iteration = 1)
    {
        $groupCodes = (array)$groupCodes;

        foreach ($groupCodes as $groupCode) {
            if (isset($meta[$groupCode]['arguments']['data']['config']['sortOrder'])) {
                return $meta[$groupCode]['arguments']['data']['config']['sortOrder'] + $iteration;
            }
        }

        return $defaultSortOrder;
    }

    /**
     * Get config for header container
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getHeaderContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $sortOrder,
                        'content' => __('Create the message variations.'),
                    ],
                ],
            ],
            'children' => [
                static::BUTTON_ADD => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title' => __('Add Message'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magenest_FbChatbot/js/form/element/add-message',
                                'sortOrder' => 20,
                                'actions' => [
                                    [
                                        'targetName' => '${ $.ns }.${ $.ns }.' . static::MESSAGE_TYPES
                                            . '.' . static::GRID_OPTIONS_NAME,
                                        '__disableTmpl' => ['targetName' => false],
                                        'actionName' => 'processingAddChild',
                                    ]
                                ]
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for the whole grid
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getOptionsGridConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Message'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Catalog/js/components/dynamic-rows-import-custom-options',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'addButton' => false,
                        'renderDefaultRecord' => false,
                        'columnsHeader' => false,
                        'collapsibleHeader' => true,
                        'sortOrder' => $sortOrder,
                        'imports' => [
                            'insertData' => '${ $.provider }:${ $.dataProvider }',
                            '__disableTmpl' => ['insertData' => false],
                        ]
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'headerLabel' => __('New Message'),
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::CONTAINER_OPTION . '.' . static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::CONTAINER_OPTION => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Fieldset::NAME,
                                        'collapsible' => true,
                                        'label' => null,
                                        'sortOrder' => 10,
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(50),
                                static::CONTAINER_COMMON_NAME => $this->getCommonContainerConfig(10),
                                static::CONTAINER_MESSAGE_TYPE => $this->getMessageTypeContainerConfig(20),
                                static::GRID_TYPE_BUTTON_NAME => $this->getButtonTypeGridConfig(40)
                            ]
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Get config for container with common fields for any type
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getMessageTypeContainerConfig($sortOrder)
    {
        $commonContainer = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'formElement' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'breakLine' => false,
                        'showLabel' => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                static::FIELD_MESSAGE_TYPE_NAME => $this->getGeneralFieldConfig(
                    20,
                    [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Message Type'),
                                    'component' => 'Magenest_FbChatbot/js/form/element/message-type',
                                    'dataScope' => static::FIELD_MESSAGE_TYPE_NAME,
                                    'formElement' => Select::NAME,
                                    'options' => $this->messageTypes->getAllOptions()
                                ]
                            ]
                        ]
                    ]
                ),
                static::FIELD_CATEGORY_LEVEL_NAME => $this->getGeneralFieldConfig(
                    30,
                    [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Category Level'),
                                    'component' => 'Magenest_FbChatbot/js/form/element/category_level',
                                    'formElement' => Select::NAME,
                                    'dataScope' => static::FIELD_CATEGORY_LEVEL_NAME,
                                    'options' => $this->categoryLevels->getAllOptions()
                                ]
                            ]
                        ]
                    ]
                ),
                static::FIELD_TEXT_NAME => $this->getGeneralFieldConfig(
                    40,
                    [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Text'),
                                    'component' => 'Magenest_FbChatbot/js/form/element/text_name',
                                    'formElement' => Input::NAME,
                                    'dataScope' => static::FIELD_TEXT_NAME,
                                    "validation" => [
                                        'max_text_length' => 640
                                    ]
                                ],
                            ],
                        ],
                    ]
                ),
                static::FIELD_PRODUCT_NAME => $this->getGeneralFieldConfig(
                    40,
                    [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Product Name'),
                                    'component' => 'Magenest_FbChatbot/js/form/element/product_name',
                                    'formElement' => Input::NAME,
                                    'dataScope' => static::FIELD_PRODUCT_NAME,
                                    "validation" => [
                                        'required-entry' => false
                                    ]
                                ],
                            ],
                        ],
                    ]
                ),
                static::FIELD_IMAGE_NAME => $this->getGeneralFieldConfig(
                    50,
                    [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Image'),
                                    'component' => 'Magenest_FbChatbot/js/form/element/uploadImage',
                                    'formElement' => 'imageUploader',
                                    'elementTmpl' => 'Magenest_FbChatbot/form/element/uploader/uploader',
                                    'previewTmpl' => 'Magento_Catalog/image-preview',
                                    'dataScope' => static::FIELD_IMAGE_NAME,
                                    'componentType' => 'imageUploader',
                                    'maxFileSize' => 4194304,
                                    'allowedExtensions' => implode(' ',['jpg','jpeg','gif','png']),
                                    'uploaderConfig' => [
                                        'url' => 'chatbot/upload/image'
                                    ]
                                ],
                            ],
                        ],
                    ]
                ),
            ]
        ];


        return $commonContainer;

    }

    /**
     * Get config for container with common fields for any type
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getCommonContainerConfig($sortOrder)
    {
        $commonContainer = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'formElement' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'breakLine' => false,
                        'showLabel' => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                static::FIELD_TITLE_NAME => $this->getTitleFieldConfig(
                    20,
                    [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Message Name'),
                                    'component' => 'Magento_Catalog/component/static-type-input',
                                    'valueUpdate' => 'input',
                                    'imports' => [
                                        'optionId' => '${ $.provider }:${ $.parentScope }.option_id',
                                        'isUseDefault' => '${ $.provider }:${ $.parentScope }.is_use_default'
                                    ],
                                    "validation" => [
                                        'required-entry' => false
                                    ]
                                ],
                            ],
                        ],
                    ]
                ),
                static::FIELD_INCLUDE_BUTTONS_NAME => $this->getIncludeButtonsFieldConfig(40)
            ]
        ];
        return $commonContainer;
    }

    /**
     * @param $sortOrder
     * @return array
     */
    protected function getButtonLabelTypesConfig($sortOrder)
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Button Label'),
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'dataScope' => static::FIELD_BUTTON_LABEL,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'validation' => [
                                'required-entry' => true
                            ]
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @param $sortOrder
     * @param array $option
     * @return array
     */
    protected function getButtonActionTypesConfig($sortOrder, $option = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Action'),
                            'componentType' => Field::NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'options' => $this->buttonActionTypes->toOptionArray(),
                            'validation' => [
                                'required-entry' => true
                            ]
                        ],
                    ],
                ],
            ],
            $option
        );
    }

    /**
     * @param $sortOrder
     * @return array
     */
    protected function getButtonTypesConfig($sortOrder)
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Button'),
                            'componentType' => Field::NAME,
                            'formElement' => Select::NAME,
                            'dataScope' => static::FIELD_BUTTON_TYPE,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'options' => $this->buttonTypes->toOptionArray(),
                            'validation' => [
                                'required-entry' => true
                            ]
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Get config for grid for "select" types
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getButtonTypeGridConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Button'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magenest_FbChatbot/js/form/element/buttons-dynamic-row',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'renderDefaultRecord' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        self::FIELD_BUTTON_TYPE => $this->getButtonTypesConfig(10),
                        self::FIELD_BUTTON_ACTION => $this->getButtonActionTypesConfig(
                            30,
                            [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'component' => 'Magenest_FbChatbot/js/form/element/button_action',
                                            'dataScope' => static::FIELD_BUTTON_ACTION,
                                            'formElement' => Select::NAME,
                                            'options' => [$this->messageName->getAllOptions(),$this->buttonActionTypes->toOptionArray()]
                                        ]
                                    ]
                                ]
                            ]),
                        self::FIELD_BUTTON_LABEL => $this->getButtonLabelTypesConfig(40),
                        static::FIELD_IS_DELETE => $this->getIsDeleteFieldConfig(50)
                    ]
                ]
            ]
        ];
    }

    /**
     * Get config for hidden id field
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getOptionIdFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Input::NAME,
                        'componentType' => Field::NAME,
                        'dataScope' => static::FIELD_OPTION_ID,
                        'sortOrder' => $sortOrder,
                        'visible' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Title" fields
     *
     * @param int $sortOrder
     * @param array $options
     * @return array
     * @since 101.0.0
     */
    protected function getTitleFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Title'),
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'dataScope' => static::FIELD_TITLE_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'validation' => [
                                'required-entry' => true
                            ],
                        ],
                    ],
                ],
            ],
            $options
        );
    }

    protected function getGeneralFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Field::NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'validation' => [
                                'required-entry' => true
                            ],
                        ],
                    ],
                ],
            ],
            $options
        );
    }

    /**
     * Get config for "Required" field
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getIncludeButtonsFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Include Buttons'),
                        'component' => 'Magenest_FbChatbot/js/custom-include-buttons',
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => static::FIELD_INCLUDE_BUTTONS_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'value' => '1',
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0'
                        ],
                        'groupsConfig' => [
                            'select' => [
                                'values' => ['0', '1'],
                                'indexes' => [
                                    static::GRID_TYPE_BUTTON_NAME
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field used for sorting
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getPositionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Hidden::NAME,
                        'dataScope' => static::FIELD_SORT_ORDER_NAME,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field used for removing rows
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getIsDeleteFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => ActionDelete::NAME,
                        'fit' => true,
                        'sortOrder' => $sortOrder
                    ],
                ],
            ],
        ];
    }

}
