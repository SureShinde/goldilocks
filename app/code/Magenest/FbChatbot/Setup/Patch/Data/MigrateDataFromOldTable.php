<?php
namespace Magenest\FbChatbot\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateDataFromOldTable implements DataPatchInterface
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

        $this->migrateDataTableMenu();
        $this->migrateDataTableButton();
        $this->migrateDataTableMessage();

        $this->moduleDataSetup->endSetup();
    }

    /**
     * migrate date form old table to new table
     */
    public function migrateDataTableMessage() {
        $oldTable = $this->moduleDataSetup->getTable('magenest_message');
        $newTable = $this->moduleDataSetup->getTable('magenest_chatbot_message');
        if($this->checkExistTable($oldTable)) {
            $cols = $this->moduleDataSetup->getConnection()->describeTable($oldTable);
            if(isset($cols['message_type']))
                unset($cols['message_type']);
          $select = $this->moduleDataSetup->getConnection()->select()->from($oldTable, array_keys($cols));
          $insertQuery = $select->insertFromSelect($newTable, array_keys($cols));
          $this->moduleDataSetup->getConnection()->query($insertQuery);
        }
    }

    /**
     * migrate date form old table to new table
     */
    public function migrateDataTableMenu() {
        $oldTable = $this->moduleDataSetup->getTable('magenest_menu');
        $newTable = $this->moduleDataSetup->getTable('magenest_chatbot_menu');
        if($this->checkExistTable($oldTable)) {
            $cols = $this->moduleDataSetup->getConnection()->describeTable($oldTable);
            $select = $this->moduleDataSetup->getConnection()->select()->from($oldTable, array_keys($cols));
            $insertQuery = $select->insertFromSelect($newTable, array_keys($cols));
            $this->moduleDataSetup->getConnection()->query($insertQuery);
        }
    }

    /**
     * migrate date form old table to new table
     */
    public function migrateDataTableButton() {
        $oldTable = $this->moduleDataSetup->getTable('magenest_button');
        $newTable = $this->moduleDataSetup->getTable('magenest_chatbot_button');
        if($this->checkExistTable($oldTable)) {
            $cols = $this->moduleDataSetup->getConnection()->describeTable($oldTable);
            $select = $this->moduleDataSetup->getConnection()->select()->from($oldTable, array_keys($cols));
            $insertQuery = $select->insertFromSelect($newTable, array_keys($cols));
            $this->moduleDataSetup->getConnection()->query($insertQuery);
        }
    }

    /**
     * @param $table
     * @return bool
     */
    public function checkExistTable($table): bool
    {
       return $this->moduleDataSetup->getConnection()->isTableExists($table);
    }
}
