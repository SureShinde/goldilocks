<?php
/**
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateEntityToEventIndexTable implements SchemaPatchInterface, PatchVersionInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @var \Plumrocket\PrivateSale\Setup\Operation\CreateEntityToEventIndexTable
     */
    private $createEntityToEventIndexTable;

    /**
     * @param SchemaSetupInterface                                                  $schemaSetup
     * @param \Plumrocket\PrivateSale\Setup\Operation\CreateEntityToEventIndexTable $createEntityToEventIndexTable
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup,
        \Plumrocket\PrivateSale\Setup\Operation\CreateEntityToEventIndexTable $createEntityToEventIndexTable
    ) {
        $this->schemaSetup = $schemaSetup;
        $this->createEntityToEventIndexTable = $createEntityToEventIndexTable;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();
        $this->createEntityToEventIndexTable->execute($this->schemaSetup);
        $this->schemaSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function revert(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [
        ];
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
