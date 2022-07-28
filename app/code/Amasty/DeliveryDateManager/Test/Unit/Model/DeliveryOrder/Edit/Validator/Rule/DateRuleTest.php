<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Test\Unit\Model\DeliveryOrder\Edit\Validator\Rule;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule\DateRule;
use Amasty\DeliveryDateManager\Model\EditableConfigProvider;

/**
 * @see \Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule\DateRule
 */
class DateRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EditableConfigProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configProviderMock;

    /**
     * @var DeliveryDateOrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $ddOrderMock;

    /**
     * @var DateRule
     */
    private $model;

    public function setUp(): void
    {
        $this->configProviderMock = $this->createMock(EditableConfigProvider::class);
        $this->ddOrderMock = $this->createMock(DeliveryDateOrderInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            DateRule::class,
            [
                'configProvider' => $this->configProviderMock,
            ]
        );
    }

    /**
     * Test with no date in delivery date order object
     *
     * @covers DateRule::validate()
     * @return void
     */
    public function testValidateNoDate(): void
    {
        $this->configProviderMock
            ->expects(self::never())
            ->method('getPeriod')
            ->with(null)
            ->willReturn(0);
        $this->ddOrderMock->expects(self::once())->method('getDate')->willReturn('');

        self::assertTrue($this->model->validate($this->ddOrderMock, null));
    }

    /**
     * @covers       DateRule::validate()
     * @dataProvider dataProviderForValidate
     * @param int $period
     * @param string $date
     * @param bool $expectedResult
     * @return void
     */
    public function testValidate(int $period, string $date, bool $expectedResult): void
    {
        $this->configProviderMock
            ->expects(self::once())
            ->method('getPeriod')
            ->with(null)
            ->willReturn($period);
        $this->ddOrderMock->expects(self::once())->method('getDate')->willReturn($date);
        $actualResult = $this->model->validate($this->ddOrderMock, null);

        self::assertSame($expectedResult, $actualResult);
    }

    /**
     * @return array[]
     */
    public function dataProviderForValidate(): array
    {
        return [
            'period_finished' => [
                'period' => 3, // 3 days
                'date' => strftime('%Y-%m-%d %H:%M:%S', strtotime('+2 days')),
                'expectedResult' => false
            ],
            'period_active' => [
                'period' => 5, // 5 days
                'date' => strftime('%Y-%m-%d %H:%M:%S', strtotime('+10 days')),
                'expectedResult' => true
            ]
        ];
    }
}
