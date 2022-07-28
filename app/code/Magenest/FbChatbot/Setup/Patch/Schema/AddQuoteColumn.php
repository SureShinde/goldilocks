<?php

namespace Magenest\FbChatbot\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class AddQuoteColumn implements SchemaPatchInterface
{
	private $moduleDataSetup;

	public function __construct(
		ModuleDataSetupInterface $moduleDataSetup
	) {
		$this->moduleDataSetup = $moduleDataSetup;
	}

	public static function getDependencies()
	{
		return [];
	}

	public function getAliases()
	{
		return [];
	}

	public function apply()
	{
		$this->moduleDataSetup->startSetup();

		$this->moduleDataSetup->getConnection()->addColumn(
			$this->moduleDataSetup->getTable('quote'),
			'using_bot',
			[
				'type'     => Table::TYPE_INTEGER,
				'length'   => 2,
				'nullable' => true,
				'default'  => "1",
				'comment'  => 'Name',
			]
		);

		$this->moduleDataSetup->endSetup();
	}
}
