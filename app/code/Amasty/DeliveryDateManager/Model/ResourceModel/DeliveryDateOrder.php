<?php
namespace Amasty\DeliveryDateManager\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DeliveryDateOrder extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_deliverydate_deliverydate_order';

    protected function _construct()
    {
        $this->connectionName = 'sales';
        $this->_init(self::MAIN_TABLE, 'deliverydate_id');
        //Use is object new method for save of object
        $this->_useIsObjectNew = true;
    }

    /**
     * Return how many delivery requested in a day
     *
     * @param int $counterId
     * @param int $lowestTimeLimit for reduce array size.
     *
     * @return array array(
     *     array(
     *         'day_counter' => '1',
     *         'date' => '1970-01-01'
     *     ),
     *     ...
     * )
     */
    public function getDayCounters(int $counterId, int $lowestTimeLimit = 1): array
    {
        $select = $this->getCounterSelect()
            ->columns(['day_counter' => 'COUNT(deliverydate_id)'])
            ->having('day_counter >= :limit');

        return $this->getConnection()->fetchAll($select, ['counter_id' => $counterId, 'limit' => $lowestTimeLimit]);
    }

    /**
     * Return how many delivery requested in a time range
     *
     * @param int $counterId
     * @param string $date filter date
     *
     * @return array array(
     *     array(
     *         'time_counter' => '1',
     *         'date' => '1970-01-01',
     *         'time_from' => '100',
     *         'time_to' => '110'
     *     ),
     *     ...
     * )
     */
    public function loadCountersForDate(int $counterId, string $date): array
    {
        $select = $this->getCounterSelect()
            ->columns(['time_from', 'time_to', 'time_counter' => 'COUNT(*)'])
            ->where('`date` = ?', $date)
            ->group(['time_from', 'time_to']);

        return $this->getConnection()->fetchAll($select, ['counter_id' => $counterId]);
    }

    /**
     * Return how many delivery requested in a time range
     *
     * @param int $counterId
     * @param array $excludeDays exclude exceeding the day limit. For reduce array size
     *
     * @return array array(
     *     array(
     *         'time_counter' => '1',
     *         'date' => '1970-01-01',
     *         'time_from' => '100',
     *         'time_to' => '110'
     *     ),
     *     ...
     * )
     */
    public function getTimeCounter(int $counterId, array $excludeDays = []): array
    {
        $select = $this->getCounterSelect()
            ->columns(['time_from', 'time_to', 'time_counter' => 'COUNT(*)'])
            ->group(['time_from', 'time_to']);

        if (!empty($excludeDays)) {
            $select->where('`date` NOT IN (?)', $excludeDays);
        }

        return $this->getConnection()->fetchAll($select, ['counter_id' => $counterId]);
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    private function getCounterSelect(): \Magento\Framework\DB\Select
    {
        return $this->getConnection()->select()
            ->from(
                [$this->getTable(self::MAIN_TABLE)],
                ['date']
            )
            ->where('counter_id = :counter_id')
            ->where('`date` >= CURRENT_DATE')
            ->group(['counter_id', 'date']);
    }
}
