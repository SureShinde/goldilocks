<?php

namespace Magenest\ServicesUi\Model\Queue\Options;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\OptionSourceInterface;

class QueueName implements OptionSourceInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * QueueName constructor.
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('queue');
        $sql = $connection->select()->from($tableName, ['name']);
        $result = $connection->fetchAll($sql);
        $options = [];
        if (!empty($result)) {
            foreach ($result as $item) {
                $options[] = ['label' => $item['name'], 'value' => $item['name']];
            }
        }
        return $options;
    }
}
