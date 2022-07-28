<?php

namespace Magenest\AdminActivity\Helper;

use Exception;
use Magenest\AdminActivity\Model\ActivityLogDetailFactory;
use Magenest\AdminActivity\Model\ActivityLogFactory;
use Magenest\AdminActivity\Model\Processor;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class LogVendor extends AbstractHelper
{
    const INGORE_MODULE = [
        'Magento_Customer',
        'Magento_Newsletter',
        'Magento_CatalogRule',
        'Magento_SalesRule',
        'Magento_Sales',
        'Magento_Email',
        'Magento_Cms',
        'Magento_Theme',
        'Magento_Widget',
        'Magento_Framework',
        'Magento_Store',
        'Magento_Catalog',
        'Magento_CatalogInventory',
        'Magento_Eav',
        'Magento_Review',
        'Magento_User',
        'Magento_Authorization',
        'Magento_Tax',
        'Magento_UrlRewrite',
        'Magento_Search',
        'Magento_Sitemap',
        'Magento_CheckoutAgreements',
        'Magento_Integration',
        'Magenest_AdminActivity'
    ];

    protected $processor;

    protected $activityLogDetailFactory;

    protected $activityLogFactory;

    public function __construct(
        ActivityLogDetailFactory $activityLogDetailFactory,
        ActivityLogFactory $activityLogFactory,
        Context $context,
        Processor $processor
    )
    {
        $this->activityLogFactory = $activityLogFactory;
        $this->activityLogDetailFactory = $activityLogDetailFactory;
        $this->processor = $processor;
        parent::__construct($context);
    }

    public function validateObject($object)
    {
        $moduleName = $this->getModuleNameByObject($object);
        if (in_array($moduleName, self::INGORE_MODULE)) {
            return false;
        }
        return true;
    }

    public function saveLogVendor($object, $type)
    {
        $isRevertible = 1;
        if ($type === 'save') {
            $isRevertible = 0;
        }
        $module = $this->getModuleNameByObject($object);
        $modelClass = get_class($object);
        $activity = $this->processor->_initLog();
        $activity->setActionType($type);
        $activity->setIsRevertable($isRevertible);
        $activity->setModule($module);
        try {
            $activity->save();
            $activityId = $activity->getId();
            $activityDetail = $this->activityLogDetailFactory->create();
            $activityDetail->setModelClass($modelClass);
            $activityDetail->setItemId($object->getId());
            $activityDetail->setStatus('success');
            $activityDetail->setData('activity_id', $activityId);
            $activityDetail->save();
            $logData = $object->getData();
            foreach ($logData as $key => $newValue) {
                if ($key === 'check_if_is_new' || $key === 'form_key') {
                    continue;
                }
                $orginValue = $object->getOrigData($key);
                if (gettype($orginValue) === 'array' || gettype($orginValue) === 'object'){
                    continue;
                }
                if (gettype($newValue) === 'array' || gettype($newValue) === 'object'){
                    continue;
                }
                $activityLog = $this->activityLogFactory->create();
                $activityLog->setActivityId($activityId);
                $activityLog->setFieldName($key);
                $activityLog->setOldValue($orginValue);
                $activityLog->setNewValue($newValue);
                $activityLog->save();
            }

        } catch (Exception $e) {
            $this->_logger->debug($e->getMessage());
        }
    }
    public function execute( $object)
    {
        if ($this->validateObject($object)) {
            if ($object->getCheckIfIsNew()) {
                $this->saveLogVendor($object,'save');
            } else {
                $this->saveLogVendor($object,'edit');
            }
        }
    }

    public function getModuleNameByObject($object)
    {
        $classObject = get_class($object);
        $bitsClassObject = explode('\\', $classObject);
        $company = current($bitsClassObject);
        $moduleName = $bitsClassObject[1];
        return $company . '_' . $moduleName;
    }

}
