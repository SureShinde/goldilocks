<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\DataHandler;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Model\ResourceModel\AbstractDb\DataHandlerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\AbstractModel;

class AbstractScopeHandler implements DataHandlerInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $scopeValueColumn;

    /**
     * @var string
     */
    private $scopeValuesDataKey;

    /**
     * @var int|mixed
     */
    private $allScopesValue;

    public function __construct(
        ResourceConnection $resourceConnection,
        string $tableName = '',
        string $scopeValueColumn = '',
        string $scopeValuesDataKey = '',
        $allScopesValue = ''
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->tableName = $tableName;
        $this->scopeValueColumn = $scopeValueColumn;
        $this->scopeValuesDataKey = $scopeValuesDataKey;
        $this->allScopesValue = $allScopesValue;
    }

    /**
     * @param AbstractModel|DeliveryChannelInterface $model
     * @return void
     */
    public function afterSave(AbstractModel $model): void
    {
        $channelId = (int)$model->getChannelId();
        $scopeValues = (array)$model->getData($this->scopeValuesDataKey);

        if (in_array($this->allScopesValue, $scopeValues) || empty($scopeValues)) {
            // Delete all scope values == selected ALL
            $this->deleteScopeValues($channelId);
            return;
        }

        $existed = $this->getScopeValues($channelId);
        $toInsert = array_diff($scopeValues, $existed);
        $toDelete = array_diff($existed, $scopeValues);

        if (!empty($toDelete)) {
            $this->deleteScopeValues($channelId, $toDelete);
        }

        if (!empty($toInsert)) {
            $this->saveScopeValues($channelId, $toInsert);
        }
    }

    /**
     * @param AbstractModel|DeliveryChannelInterface $model
     * @return void
     */
    public function afterLoad(AbstractModel $model): void
    {
        $channelId = (int)$model->getChannelId();

        if ($channelId) {
            $model->setData($this->scopeValuesDataKey, $this->getScopeValues($channelId));
        }
    }

    /**
     * @param int $channelId
     * @param array $cols
     * @return array
     */
    private function getScopeValues(int $channelId, array $cols = []): array
    {
        $cols = empty($cols) ? [$this->scopeValueColumn] : $cols;
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName($this->tableName), $cols)
            ->where(DeliveryChannelInterface::CHANNEL_ID . ' = ?', $channelId);

        return empty($cols)
            ? (array)$connection->fetchAll($select)
            : (array)$connection->fetchCol($select);
    }

    /**
     * @param int $channelId
     * @param array $scopeValues
     * @return void
     */
    private function deleteScopeValues(int $channelId, array $scopeValues = []): void
    {
        $connection = $this->resourceConnection->getConnection();
        $where = [DeliveryChannelInterface::CHANNEL_ID . ' = ?' => $channelId];

        if (!empty($scopeValues)) {
            $where[$this->scopeValueColumn . ' IN(?)'] = $scopeValues;
        }
        $connection->delete(
            $this->resourceConnection->getTableName($this->tableName),
            $where
        );
    }

    /**
     * @param int $channelId
     * @param array $scopeValues
     * @return void
     */
    private function saveScopeValues(int $channelId, array $scopeValues): void
    {
        $connection = $this->resourceConnection->getConnection();
        $insertArray = [];
        foreach ($scopeValues as $scopeValue) {
            $insertArray[] = [
                $channelId,
                $scopeValue
            ];
        }
        $connection->insertArray(
            $this->resourceConnection->getTableName($this->tableName),
            [
                DeliveryChannelInterface::CHANNEL_ID,
                $this->scopeValueColumn
            ],
            $insertArray
        );
    }
}
