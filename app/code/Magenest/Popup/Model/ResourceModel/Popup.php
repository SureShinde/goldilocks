<?php
namespace Magenest\Popup\Model\ResourceModel;

use Magenest\Popup\Helper\Data;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

class Popup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const SPECIFIC_ENTITIES = 'specific';
    public const ALL_ENTITIES = 'all';
    public const DEFAULT_LAYOUT_HANDLE = 'default';
    public const PRODUCT_LAYOUT_HANDLE = 'catalog_product_view';
    public const SINGLE_PRODUCT_LAYOUT_HANDLE = 'catalog_product_view_id_{{ID}}';
    public const PRODUCT_TYPE_LAYOUT_HANDLE = 'catalog_product_view_type_{{TYPE}}';
    public const ANCHOR_CATEGORY_LAYOUT_HANDLE = 'catalog_category_view_type_layered';
    public const NOTANCHOR_CATEGORY_LAYOUT_HANDLE = 'catalog_category_view_type_default';
    public const SINGLE_CATEGORY_LAYOUT_HANDLE = 'catalog_category_view_id_{{ID}}';

    /** @var Json */
    protected $json;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var array */
    protected $_specificEntitiesLayoutHandles = [];

    /** @var array */
    protected $_layoutHandles = [];

    /** @var Registry */
    protected $registry;

    /** @var Data */
    protected $helper;

    /** @var Type */
    protected $_productType;

    /**
     * Popup constructor.
     * @param Context $context
     * @param Json $json
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param Data $helper
     * @param Type $productType
     * @param mixed $connectionName
     */
    public function __construct(
        Context $context,
        Json $json,
        StoreManagerInterface $storeManager,
        Registry $registry,
        Data $helper,
        Type $productType,
        $connectionName = null
    ) {
        $this->storeManager = $storeManager;
        $this->json = $json;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->_productType = $productType;
        parent::__construct($context, $connectionName);
    }

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init('magenest_popup', 'popup_id');
        $this->_layoutHandles = [
            'anchor_categories' => self::ANCHOR_CATEGORY_LAYOUT_HANDLE,
            'notanchor_categories' => self::NOTANCHOR_CATEGORY_LAYOUT_HANDLE,
            'all_products' => self::PRODUCT_LAYOUT_HANDLE,
            'all_pages' => self::DEFAULT_LAYOUT_HANDLE,
        ];
        $this->_specificEntitiesLayoutHandles = [
            'anchor_categories' => self::SINGLE_CATEGORY_LAYOUT_HANDLE,
            'notanchor_categories' => self::SINGLE_CATEGORY_LAYOUT_HANDLE,
            'all_products' => self::SINGLE_PRODUCT_LAYOUT_HANDLE,
        ];
        foreach (array_keys($this->_productType->getTypes()) as $typeId) {
            $layoutHandle = str_replace('{{TYPE}}', $typeId, self::PRODUCT_TYPE_LAYOUT_HANDLE);
            $this->_layoutHandles[$typeId . '_products'] = $layoutHandle;
            $this->_specificEntitiesLayoutHandles[$typeId . '_products'] = self::SINGLE_PRODUCT_LAYOUT_HANDLE;
        }
    }

    /**
     * Process after save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magenest\Popup\Model\ResourceModel\Popup
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        // check whether save object in backend
        if ($this->registry->registry('current_widget_instance')) {
            $popupLayoutTable = $this->getTable('magenest_popup_layout');
            $popupId = (int)$object->getId();
            $connection = $this->getConnection();
            $select = $connection->select()->from($popupLayoutTable, 'layout_update_id')->where('popup_id = ?', $popupId);
            $removeLayoutUpdateIds = $connection->fetchCol($select);

            if (!empty($removeLayoutUpdateIds)) {
                $inCond = $connection->prepareSqlCondition('popup_id', $popupId);
                $connection->delete($popupLayoutTable, $inCond);
                $this->_deleteLayoutUpdates($removeLayoutUpdateIds);
            }

            if ($object->getPopupStatus() == "1") {
                $pageGroups = $this->getLayoutHandleUpdate($object);
                if (!empty($pageGroups)) {
                    foreach ($pageGroups as $pageGroup) {
                        $pageLayoutUpdateIds = $this->_saveLayoutUpdates($object, $pageGroup);
                        $data = [
                            'page_group' => isset($pageGroup['group']) ? $pageGroup['group'] : '',
                            'layout_handle' => isset($pageGroup['layout_handle']) ? $pageGroup['layout_handle'] : '',
                            'page_for' => isset($pageGroup['for']) ? $pageGroup['for'] : '',
                            'entities' => isset($pageGroup['entities']) ? $pageGroup['entities'] : ''
                        ];
                        if (!empty($pageLayoutUpdateIds)) {
                            foreach ($pageLayoutUpdateIds as $layoutUpdateId) {
                                $connection->insert(
                                    $popupLayoutTable,
                                    ['popup_id' => $popupId, 'layout_update_id' => $layoutUpdateId]
                                );
                            }
                        }
                    }
                } else {
                    $pageLayoutUpdateIds = $this->_saveLayoutUpdates($object, $pageGroups);
                    $data = [
                        'page_group' => isset($pageGroups['group']) ? $pageGroups['group'] : '',
                        'layout_handle' => isset($pageGroups['layout_handle']) ? $pageGroups['layout_handle'] : '',
                        'page_for' => isset($pageGroups['for']) ? $pageGroups['for'] : '',
                        'entities' => isset($pageGroups['entities']) ? $pageGroups['entities'] : ''
                    ];
                    if (!empty($pageLayoutUpdateIds)) {
                        foreach ($pageLayoutUpdateIds as $layoutUpdateId) {
                            $connection->insert(
                                $popupLayoutTable,
                                ['popup_id' => $popupId, 'layout_update_id' => $layoutUpdateId]
                            );
                        }
                    }
                }
            }
        }
        return parent::_afterSave($object);
    }

    /**
     * Prepare and save layout updates data
     *
     * @param mixed $object
     * @param array $pageGroupData
     * @return string[] of inserted layout updates ids
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _saveLayoutUpdates($object, $pageGroupData)
    {
        $connection = $this->getConnection();
        $pageLayoutUpdateIds = [];
        $storeIds = $this->storeManager->getStore()->getId();
        $themes = $this->helper->getAllThemeId();
        $layoutUpdateTable = $this->getTable('layout_update');
        $layoutUpdateLinkTable = $this->getTable('layout_link');

        if (!empty($pageGroupData)) {
            foreach ($pageGroupData['layout_handle_updates'] as $handle) {
                $xml = '<body><referenceContainer name="after.body.start"><block class="Magenest\Popup\Block\Popup\Display" name="magenest.popup" template="Magenest_Popup::popup/popup.phtml" /></referenceContainer><referenceContainer name="content"><block class="Magenest\Popup\Block\Popup\Button" name="magenest.button" template="Magenest_Popup::popup/button.phtml" /></referenceContainer></body>';
                $insert = ['handle' => $handle, 'xml' => $xml];

                $connection->insert($layoutUpdateTable, $insert);
                $layoutUpdateId = $connection->lastInsertId($layoutUpdateTable);
                $pageLayoutUpdateIds[] = $layoutUpdateId;

                $data = [];
                foreach ($themes as $theme) {
                    if ($theme['area'] == 'frontend') {
                        $data[] = [
                            'store_id' => $storeIds,
                            'theme_id' => $theme['theme_id'],
                            'layout_update_id' => $layoutUpdateId,
                        ];
                    }
                }
                $connection->insertMultiple($layoutUpdateLinkTable, $data);
            }
        } else {
            $xml = '<body><referenceContainer name="after.body.start"><block class="Magenest\Popup\Block\Popup\Display" name="magenest.popup" template="Magenest_Popup::popup/popup.phtml" /></referenceContainer><referenceContainer name="content"><block class="Magenest\Popup\Block\Popup\Button" name="magenest.button" template="Magenest_Popup::popup/button.phtml" /></referenceContainer></body>';
            $insert = ['handle' => 'default', 'xml' => $xml];

            $connection->insert($layoutUpdateTable, $insert);
            $layoutUpdateId = $connection->lastInsertId($layoutUpdateTable);
            $pageLayoutUpdateIds[] = $layoutUpdateId;

            $data = [];
            foreach ($themes as $theme) {
                if ($theme['area'] == 'frontend') {
                    $data[] = [
                        'store_id' => $storeIds,
                        'theme_id' => $theme['theme_id'],
                        'layout_update_id' => $layoutUpdateId,
                    ];
                }
            }
            $connection->insertMultiple($layoutUpdateLinkTable, $data);
        }
        return $pageLayoutUpdateIds;
    }

    /**
     * Get layout handle update
     *
     * @param mixed $object
     * @return array
     */
    public function getLayoutHandleUpdate($object)
    {
        $pageGroupIds = [];
        $tmpPageGroups = [];
        $pageGroups = $this->json->unserialize($object->getData('widget_instance'));
        if ($pageGroups) {
            foreach ($pageGroups as $pageGroup) {
                if (isset($pageGroup[$pageGroup['page_group']])) {
                    $pageGroupData = $pageGroup[$pageGroup['page_group']];
                    if ($pageGroupData['page_id']) {
                        $pageGroupIds[] = $pageGroupData['page_id'];
                    }
                    if (in_array($pageGroup['page_group'], ['pages', 'page_layouts'])) {
                        $layoutHandle = $pageGroupData['layout_handle'];
                    } else {
                        $layoutHandle = $this->_layoutHandles[$pageGroup['page_group']];
                    }
                    if (!isset($pageGroupData['template'])) {
                        $pageGroupData['template'] = '';
                    }
                    $tmpPageGroup = [
                        'page_id' => isset($pageGroupData['page_id']) ? $pageGroupData['page_id'] : '',
                        'group' => isset($pageGroup['page_group']) ? $pageGroup['page_group'] : '',
                        'layout_handle' => $layoutHandle,
                        'for' => isset($pageGroupData['for']) ? $pageGroupData['for'] : '',
                        'block_reference' => isset($pageGroupData['block']) ? $pageGroupData['block'] : '',
                        'entities' => '',
                        'layout_handle_updates' => (array) (isset($pageGroupData['layout_handle']) ? $pageGroupData['layout_handle'] : ''),
                        'template' => $pageGroupData['template'] ? $pageGroupData['template'] : '',
                    ];
                    if ($pageGroupData['for'] == self::SPECIFIC_ENTITIES) {
                        $layoutHandleUpdates = [];
                        foreach (explode(',', $pageGroupData['entities']) as $entity) {
                            $layoutHandleUpdates[] = str_replace(
                                '{{ID}}',
                                $entity,
                                $this->_specificEntitiesLayoutHandles[$pageGroup['page_group']]
                            );
                        }
                        $tmpPageGroup['entities'] = $pageGroupData['entities'];
                        $tmpPageGroup['layout_handle_updates'] = $layoutHandleUpdates;
                    }
                    $tmpPageGroups[] = $tmpPageGroup;
                }
            }
        }
        return $tmpPageGroups;
    }

    /**
     * Delete layout updates by given ids
     *
     * @param array $layoutUpdateIds
     * @return $this
     */
    protected function _deleteLayoutUpdates($layoutUpdateIds)
    {
        $connection = $this->getConnection();
        if ($layoutUpdateIds) {
            $inCond = $connection->prepareSqlCondition('layout_update_id', ['in' => $layoutUpdateIds]);
            $connection->delete($this->getTable('layout_update'), $inCond);
        }
        return $this;
    }
}
