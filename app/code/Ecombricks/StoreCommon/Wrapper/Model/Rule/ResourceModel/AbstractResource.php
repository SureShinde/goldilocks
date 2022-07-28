<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreCommon\Wrapper\Model\Rule\ResourceModel;

/**
 * Abstract rule resource wrapper
 */
class AbstractResource extends \Ecombricks\Common\Object\Wrapper
{

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Object\ReflectionFactory $objectReflectionFactory
     * @param \Magento\Rule\Model\ResourceModel\AbstractResource $object
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\ReflectionFactory $objectReflectionFactory,
        \Magento\Rule\Model\ResourceModel\AbstractResource $object
    )
    {
        parent::__construct($objectReflectionFactory);
        $this->setObject($object);
    }

    /**
     * Get customer group IDs
     *
     * @param int $ruleId
     * @return array
     */
    public function getCustomerGroupIds($ruleId)
    {
        return $this->getObject()->getCustomerGroupIds($ruleId);
    }

    /**
     * Get store IDs
     *
     * @param int $ruleId
     * @return array
     */
    public function getStoreIds($ruleId)
    {
        return $this->getObject()->getAssociatedEntityIds($ruleId, 'store');
    }

}
