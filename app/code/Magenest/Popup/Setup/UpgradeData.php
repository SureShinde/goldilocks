<?php
namespace Magenest\Popup\Setup;

use Magenest\Popup\Helper\Data;
use Magenest\Popup\Model\LogFactory;
use Magenest\Popup\Model\PopupFactory;
use Magenest\Popup\Model\ResourceModel\Log;
use Magenest\Popup\Model\ResourceModel\Popup;
use Magenest\Popup\Model\ResourceModel\Template;
use Magenest\Popup\Model\ResourceModel\Template\CollectionFactory;
use Magenest\Popup\Model\TemplateFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UpgradeData
 * @package Magenest\Popup\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /** @var Data */
    protected $_helperData;

    /** @var TemplateFactory */
    protected $_popupTemplateFactory;

    /** @var CollectionFactory */
    protected $_popupTemplateCollection;

    /** @var LogFactory */
    private $_logModel;

    /** @var PopupFactory */
    private $_popupModel;

    /** @var Popup\CollectionFactory */
    protected $_popupCollection;

    /** @var Popup */
    protected $_popupResource;

    /** @var StoreManagerInterface */
    protected $_storeManager;

    /** @var Json */
    protected $_json;

    /** @var Template */
    private $popupTemplateResources;

    /** @var Log */
    private $logResources;

    /** @var Log\CollectionFactory */
    private $logCollection;

    /** @var Template\Collection|null */
    private $templateCollection = null;

    /**
     * UpgradeData constructor.
     * @param Popup\CollectionFactory $popupCollection
     * @param Popup $popupResource
     * @param Data $helperData
     * @param TemplateFactory $popupTemplateFactory
     * @param CollectionFactory $popupTemplateCollection
     * @param LogFactory $logModel
     * @param Log $logResources
     * @param Log\CollectionFactory $logCollection
     * @param PopupFactory $popupModel
     * @param Json $json
     * @param Template $popupTemplateResources
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Popup\CollectionFactory$popupCollection,
        Popup $popupResource,
        Data $helperData,
        TemplateFactory $popupTemplateFactory,
        CollectionFactory $popupTemplateCollection,
        LogFactory $logModel,
        Log $logResources,
        Log\CollectionFactory $logCollection,
        PopupFactory $popupModel,
        Json $json,
        Template $popupTemplateResources,
        StoreManagerInterface $storeManager
    ) {
        $this->_helperData = $helperData;
        $this->_popupTemplateFactory = $popupTemplateFactory;
        $this->popupTemplateResources = $popupTemplateResources;
        $this->_popupTemplateCollection = $popupTemplateCollection;
        $this->_logModel = $logModel;
        $this->logResources = $logResources;
        $this->logCollection = $logCollection;
        $this->_popupModel = $popupModel;
        $this->_popupCollection = $popupCollection;
        $this->_popupResource = $popupResource;
        $this->_storeManager = $storeManager;
        $this->_json = $json;
    }

    /**
     * @return Template\Collection
     */
    private function getTemplateCollection()
    {
        if ($this->templateCollection === null) {
            $this->templateCollection = $this->_popupTemplateCollection->create();
        }

        return $this->templateCollection->reset();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws AlreadyExistsException
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.0') < 0) {
            $this->addDefaultTemplate();
            $this->updateLogContent();
        }

        if (version_compare($context->getVersion(), '1.2.0') < 0) {
            $this->updatePopupConfig();

            // Update new default template
            $popup_type_default = $this->_helperData->getPopupTemplateDefault();

            // set status for template default
            $this->setDefaultTemplateStatus($popup_type_default);

            // set status for tempalate_edited
            $this->setEditedTemplateStatus();

            // set status for tempalate_default_deleted
            $this->setDeletedTemplateStatus($popup_type_default);

            // add class = 'magenest-popup-step' to html_content of template default
            $this->updateDefaultTemplateHtml($popup_type_default);
        }
    }

    /**
     * @throws FileSystemException
     * @throws LocalizedException
     */
    private function addDefaultTemplate()
    {
        $popup_type = [
            [
                'path' => 'hot_deal/popup_1',
                'type' => '6',
                'class' => 'popup-default-40',
            ],
            [
                'path' => 'hot_deal/popup_2',
                'type' => '6',
                'class' => 'popup-default-41',
            ]
        ];
        $data = [];
        $count = $this->_popupTemplateCollection->create()->getSize();

        if (!empty($popup_type)) {
            foreach ($popup_type as $type) {
                $data[] = [
                    'template_name' => "Default Template " . $count,
                    'template_type' => $type['type'],
                    'html_content' => $this->_helperData->getTemplateDefault($type['path']),
                    'css_style' => '',
                    'class' => $type['class'],
                    'status' => 1
                ];
                $count++;
            }

            $this->popupTemplateResources->insertMultiple($data);
        }
    }

    /**
     * @throws \Exception
     */
    private function updateLogContent()
    {
        $logCollection = $this->logCollection->create();
        $popupModel = $this->_popupModel->create();
        /** @var \Magenest\Popup\Model\Log $log */
        foreach ($logCollection as $log) {
            $string = $log->getContent();
            if ($this->isJSON($string)) {
                $content = $this->_json->unserialize($string ?? 'null');
                if (is_array($content)) {
                    $count = 0;
                    $result = '';
                    foreach ($content as $raw) {
                        if ($count == 0) {
                            $count++;
                            continue;
                        }
                        if (isset($raw['name'])) {
                            $result .= $raw['name'] . ": " . $raw['value'] . "| ";
                        }
                    }
                    $string = $result ?? $content;
                }
            }

            $this->_popupResource->load($popupModel, $log->getPopupId());
            $log->setPopupName($popupModel->getPopupName());
            $log->setContent($string);

            $this->logResources->save($log);
            $popupModel->unsetData();
        }
    }

    /**
     * @throws AlreadyExistsException
     */
    private function updatePopupConfig()
    {
        $popups = $this->_popupCollection->create();
        /** @var  \Magenest\Popup\Model\Popup $popup */
        foreach ($popups as $popup) {
            if ($popup->getData('floating_button_text_color') && $popup->getData('floating_button_text_color')[0] != '#') {
                $popup->setData('floating_button_text_color', '#' . $popup->getData('floating_button_text_color'));
            }
            if ($popup->getData('floating_button_background_color') && $popup->getData('floating_button_background_color')[0] != '#') {
                $popup->setData('floating_button_background_color', '#' . $popup->getData('floating_button_background_color'));
            }
            if (!$popup->getData('floating_button_hover_color')) {
                $popup->setData('floating_button_hover_color', '#eaeaea');
            }
            if (!$popup->getData('floating_button_text_hover_color')) {
                $popup->setData('floating_button_text_hover_color', '#0e3e81');
            }
            if (!$popup->setData('customer_group_ids')) {
                $popup->setData('customer_group_ids', '32000');
            }
            $this->_popupResource->save($popup);
        }
    }

    /**
     * @param $defaultPopup
     * @throws AlreadyExistsException
     * @throws FileSystemException
     */
    private function setDefaultTemplateStatus($defaultPopup)
    {
        foreach ($defaultPopup as $type) {
            /** @var \Magenest\Popup\Model\Template $matchedTemplate */
            $matchedTemplate = $this->getTemplateCollection()
                ->addFieldToFilter('class', $type['class'])
                ->addFieldToFilter('template_name', $type['name'])
                ->addFieldToFilter('template_type', $type['type'])
                ->addFieldToFilter('html_content', $this->_helperData->getTemplateDefault($type['path']))
                ->addFieldToFilter('css_style', '')
                ->addFieldToFilter('status', 0)
                ->setPageSize(0)->setCurPage(0)
                ->getFirstItem();
            if ($matchedTemplate->getTemplateId()) {
                $matchedTemplate->setStatus(1);
                $this->popupTemplateResources->save($matchedTemplate);
            }
        }
    }

    /**
     * @throws AlreadyExistsException
     */
    private function setEditedTemplateStatus()
    {
        $templateEdited = $this->getTemplateCollection()
            ->addFieldToFilter('status', ['nin' => [1]])
            ->getItems();
        /** @var \Magenest\Popup\Model\Template $template */
        foreach ($templateEdited as $template) {
            $template->setStatus(2);
            $this->popupTemplateResources->save($template);
        }
    }

    /**
     * @param $defaultPopup
     * @throws FileSystemException
     * @throws LocalizedException
     */
    private function setDeletedTemplateStatus($defaultPopup)
    {
        $data_template_default = [];
        $templateDefault = $this->getTemplateCollection()
            ->addFieldToFilter('status', ['eq' => 1])
            ->addFieldToSelect('class')
            ->getData();
        $templateClass = array_column($templateDefault, 'class');
        foreach ($defaultPopup as $type) {
            $check = in_array($type['class'], $templateClass);
            if (!$check) {
                $data_template_default[] = [
                    'template_name' => $type['name'],
                    'template_type' => $type['type'],
                    'html_content' => $this->_helperData->getTemplateDefault($type['path']),
                    'css_style' => '',
                    'class' => $type['class'],
                    'status' => 1
                ];
            }
        }
        if (!empty($data_template_default)) {
            $this->popupTemplateResources->insertMultiple($data_template_default);
        }
    }

    /**
     * @param $defaultPopup
     * @throws AlreadyExistsException
     * @throws FileSystemException
     */
    private function updateDefaultTemplateHtml($defaultPopup)
    {
        $type_array = [];
        $templateDefault = $this->getTemplateCollection()
            ->addFieldToFilter('status', ['eq' => 1])
            ->getItems();
        foreach ($defaultPopup as $type) {
            $type_array[$type['class']] = $type['path'];
        }
        /** @var \Magenest\Popup\Model\Template $template */
        foreach ($templateDefault as $template) {
            if (isset($type_array[$template['class']])) {
                $html_content = $this->_helperData->getTemplateDefault($type_array[$template['class']]);
                $template->setHtmlContent($html_content);
                $this->popupTemplateResources->save($template);
            }
        }
    }

    /**
     * @param $string
     * @return bool
     */
    public function isJSON($string)
    {
        return is_string($string) && is_array($this->_json->unserialize($string ?? 'null'));
    }
}
