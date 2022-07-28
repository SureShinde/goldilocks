<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\Common\Setup;

/**
 * Abstract install data
 */
abstract class AbstractInstallData implements \Magento\Framework\Setup\InstallDataInterface
{

    /**
     * Operation
     *
     * @var \Ecombricks\Common\Setup\Operation\OperationInterface
     */
    protected $operation;

    /**
     * Constructor
     *
     * @param \Ecombricks\Common\Setup\Operation\OperationInterface $operation
     * @return void
     */
    public function __construct(
        \Ecombricks\Common\Setup\Operation\OperationInterface $operation
    )
    {
        $this->operation = $operation;
    }

    /**
     * Install
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     */
    public function install(\Magento\Framework\Setup\ModuleDataSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->operation->setSetup($setup);
        $this->operation->setModuleContext($context);
        $this->operation->execute();
        $setup->endSetup();
    }

}
