<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\Common\Plugin\Data\Framework\Collection;

/**
 * Abstract database data collection plugin
 */
class AbstractDb extends \Ecombricks\Common\Plugin\Plugin
{

    /**
     * Before load
     *
     * @return $this
     */
    protected function beforeLoad()
    {
        $this->invokeSubjectMethod('_beforeLoad');
        return $this;
    }

    /**
     * After load
     *
     * @return $this
     */
    protected function afterLoad()
    {
        $this->invokeSubjectMethod('_afterLoad');
        return $this;
    }

    /**
     * Around load with filter
     *
     * @param \Magento\Framework\Data\Collection\AbstractDb $subject
     * @param \Closure $proceed
     * @param bool $printQuery
     * @param bool $logQuery
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    public function aroundLoadWithFilter(
        \Magento\Framework\Data\Collection\AbstractDb $subject,
        \Closure $proceed,
        $printQuery = false,
        $logQuery = false
    )
    {
        $this->setSubject($subject);
        $this->beforeLoad();
        $this->invokeSubjectMethod('_renderFilters');
        $this->invokeSubjectMethod('_renderOrders');
        $this->invokeSubjectMethod('_renderLimit');
        $subject->printLogQuery($printQuery, $logQuery);
        $data = $subject->getData();
        $subject->resetData();
        $idFieldName = $subject->getIdFieldName();
        if (is_array($data)) {
            foreach ($data as $row) {
                $item = $subject->getNewEmptyItem();
                if ($idFieldName) {
                    $item->setIdFieldName($idFieldName);
                }
                $item->addData($row);
                $this->invokeSubjectMethod('beforeAddLoadedItem', $item);
                $subject->addItem($item);
            }
        }
        $this->invokeSubjectMethod('_setIsLoaded');
        $this->afterLoad();
        return $subject;
    }

}
