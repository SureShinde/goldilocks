<?php
namespace Magenest\Popup\Block\Adminhtml\Template\Edit;

/**
 * Class Form
 * @package Magenest\Popup\Block\Adminhtml\Template\Edit
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Magenest\Popup\Helper\Data
     */
    protected $_helperData;

    /**
     * Form constructor.
     * @param \Magenest\Popup\Helper\Data $helperData
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magenest\Popup\Helper\Data $helperData,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->_helperData = $helperData;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_form');
        $this->setTitle('General');
    }

    /**
     * @return \Magenest\Popup\Block\Adminhtml\Template\Edit\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareForm()
    {
        $popupTemplate = $this->_coreRegistry->registry('popup_template');
        $templateStatus = $popupTemplate->getStatus();
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save'),
                'method' => 'post',
            ],
        ]);
        $form->setUseContainer(true);
        $form->setHtmlIdPrefix('magenest_popup_template');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Popup Template'), 'class' => 'general-fieldset']);
        $fieldset->addField(
            'template_id',
            'hidden',
            [
                'name'=>'template_id',
                'label' => __('Id'),
                'title' => __('Id')
            ]
        );
        $fieldset->addField(
            'template_name',
            'text',
            [
                'name'=>'template_name',
                'label' => __('Template Name'),
                'title' => __('Template Name'),
                'required' => true,
            ]
        );

        if ($templateStatus === null) {
            $fieldset->addField(
                'template_type',
                'select',
                [
                    'name' => 'template_type',
                    'label' => __('Template Type'),
                    'required' => true,
                    'values' => $this->_helperData->getTemplateType()
                ]
            );
        } else {
            $fieldset->addField(
                'template_type',
                'select',
                [
                    'name' => 'template_type',
                    'label' => __('Template Type'),
                    'required' => true,
                    'disabled' => true,
                    'values' => $this->_helperData->getTemplateType()
                ]
            );

        }

        $widgetFilters = ['is_email_compatible' => 1];
        $wysiwygConfig = $this->_wysiwygConfig->getConfig(['widget_filters' => $widgetFilters, 'add_variables' => true]);
        $fieldset->addField(
            'html_content',
            'editor',
            [
                'name' => 'html_content',
                'label' => __('HTML Content'),
                'state' => 'html',
                'required' => true,
                'value' => $popupTemplate->getHtmlContent(),
                'style' => 'height: 600px;',
                'config' => $wysiwygConfig,
                'note'      => __('Note: If you want to add custom template with form for submission, you must have these following setting so that form validation can work properly.<br><ol>
<li>Form ID must be <span style="font-weight: bold">popup-action-form</span></li><li>Each input field must have <span style="font-weight: bold">data-validate="{required:true}"</span>. Especially with email input type, the value of this attribute must be <span style="font-weight: bold">data-validate="{required:true, "validate-email":true}"</span></li>
</ol>')
            ]
        );
        $fieldset->addField(
            'css_style',
            'textarea',
            [
                'name' => 'css_style',
                'label' => __('Css Styles'),
                'container_id' => 'field_newsletter_styles'
            ]
        );
        $data = $popupTemplate->getData();
        if (!empty($data)) {
            $form->setValues($data);
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
