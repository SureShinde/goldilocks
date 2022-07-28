<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreCommon\Plugin\Model\CatalogImportExport\Import;

/**
 * Product import plugin
 */
class Product extends \Ecombricks\Common\Plugin\Plugin
{

    const VALUE_ALL = \Magento\CatalogImportExport\Model\Import\Product::VALUE_ALL;
    const COL_SKU = \Magento\CatalogImportExport\Model\Import\Product::COL_SKU;
    const URL_KEY = \Magento\CatalogImportExport\Model\Import\Product::URL_KEY;
    const COL_TYPE = \Magento\CatalogImportExport\Model\Import\Product::COL_TYPE;
    const COL_ATTR_SET = \Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET;
    const COL_STORE = \Magento\CatalogImportExport\Model\Import\Product::COL_STORE;
    const COL_PRODUCT_WEBSITES = \Magento\CatalogImportExport\Model\Import\Product::COL_PRODUCT_WEBSITES;
    const COL_MEDIA_IMAGE = \Magento\CatalogImportExport\Model\Import\Product::COL_MEDIA_IMAGE;

    /**
     * Connection provider
     *
     * @var \Ecombricks\Common\Model\ResourceModel\ConnectionProvider
     */
    protected $connectionProvider;

    /**
     * Price scope
     *
     * @var \Ecombricks\StoreCommon\Model\Catalog\Product\PriceScope
     */
    protected $priceScope;

    /**
     * Fields map
     *
     * @var array
     */
    protected $fieldsMap = [];

