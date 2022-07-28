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

namespace Plumrocket\PrivateSale\Test\Unit\Block\Model\Indexer\EntityToEvent;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Plumrocket\PrivateSale\Model\CurrentDateTime;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexDataSorter;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexRow;
use Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\Structure;

/**
 * @covers \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexDataSorter
 */
class IndexDataSorterTest extends TestCase
{
    /**
     * @var \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexDataSorter
     */
    private $indexDataSorter;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Plumrocket\PrivateSale\Model\CurrentDateTime
     */
    private $currentDateTime;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->indexDataSorter = $this->objectManager->getObject(IndexDataSorter::class);

        $this->currentDateTime = $this->getMockBuilder(CurrentDateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @covers       \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexDataSorter::sortByPriority
     * @covers       \Plumrocket\PrivateSale\Model\Indexer\EntityToEvent\IndexDataSorter::compareIndexRows
     * @dataProvider productIndexData
     * @param array $unsortedData
     * @param array $sortedEventsIds
     */
    public function testSortByPriority(array $unsortedData, array $sortedEventsIds)
    {
        $indexRows = [];
        foreach ($unsortedData as $rowData) {
            $indexRows[] = $this->objectManager->getObject(IndexRow::class, [
                'currentDateTime' => $this->currentDateTime,
                'rowData' => $rowData
            ]);
        }

        $sortedRows = $this->indexDataSorter->sortByPriority($indexRows);

        $eventIds = [];
        foreach ($sortedRows as $sortedRow) {
            $eventIds[] = $sortedRow->getEventId();
        }

        self::assertSame($sortedEventsIds, $eventIds);
    }

    /**
     * @return array
     */
    public function productIndexData(): array
    {
        return [
            [
                'unsortedData' => [
                    [Structure::EVENT_ID => 1, Structure::PRIORITY => 0],
                    [Structure::EVENT_ID => 2, Structure::PRIORITY => 0],
                ],
                'sortedEventsIds' => [2, 1],
            ],
            [
                'unsortedData' => [
                    [Structure::EVENT_ID => 1, Structure::PRIORITY => 0],
                    [Structure::EVENT_ID => 2, Structure::PRIORITY => 1],
                ],
                'sortedEventsIds' => [1, 2],
            ],
            [
                'unsortedData' => [
                    [Structure::EVENT_ID => 1, Structure::PRIORITY => 1],
                    [Structure::EVENT_ID => 2, Structure::PRIORITY => 0],
                ],
                'sortedEventsIds' => [2, 1],
            ],
            [
                'unsortedData' => [
                    [Structure::EVENT_ID => 1, Structure::PRIORITY => 2],
                    [Structure::EVENT_ID => 2, Structure::PRIORITY => 1],
                ],
                'sortedEventsIds' => [2, 1],
            ],
            [
                'unsortedData' => [
                    [Structure::EVENT_ID => 1, Structure::PRIORITY => 999],
                    [Structure::EVENT_ID => 2, Structure::PRIORITY => 0],
                    [Structure::EVENT_ID => 3, Structure::PRIORITY => 1],
                    [Structure::EVENT_ID => 5, Structure::PRIORITY => 1000],
                    [Structure::EVENT_ID => 4, Structure::PRIORITY => 1000],
                ],
                'sortedEventsIds' => [2, 3, 1, 5, 4],
            ],
        ];
    }
}
