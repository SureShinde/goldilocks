<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\Common\Setup\Operation;

/**
 * Setup operation interface
 */
interface OperationInterface
{

    /**
     * Set setup
     *
     * @param \Magento\Framework\Setup\SetupInterface $setup
     * @return \Ecombricks\Common\Setup\Operation\OperationInterface
     */
    public function setSetup(\Magento\Framework\Setup\SetupInterface $setup): \Ecombricks\Common\Setup\Operation\OperationInterface;

    /**
     * Get setup
     *
     * @return \Magento\Framework\Setup\SetupInterface
     */
    public function getSetup(): \Magento\Framework\Setup\SetupInterface;

    /**
     * Set module context
     *
     * @param \Magento\Framework\Setup\ModuleContextInterface $moduleContext
     * @return \Ecombricks\Common\Setup\Operation\OperationInterface
     */
    public function setModuleContext(\Magento\Framework\Setup\ModuleContextInterface $moduleContext): \Ecombricks\Common\Setup\Operation\OperationInterface;

    /**
     * Get module context
     *
     * @return \Magento\Framework\Setup\ModuleContextInterface
     */
    public function getModuleContext(): \Magento\Framework\Setup\ModuleContextInterface;

    /**
     * Execute
     *
     * @return \Ecombricks\Common\Setup\Operation\OperationInterface
     */
    public function execute(): \Ecombricks\Common\Setup\Operation\OperationInterface;

}
