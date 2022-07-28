<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\ResourceModel\Event;

use Magento\Framework\App\ResourceConnection;
use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Model\Config\Source\EventStatus;
use Plumrocket\PrivateSale\Model\CurrentDateTime;
use Plumrocket\PrivateSale\Model\ResourceModel\Event;

/**
 * @since 5.0.0
 */
class GetEventIdByCategoryId
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Plumrocket\PrivateSale\Model\CurrentDateTime
     */
    private $currentDateTime;

    /**
     * @param \Magento\Framework\App\ResourceConnection     $resourceConnection
     * @param \Plumrocket\PrivateSale\Model\CurrentDateTime $currentDateTime
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CurrentDateTime $currentDateTime
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->currentDateTime = $currentDateTime;
    }

    /**
     * @param int $categoryId
     * @param int $status
     * @return int
     */
    public function execute(int $categoryId, int $status) : int
    {
        $connection = $this->resourceConnection->getConnection();
        $currentDate = $this->currentDateTime->getCurrentGmtDate();

        $select = $connection->select()
             ->from(
                 ['main_table' => $this->resourceConnection->getTableName(Event::MAIN_TABLE_NAME)],
                 [Event::ID_FIELD_NAME]
             )
             ->where(EventInterface::CATEGORY_EVENT . ' = ?', $categoryId)
             ->where('enable = 1');

        switch ($status) {
            case EventStatus::ACTIVE:
                $select->where('event_from < ?', $currentDate);
                $select->where('event_to > ?', $currentDate);
                break;
            case EventStatus::UPCOMING:
                $select->where('event_from > ?', $currentDate);
                break;
            case EventStatus::ENDED:
                $select->where('event_to < ?', $currentDate);
                break;
            default:
                throw new \InvalidArgumentException('Status ' . $status . ' dont known.');
        }

        return (int) $connection->fetchOne($select);
    }
}
