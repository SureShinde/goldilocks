<?php

namespace Magenest\ServicesUi\Model\ResourceModel\Queue\Consumer;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\MysqlMq\Model\QueueManagement;
use Magento\MysqlMq\Model\ResourceModel\MessageStatus;
use Magento\MysqlMq\Model\Queue;

class Collection extends AbstractCollection
{
    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    protected function _construct()
    {
        $this->_init(
            Document::class,
            MessageStatus::class
        );
    }

    /**
     * @return $this|AbstractCollection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinTable();

        return $this;
    }

    /**
     * @return $this
     */
    private function joinTable()
    {
        $this->getSelect()
            ->joinInner(
                ['qm' => $this->getTable('queue_message')],
                'main_table.message_id = qm.id',
                [
                    QueueManagement::MESSAGE_TOPIC => 'qm.topic_name',
                    QueueManagement::MESSAGE_BODY  => 'qm.body'
                ]
            )->joinInner(
                ['q' => $this->getTable('queue')],
                'main_table.queue_id = q.id',
                [
                    Queue::KEY_NAME => 'q.name'
                ]
            );

        return $this;
    }
}
