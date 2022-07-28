<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Test\Unit\Model\DeliveryOrder\Edit\Validator\Rule;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule\OrderStatusRule;
use Amasty\DeliveryDateManager\Model\EditableConfigProvider;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * @see \Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule\OrderStatusRule
 */
class OrderStatusRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule\OrderStatusRule::validate
     * @dataProvider dataProvider
     * @param array $allowedStatuses
     * @param string $currentStatus
     * @param bool $expectedResult
     */
    public function testValidate(array $allowedStatuses, string $currentStatus, bool $expectedResult): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $orderMock = $this->createMock(OrderInterface::class);
        $orderMock->expects(self::once())->method('getStatus')->willReturn($currentStatus);

        $orderId = 3;

        $orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $orderRepositoryMock->expects(self::once())->method('get')->with($orderId)->willReturn($orderMock);

        $configProviderMock = $this->createMock(EditableConfigProvider::class);
        $configProviderMock
            ->expects(self::once())
            ->method('getOrderStatuses')
            ->with(null)
            ->willReturn($allowedStatuses);

        $model = $objectManager->getObject(
            OrderStatusRule::class,
            [
                'orderRepository' => $orderRepositoryMock,
                'configProvider' => $configProviderMock
            ]
        );

        $ddOrderMock = $this->createMock(DeliveryDateOrderInterface::class);
        $ddOrderMock->expects(self::once())->method('getOrderId')->willReturn($orderId);

        self::assertSame($expectedResult, $model->validate($ddOrderMock, null));
    }

    /**
     * @return array[]
     */
    public function dataProvider(): array
    {
        return [
            'allowed' => [
                'allowedStatuses' => ['one', 'two', 'three'],
                'currentStatus' => 'two',
                'expectedResult' => true,
            ],
            'disallowed' => [
                'allowedStatuses' => ['one', 'two', 'three'],
                'currentStatus' => 'four',
                'expectedResult' => false,
            ]
        ];
    }
}
