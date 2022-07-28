<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Test\Unit\Model\DeliveryOrder\Edit\Validator\Rule;

use Amasty\DeliveryDateManager\Api\Data\DeliveryDateOrderInterface;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule\CombineRule;
use Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule\RuleInterface;

/**
 * @see \Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule\CombineRule
 */
class CombineRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Amasty\DeliveryDateManager\Model\DeliveryOrder\Edit\Validator\Rule\CombineRule::validate
     * @dataProvider dataProvider
     * @param array $rulesResults
     * @param string $mode
     * @param bool $expectedResult
     */
    public function testValidate(array $rulesResults, string $mode, bool $expectedResult): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var DeliveryDateOrderInterface $ddOrderMock */
        $ddOrderMock = $this->createMock(DeliveryDateOrderInterface::class);
        $rules = [];
        foreach ($rulesResults as $ruleResult) {
            $rule = $this->createMock(RuleInterface::class);
            $rule->expects($this->any())->method('validate')->willReturn($ruleResult);
            $rules[] = $rule;
        }

        $model = $objectManager->getObject(
            CombineRule::class,
            [
                'rules' => $rules,
                'mode' => $mode
            ]
        );

        self::assertSame($expectedResult, $model->validate($ddOrderMock, null));
    }

    /**
     * @return array[]
     */
    public function dataProvider(): array
    {
        return [
            'all_all_true' => [
                'rulesResults' => [true, true],
                'mode' => CombineRule::ALL,
                'expectedResult' => true
            ],

            'all_all_false' => [
                'rulesResults' => [false, false],
                'mode' => CombineRule::ALL,
                'expectedResult' => false
            ],

            'all_first_false' => [
                'rulesResults' => [false, true],
                'mode' => CombineRule::ALL,
                'expectedResult' => false
            ],

            'all_second_false' => [
                'rulesResults' => [true, false],
                'mode' => CombineRule::ALL,
                'expectedResult' => false
            ],
            'all_many_true' => [
                'rulesResults' => [true, true, true, true],
                'mode' => CombineRule::ALL,
                'expectedResult' => true
            ],

            'all_many_false' => [
                'rulesResults' => [false, false, false, false],
                'mode' => CombineRule::ALL,
                'expectedResult' => false
            ],

            'all_many_one_false' => [
                'rulesResults' => [true, true, false, true],
                'mode' => CombineRule::ALL,
                'expectedResult' => false
            ],

            // one
            'one_all_true' => [
                'rulesResults' => [true, true, true, true],
                'mode' => CombineRule::ONE,
                'expectedResult' => true
            ],

            'one_all_false' => [
                'rulesResults' => [false, false, false, false],
                'mode' => CombineRule::ONE,
                'expectedResult' => false
            ],

            'one_first_true' => [
                'rulesResults' => [true, false, false, false],
                'mode' => CombineRule::ONE,
                'expectedResult' => true
            ],
            
            'one_last_true' => [
                'rulesResults' => [false, false, false, true],
                'mode' => CombineRule::ONE,
                'expectedResult' => true
            ],

            'one_middle_true' => [
                'rulesResults' => [false, false, true, false],
                'mode' => CombineRule::ONE,
                'expectedResult' => true
            ],
        ];
    }
}
