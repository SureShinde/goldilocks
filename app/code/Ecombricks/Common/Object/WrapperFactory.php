<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\Common\Object;

/**
 * Object wrapper factory
 */
class WrapperFactory extends \Ecombricks\Common\Object\Factory
{
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Ecombricks\Common\Object\Wrapper::class
    )
    {
        parent::__construct($objectManager, $instanceName);
    }
    
}