    /**
     * Special attributes map
     *
     * @var array
     */
    protected $specialAttributesMap = [];

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Object\WrapperFactory $wrapperFactory
     * @param \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider
     * @param \Ecombricks\StoreCommon\Model\Catalog\Product\PriceScope $priceScope
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\WrapperFactory $wrapperFactory,
        \Ecombricks\Common\Model\ResourceModel\ConnectionProvider $connectionProvider,
        \Ecombricks\StoreCommon\Model\Catalog\Product\PriceScope $priceScope
    )
    {
        parent::__construct($wrapperFactory);
        $this->connectionProvider = $connectionProvider;
        $this->priceScope = $priceScope;
    }

    /**
     * Get store ID
     * @param array $data
     * @return int
     */
    protected function getStoreId(array &$data): int
    {
        $subject = $this->getSubject();
        $store = $data[self::COL_STORE] ?? null;
        return $store !== null ? (int) $subject->getStoreIdByCode($store) : \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    /**
     * Get current date time
     *
     * @return string
     */
    protected function getCurrentDateTime(): string
    {
        return (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * Reset websites
     *
     * @return $this
     */
    protected function resetWebsites()
    {
        $this->setSubjectPropertyValue('websitesCache', []);
        return $this;
    }

    /**
     * Set websites
     *
     * @param string $sku
     * @param array &$data
     * @return $this
     */
    protected function setWebsites(string $sku, array &$data)
    {
        $subject = $this->getSubject();
        $storeResolver = $this->getSubjectPropertyValue('storeResolver');
        $websitesCache = $this->getSubjectPropertyValue('websitesCache');
        if (!array_key_exists($sku, $websitesCache)) {
            $websitesCache[$sku] = [];
        }
        $websiteCodes = $data[self::COL_PRODUCT_WEBSITES] ?? [];
        if (!empty($websiteCodes)) {
            foreach (explode($subject->getMultipleValueSeparator(), $websiteCodes) as $websiteCode) {
                $websitesCache[$sku][$storeResolver->getWebsiteCodeToId($websiteCode)] = true;
            }
        } else {
            $product = $this->invokeSubjectMethod('retrieveProductBySku', $sku);
            if ($product) {
                foreach ($product->getWebsiteIds() as $websiteId) {
                    $websitesCache[$sku][$websiteId] = true;
                }
            }
        }
        $this->setSubjectPropertyValue('websitesCache', $websitesCache);
        return $this;
    }

    /**
     * Save websites
     *
     * @param array $websites
     * @return $this
     */
    protected function saveWebsites(array $websites)
    {
        $this->invokeSubjectMethod('_saveProductWebsites', $websites);
        return $this;
    }

    /**
     * Reset categories
     *
     * @return $this
     */
    protected function resetCategories()
    {
        $this->setSubjectPropertyValue('categoriesCache', []);
        return $this;
    }

    /**
     * Set categories
     *
     * @param int $number
     * @param string $sku
     * @param array &$data
     * @return $this
     */
    protected function setCategories(int $number, string $sku, array &$data)
    {
        $categoriesCache = $this->getSubjectPropertyValue('categoriesCache');
        if (!array_key_exists($sku, $categoriesCache)) {
            $categoriesCache[$sku] = [];
        }
        $data['rowNum'] = $number;
        $categoryIds = $this->invokeSubjectMethod('processRowCategories', $data);
        foreach ($categoryIds as $categoryId) {
            $categoriesCache[$sku][$categoryId] = true;
        }
        unset($data['rowNum']);
        $this->setSubjectPropertyValue('categoriesCache', $categoriesCache);
        return $this;
    }

    /**
     * Set tier prices
     *
     * @param string $sku
     * @param array $data
     * @param array $tierPrices
     * @return $this
     */
    protected function setTierPrices(string $sku, array &$data, array &$tierPrices)
    {
        $store = $data['_tier_price_website'] ?? null;
        if (empty($store)) {
            return $this;
        }
        $storeResolver = $this->getSubjectPropertyValue('storeResolver');
        $customerGroup = $data['_tier_price_customer_group'];
        $allGroups = $customerGroup == self::VALUE_ALL;
        $tierPrices[$sku][] = [
            'all_groups' => $allGroups,
            'customer_group_id' => $allGroups ? 0 : (int) $customerGroup,
            'qty' => $data['_tier_price_qty'],
            'value' => $data['_tier_price_price'],
            'website_id' => self::VALUE_ALL == $data['_tier_price_website'] || $this->priceScope->isGlobal() ?
                0 : $storeResolver->getWebsiteCodeToId($data['_tier_price_website']),
        ];
        return $this;
    }

    /**
     * Save tier prices
     *
     * @param array &$tierPrices
     * @return $this
     */
    protected function saveTierPrices(array &$tierPrices)
    {
        $this->invokeSubjectMethod('_saveProductTierPrices', $tierPrices);
        return $this;
    }

    /**
     * Set media gallery
     *
     * @param int $number
     * @param string $sku
     * @param array $data
     * @param array $mediaGallery
     * @param array $existingImages
     * @param array $uploadedImages
     * @param array $labelsForUpdate
     * @param array $imagesForChangeVisibility
     * @return $this
     */
    protected function setMediaGallery(
        int $number,
        string $sku,
        array &$data,
        array &$mediaGallery,
        array &$existingImages,
        array &$uploadedImages,
        array &$labelsForUpdate,
        array &$imagesForChangeVisibility
    )
    {
        $subject = $this->getSubject();
        list($images, $labels) = $subject->getImagesFromRow($data);
        $storeId = $this->getStoreId($data);
        $imageHiddenStates = $this->invokeSubjectMethod('getImagesHiddenStates', $data);
        foreach (array_keys($imageHiddenStates) as $image) {
            if (array_key_exists($sku, $existingImages) && array_key_exists($image, $existingImages[$sku])) {
                $images[self::COL_MEDIA_IMAGE][] = $image;
                $uploadedImages[$image] = $image;
            }
            if (empty($images)) {
                $images[self::COL_MEDIA_IMAGE][] = $image;
            }
        }
        $data[self::COL_MEDIA_IMAGE] = [];
        $position = 0;
        foreach ($images as $column => $columnImages) {
            foreach ($columnImages as $columnImageKey => $columnImage) {
                if (!isset($uploadedImages[$columnImage])) {
                    $uploadedFile = $this->invokeSubjectMethod('uploadMediaFiles', $columnImage);
                    $uploadedFile = $uploadedFile ?: $this->invokeSubjectMethod('getSystemFile', $columnImage);
                    if ($uploadedFile) {
                        $uploadedImages[$columnImage] = $uploadedFile;
                    } else {
                        unset($data[$column]);
                        if (version_compare(\Magento\Framework\App\ObjectManager::getInstance()->get('\\Magento\\Framework\\App\\ProductMetadata')->getVersion(), '2.3.2', '>=')) {
                            $subject->addRowError(
                                \Magento\CatalogImportExport\Model\Import\Product\ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE,
                                $number,
                                null,
                                null,
                                \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                            );
                        } else {
                            $this->invokeSubjectMethod(
                                'skipRow',
                                $number,
                                \Magento\CatalogImportExport\Model\Import\Product\ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE
                            );
                        }
                    }
                } else {
                    $uploadedFile = $uploadedImages[$columnImage];
                }
                if ($uploadedFile && $column !== self::COL_MEDIA_IMAGE) {
                    $data[$column] = $uploadedFile;
                }
                if (!$uploadedFile || isset($mediaGallery[$storeId][$sku][$uploadedFile])) {
                    continue;
                }
                if (isset($existingImages[$sku][$uploadedFile])) {
                    $currentFileData = $existingImages[$sku][$uploadedFile];
                    if (isset($labels[$column][$columnImageKey]) && $labels[$column][$columnImageKey] != $currentFileData['label']) {
                        $labelsForUpdate[] = ['label' => $labels[$column][$columnImageKey], 'imageData' => $currentFileData];
                    }
                    if (array_key_exists($uploadedFile, $imageHiddenStates) && $currentFileData['disabled'] != $imageHiddenStates[$uploadedFile]) {
                        $imagesForChangeVisibility[] = ['disabled' => $imageHiddenStates[$uploadedFile], 'imageData' => $currentFileData];
                    }
                } else {
                    if ($column == self::COL_MEDIA_IMAGE) {
                        $data[$column][] = $uploadedFile;
                    }
                    $mediaGallery[$storeId][$sku][$uploadedFile] = [
                        'attribute_id' => $subject->getMediaGalleryAttributeId(),
                        'label' => isset($labels[$column][$columnImageKey]) ? $labels[$column][$columnImageKey] : '',
                        'position' => ++$position,
                        'disabled' => isset($imageHiddenStates[$columnImage]) ? $imageHiddenStates[$columnImage] : '0',
                        'value' => $uploadedFile,
                    ];
                }
            }
        }
        return $this;
    }

    /**
     * Set attributes
     *
     * @param string $sku
     * @param array $data
     * @param array $attributes
     * @return $this
     */
    protected function setAttributes(string $sku, array &$data, array &$attributes)
    {
        $subject = $this->getSubject();
        $productFactory = $this->getSubjectPropertyValue('_proxyProdFactory');
        $dateAttributeCodes = $this->getSubjectPropertyValue('dateAttrCodes');
        $dateTime = $this->getSubjectPropertyValue('dateTime');
        $localeDate = $this->getSubjectPropertyValue('_localeDate');
        $storeResolver = $this->getSubjectPropertyValue('storeResolver');
        $storeId = $this->getStoreId($data);
        $product = $productFactory->create(['data' => $data]);
        foreach ($data as $code => $value) {
            $attribute = $subject->retrieveAttributeByCode($code);
            $attributeId = $attribute->getId();
            $attributeCode = $attribute->getAttributeCode();
            $attributeBackend = $attribute->getBackend();
            $attributeBackendModel = $attribute->getBackendModel();
            $attributeTable = $attributeBackend->getTable();
            $attributeStoreIds = [0];
            if ('datetime' == $attribute->getBackendType() && (in_array($attributeCode, $dateAttributeCodes) || $attribute->getIsUserDefined())) {
                $value = $dateTime->formatDate($value, false);
            } elseif ('datetime' == $attribute->getBackendType() && strtotime($value)) {
                $value = gmdate('Y-m-d H:i:s', $localeDate->date($value)->getTimestamp());
            } elseif ($attributeBackendModel) {
                $attributeBackend->beforeSave($product);
                $value = $product->getData($attributeCode);
            }
            if ($storeId) {
                if (\Magento\CatalogImportExport\Model\Import\Product::SCOPE_WEBSITE == $attribute->getIsGlobal()) {
                    if (!isset($attributes[$attributeTable][$sku][$attributeId][$storeId])) {
                        $attributeStoreIds = $storeResolver->getStoreIdToWebsiteStoreIds($storeId);
                    }
                } elseif (\Magento\CatalogImportExport\Model\Import\Product::SCOPE_STORE == $attribute->getIsGlobal()) {
                    $attributeStoreIds = [$storeId];
                }
                if (!$this->invokeSubjectMethod('isSkuExist', $sku)) {
                    $attributeStoreIds[] = 0;
                }
            }
            foreach ($attributeStoreIds as $attributeStoreId) {
                if (!isset($attributes[$attributeTable][$sku][$attributeId][$attributeStoreId])) {
                    $attributes[$attributeTable][$sku][$attributeId][$attributeStoreId] = $value;
                }
            }
            $attribute->setBackendModel($attributeBackendModel);
        }
        return $this;
    }

    /**
     * Save products
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveProducts()
    {
        $subject = $this->getSubject();
        $dataSourceModel = $this->getSubjectPropertyValue('_dataSourceModel');
        $skuProcessor = $this->getSubjectPropertyValue('skuProcessor');
        $categoryProcessor = $this->getSubjectPropertyValue('categoryProcessor');
        $catalogConfig = $this->getSubjectPropertyValue('catalogConfig');
        $taxClassProcessor = $this->getSubjectPropertyValue('taxClassProcessor');
        $productTypeModels = $this->getSubjectPropertyValue('_productTypeModels');
        $eventManager = $this->getSubjectPropertyValue('_eventManager');
        $errorAggregator = $subject->getErrorAggregator();
        $productLimit = null;
        $productsQty = null;
        while ($bunch = $dataSourceModel->getNextBunch()) {
            $newEntities = [];
            $entities = [];
            $attributes = [];
            $this->resetWebsites();
            $this->resetCategories();
            $tierPrices = [];
            $mediaGallery = [];
            $labelsForUpdate = [];
            $imagesForChangeVisibility = [];
            $uploadedImages = [];
            $existingImages = $this->invokeSubjectMethod('getExistingImages', $bunch);
            foreach ($bunch as $number => $data) {
                $categoryProcessor->clearFailedCategories();
                if (!$subject->validateRow($data, $number)) {
                    continue;
                }
                if ($errorAggregator->hasToBeTerminated()) {
                    $errorAggregator->addRowToSkip($number);
                    continue;
                }
                $storeId = $this->getStoreId($data);
                $urlKey = $this->invokeSubjectMethod('getUrlKey', $data);
                if (!empty($data[self::URL_KEY])) {
                    $data[self::URL_KEY] = $urlKey;
                } elseif ($this->invokeSubjectMethod('isNeedToChangeUrlKey', $data)) {
                    $bunch[$number][self::URL_KEY] = $data[self::URL_KEY] = $urlKey;
                }
                $sku = $data[self::COL_SKU];
                if (null === $sku) {
                    $errorAggregator->addRowToSkip($number);
                    continue;
                }
                if ($storeId) {
                    $data[self::COL_TYPE] = $skuProcessor->getNewSku($sku)['type_id'];
                    $data['attribute_set_id'] = $skuProcessor->getNewSku($sku)['attr_set_id'];
                    $data[self::COL_ATTR_SET] = $skuProcessor->getNewSku($sku)['attr_set_code'];
                }
                if ($this->invokeSubjectMethod('isSkuExist', $sku)) {
                    if (isset($data['attribute_set_code'])) {
                        $attributeSetId = $catalogConfig->getAttributeSetId($subject->getEntityTypeId(), $data['attribute_set_code']);
                        if (!$attributeSetId) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('Wrong attribute set code "%1", please correct it and try again.', $data['attribute_set_code'])
                            );
                        }
                    } else {
                        $attributeSetId = $skuProcessor->getNewSku($sku)['attr_set_id'];
                    }
                    $entities[] = [
                        'updated_at' => $this->getCurrentDateTime(),
                        'attribute_set_id' => $attributeSetId,
                        'entity_id' => $this->invokeSubjectMethod('getExistingSku', $sku)['entity_id']
                    ];
                } else {
                    if (!$productLimit || $productsQty < $productLimit) {
                        $newEntities[strtolower($sku)] = [
                            'attribute_set_id' => $skuProcessor->getNewSku($sku)['attr_set_id'],
                            'type_id' => $skuProcessor->getNewSku($sku)['type_id'],
                            'sku' => $sku,
                            'has_options' => isset($data['has_options']) ? $data['has_options'] : 0,
                            'created_at' => $this->getCurrentDateTime(),
                            'updated_at' => $this->getCurrentDateTime(),
                        ];
                        $productsQty++;
                    } else {
                        $sku = null;
                        $errorAggregator->addRowToSkip($number);
                        continue;
                    }
                }
                $this->setWebsites($sku, $data);
                $this->setCategories($number, $sku, $data);
                $this->setTierPrices($sku, $data, $tierPrices);
                if (!$subject->validateRow($data, $number)) {
                    continue;
                }
                $this->setMediaGallery($number, $sku, $data, $mediaGallery, $existingImages, $uploadedImages, $labelsForUpdate, $imagesForChangeVisibility);
                $productType = $data[self::COL_TYPE] ?? null;
                $productTypeModel = $productTypeModels[$productType];
                if (!empty($data['tax_class_name'])) {
                    $data['tax_class_id'] = $taxClassProcessor->upsertTaxClass($data['tax_class_name'], $productTypeModel);
                }
                if ($subject->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND || empty($data[self::COL_SKU])) {
                    $data = $productTypeModel->clearEmptyData($data);
                }
                $data = $productTypeModel->prepareAttributesWithDefaultValueForSave($data, !$this->invokeSubjectMethod('isSkuExist', $sku));
                $this->setAttributes($sku, $data, $attributes);
            }
            foreach ($bunch as $number => $data) {
                if ($errorAggregator->isRowInvalid($number)) {
                    unset($bunch[$number]);
                }
            }
            $subject->saveProductEntity($newEntities, $entities);
            $this->saveWebsites($this->getSubjectPropertyValue('websitesCache'));
            $this->invokeSubjectMethod('_saveProductCategories', $this->getSubjectPropertyValue('categoriesCache'));
            $this->saveTierPrices($tierPrices);
            $this->invokeSubjectMethod('_saveMediaGallery', $mediaGallery);
            $this->invokeSubjectMethod('_saveProductAttributes', $attributes);
            $this->invokeSubjectMethod('updateMediaGalleryVisibility', $imagesForChangeVisibility);
            $this->invokeSubjectMethod('updateMediaGalleryLabels', $labelsForUpdate);
            $eventManager->dispatch('catalog_product_import_bunch_save_after', ['adapter' => $subject, 'bunch' => $bunch]);
        }
        return $this;
    }

    /**
     * Save products data
     *
     * @return $this
     */
    protected function saveProductsData()
    {
        $subject = $this->getSubject();
        $productTypeModels = $this->getSubjectPropertyValue('_productTypeModels');
        $optionEntity = $subject->getOptionEntity();
        $this->saveProducts();
        foreach ($productTypeModels as $productTypeModel) {
            $productTypeModel->saveData();
        }
        $this->invokeSubjectMethod('_saveLinks');
        $this->invokeSubjectMethod('_saveStockItem');
        if ($this->getSubjectPropertyValue('_replaceFlag')) {
            $optionEntity->clearProductsSkuToId();
        }
        $optionEntity->importData();
        return $this;
    }

    /**
     * Replace products
     *
     * @return $this
     */
    protected function replaceProducts()
    {
        $subject = $this->getSubject();
        $skuProcessor = $this->getSubjectPropertyValue('skuProcessor');
        $subject->deleteProductsForReplacement();
        $this->setSubjectPropertyValue('_oldSku', $skuProcessor->reloadOldSkus()->getOldSkus());
        $this->setSubjectPropertyValue('_validatedRows', null);
        $subject->setParameters(array_merge($subject->getParameters(), ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND]));
        $this->saveProductsData();
        return $this;
    }

    /**
     * Import data
     *
     * @throws \Exception
     * @return bool
     */
    protected function importData()
    {
        $subject = $this->getSubject();
        $eventManager = $this->getSubjectPropertyValue('_eventManager');
        $this->setSubjectPropertyValue('_validatedRows', null);
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $subject->getBehavior()) {
            $this->invokeSubjectMethod('_deleteProducts');
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $subject->getBehavior()) {
            $this->setSubjectPropertyValue('_replaceFlag', true);
            $this->replaceProducts();
        } else {
            $this->saveProductsData();
        }
        $eventManager->dispatch('catalog_product_import_finish_before', ['adapter' => $subject]);
        return true;
    }

    /**
     * Around import data
     *
     * @param \Magento\CatalogImportExport\Model\Import\Product $subject
     * @param \Closure $proceed
     * @return bool
     */
    public function aroundImportData(
        \Magento\CatalogImportExport\Model\Import\Product $subject,
        \Closure $proceed
    )
    {
        $this->setSubject($subject);
        return $this->importData();
    }

    /**
     * Before validate data
     *
     * @param \Magento\CatalogImportExport\Model\Import\Product $subject
     * @return void
     */
    public function beforeValidateData(\Magento\CatalogImportExport\Model\Import\Product $subject)
    {
        $this->setSubject($subject);
        $fieldsMap = $this->getSubjectPropertyValue('_fieldsMap');
        foreach ($this->fieldsMap as $systemFieldName => $fieldName) {
            $fieldsMap[$systemFieldName] = $fieldName;
        }
        $this->setSubjectPropertyValue('_fieldsMap', $fieldsMap);
    }

    /**
     * Before is attribute particular
     *
     * @param \Magento\CatalogImportExport\Model\Import\Product $subject
     * @param string $attrCode
     * @return void
     */
    public function beforeIsAttributeParticular(
        \Magento\CatalogImportExport\Model\Import\Product $subject,
        $attrCode
    )
    {
        $this->setSubject($subject);
        $specialAttributes = $this->getSubjectPropertyValue('_specialAttributes');
        foreach ($specialAttributes as &$specialAttribute) {
            if (isset($this->specialAttributesMap[$specialAttribute])) {
                $specialAttribute = $this->specialAttributesMap[$specialAttribute];
            }
        }
        $this->setSubjectPropertyValue('_specialAttributes', $specialAttributes);
    }

}
