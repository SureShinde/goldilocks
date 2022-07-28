<?php

namespace Magenest\AbandonedCart\Model;

use mysql_xdevapi\Exception;

class ABTestCampaign extends \Magento\Framework\Model\AbstractModel
{
    const CAMPAIGN_ACTIVE = 1;
    const CAMPAIGN_INACTIVE = 0;

    public function _construct()
    {
        $this->_init('Magenest\AbandonedCart\Model\ResourceModel\ABTestCampaign');
    }

    /**
     * @param $dataArr
     * @throws \Exception
     */
    public function deleteMultiple($dataArr)
    {
        $size = count($dataArr);
        if (!is_array($dataArr) && $size == 0) {
            return;
        }
        $collectionIds = implode(', ', $dataArr);
        try {
            $getResource = \Magento\Framework\App\ObjectManager::getInstance()->get($this->getResourceName());
            $getResource->getConnection()->delete(
                $getResource->getMainTable(),
                "{$this->getIdFieldName()} in ({$collectionIds})"
            );
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
