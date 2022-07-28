<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreCommon\Wrapper\Model\Rule\ResourceModel;

/**
 * Abstract rule resource wrapper factory
 */
class AbstractResourceFactory extends \Ecombricks\Common\Object\WrapperFactory
{

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Ecombricks\StoreCommon\Wrapper\Model\Rule\ResourceModel\AbstractResource::class
    )
    {
        parent::__construct($objectManager, $instanceName);
    }

}
