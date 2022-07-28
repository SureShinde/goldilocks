<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Test\Unit\Model;

use Amasty\DeliveryDateManager\Model\ConfigProvider;
use Amasty\DeliveryDateManager\Model\ConfigDisplay;
use Amasty\DeliveryDateManager\Model\Config\Source\Show;
use Amasty\DeliveryDateManager\Model\Config\Source\IncludeInto;

/**
 * @see \Amasty\DeliveryDateManager\Model\ConfigDisplay
 */
class ConfigDisplayTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configProviderMock;

    /**
     * @var ConfigDisplay
     */
    private $model;

    public function setUp(): void
    {
        $this->configProviderMock = $this->createMock(ConfigProvider::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            ConfigDisplay::class,
            [
                'configProvider' => $this->configProviderMock,
            ]
        );
    }

    /**
     * @dataProvider dataProviderForDateDisplayOn
     *
     * @param string $place
     * @param int $store
     * @param array $willReturnData
     * @param bool $expectedResult
     */
    public function testIsDateDisplayOn(string $place, int $store, array $willReturnData, bool $expectedResult)
    {
        $this->configProviderMock
            ->expects(self::once())
            ->method('getDateDisplayOn')
            ->with($store)
            ->willReturn($willReturnData);

        $actualResult = $this->model->isDateDisplayOn($place, $store);
        self::assertSame($expectedResult, $actualResult);
    }

    /**
     * @dataProvider dataProviderForDateDisplayOn
     *
     * @param string $place
     * @param int $store
     * @param array $willReturnData
     * @param bool $expectedResult
     */
    public function testIsTimeDisplayOn(string $place, int $store, array $willReturnData, bool $expectedResult)
    {
        $this->configProviderMock
            ->expects(self::once())
            ->method('getTimeDisplayOn')
            ->with($store)
            ->willReturn($willReturnData);

        $actualResult = $this->model->isTimeDisplayOn($place, $store);
        self::assertSame($expectedResult, $actualResult);
    }

    /**
     * @dataProvider dataProviderForDateDisplayOn
     *
     * @param string $place
     * @param int $store
     * @param array $willReturnData
     * @param bool $expectedResult
     */
    public function testIsCommentDisplayOn(string $place, int $store, array $willReturnData, bool $expectedResult)
    {
        $this->configProviderMock
            ->expects(self::once())
            ->method('getCommentDisplayOn')
            ->with($store)
            ->willReturn($willReturnData);

        $actualResult = $this->model->isCommentDisplayOn($place, $store);
        self::assertSame($expectedResult, $actualResult);
    }

    /**
     * @return array[]
     */
    public function dataProviderForDateDisplayOn(): array
    {
        return [
            'haveDisplayOn' => [
                'place' => Show::ORDER_CREATE,
                'store' => 1,
                'willReturnData' => $this->willReturnData('show'),
                'expectedResult' => true
            ],
            'dontHaveDisplayOn' => [
                'place' => IncludeInto::INVOICE_PDF,
                'store' => 1,
                'willReturnData' => $this->willReturnData('show'),
                'expectedResult' => false
            ],
            'haveInclude' => [
                'place' => Show::ORDER_CREATE,
                'store' => 1,
                'willReturnData' => $this->willReturnData('include'),
                'expectedResult' => false
            ],
            'dontHaveInclude' => [
                'place' => IncludeInto::INVOICE_PDF,
                'store' => 1,
                'willReturnData' => $this->willReturnData('include'),
                'expectedResult' => true
            ],
            'mixedDisplayData1' => [
                'place' => IncludeInto::ORDER_EMAIL,
                'store' => 1,
                'willReturnData' => $this->willReturnData('mixed'),
                'expectedResult' => true
            ],
            'mixedDisplayData2' => [
                'place' => Show::ORDER_VIEW,
                'store' => 1,
                'willReturnData' => $this->willReturnData('mixed'),
                'expectedResult' => true
            ],
            'emptyReturned' => [
                'place' => Show::ORDER_VIEW,
                'store' => 1,
                'willReturnData' => $this->willReturnData('nothing'),
                'expectedResult' => false
            ]
        ];
    }

    private function willReturnData($type)
    {
        $willReturned = [];
        switch ($type) {
            case 'show':
                $willReturned = [
                    Show::ORDER_VIEW,
                    Show::ORDER_CREATE,
                    Show::INVOICE_VIEW,
                    Show::SHIPMENT_VIEW,
                    Show::ORDER_INFO,
                ];
                break;
            case 'include':
                $willReturned = [
                    IncludeInto::ORDER_PRINT,
                    IncludeInto::ORDER_EMAIL,
                    IncludeInto::INVOICE_EMAIL,
                    IncludeInto::SHIPMENT_EMAIL,
                    IncludeInto::INVOICE_PDF,
                    IncludeInto::SHIPMENT_PDF
                ];
                break;
            case 'mixed':
                $willReturned = [
                    IncludeInto::ORDER_PRINT,
                    IncludeInto::ORDER_EMAIL,
                    IncludeInto::INVOICE_EMAIL,
                    IncludeInto::SHIPMENT_EMAIL,
                    IncludeInto::INVOICE_PDF,
                    IncludeInto::SHIPMENT_PDF,
                    Show::ORDER_VIEW,
                    Show::ORDER_CREATE,
                    Show::INVOICE_VIEW,
                    Show::SHIPMENT_VIEW,
                    Show::ORDER_INFO,
                ];
                break;
            case 'nothing':
                $willReturned = [];
                break;
        }

        return $willReturned;
    }
}
