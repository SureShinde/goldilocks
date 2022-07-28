<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\Common\Object;

/**
 * Object reflection factory
 */
class ReflectionFactory extends \Ecombricks\Common\Object\Factory
{
    
    /**
     * Instances
     * 
     * @var array
     */
    protected $instances = [];
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Ecombricks\Common\Object\Reflection::class
    )
    {
        parent::__construct($objectManager, $instanceName);
    }
    
    /**
     * Create
     *
     * @param array $data
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(array $data = [])
    {
        if (empty($data['class'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Class argument is not found for %1 factory.', get_class($this)));
        }
        $class = $data['class'];
        if (array_key_exists($class, $this->instances)) {
            return $this->instances[$class];
        }
        return $this->instances[$class] = parent::create($data);
    }
    
}
