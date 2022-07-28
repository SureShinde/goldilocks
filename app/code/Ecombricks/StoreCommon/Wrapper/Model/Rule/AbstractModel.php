<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreCommon\Wrapper\Model\Rule;

/**
 * Abstract rule wrapper
 */
class AbstractModel extends \Ecombricks\Common\Object\Wrapper
{

    /**
     * Resource wrapper
     *
     * @var \Ecombricks\StoreCommon\Wrapper\Model\Rule\ResourceModel\AbstractResource
     */
    protected $resourceWrapper;

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Object\ReflectionFactory $objectReflectionFactory
     * @param \Ecombricks\StoreCommon\Wrapper\Model\Rule\ResourceModel\AbstractResource $resourceWrapper
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Object\ReflectionFactory $objectReflectionFactory,
        \Ecombricks\StoreCommon\Wrapper\Model\Rule\ResourceModel\AbstractResource $resourceWrapper
    )
    {
        parent::__construct($objectReflectionFactory);
        $this->resourceWrapper = $resourceWrapper;
    }

    /**
     * Get store IDs
     *
     * @return array
     */
    public function getStoreIds()
    {
        $object = $this->getObject();
        if (!$object->hasStoreIds()) {
            $object->setData('store_ids', (array) $this->resourceWrapper->getStoreIds($object->getId()));
        }
        return $object->getData('store_ids');
    }

}
