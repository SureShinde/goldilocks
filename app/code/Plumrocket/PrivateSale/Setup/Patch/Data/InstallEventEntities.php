<?php
/**
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Setup\Patch\Data;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Plumrocket\PrivateSale\Setup\Patch\Data\Entities\GetEventEntities;

/**
 * @since 5.1.0
 */
class InstallEventEntities implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Plumrocket\PrivateSale\Setup\Patch\Data\Entities\GetEventEntities
     */
    private $eventEntities;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface                  $moduleDataSetup
     * @param \Magento\Eav\Setup\EavSetupFactory                                 $eavSetupFactory
     * @param \Magento\Eav\Model\Config                                          $eavConfig
     * @param \Plumrocket\PrivateSale\Setup\Patch\Data\Entities\GetEventEntities $eventEntities
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        EavConfig $eavConfig,
        GetEventEntities $eventEntities
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->eventEntities = $eventEntities;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->installEntities($this->eventEntities->execute());
        $this->eavConfig->clear();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Remove entities attributes and types
     *
     * @param \Magento\Eav\Setup\EavSetup $eavSetup
     * @param array $entities
     * @return void
     */
    private function removeEntities(EavSetup $eavSetup, array $entities): void
    {
        foreach ($entities as $entityType => $entityData) {
            // remove attributes
            if (is_array($entityData['attributes']) && !empty($entityData['attributes'])) {
                foreach ($entityData['attributes'] as $attrCode => $attr) {
                    $eavSetup->removeAttribute($entityType, $attrCode);
                }
            }
            //remove Eav Entity Type
            $eavSetup->removeEntityType($entityType);
        }
    }

    /**
     * @inheritdoc
     */
    public function revert(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $this->removeEntities($eavSetup, $this->eventEntities->execute());

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getVersion(): string
    {
        return '5.0.0';
    }
}
