<?php

namespace Magenest\GoogleTagManager\Test\Unit\Helper;

use Magenest\GoogleTagManager\Helper\Checkoutsteps as Model;

use function Magenest\TestingTools\Functions\get;

class CheckoutstepsTest extends \Magenest\TestingTools\Test\Unit\TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Only whole numbers greater than 0 are allowed.
     */
    public function testGetValidStepNumberSholdThrowExceptionIfStepNumberIsInvalid()
    {
        /** @var Model $model */
        $model = get();

        $model->getValidStepNumber('#1');
    }

    /**
     * dataProvider for serialized values
     */
    public function serializedValuesProvider()
    {
        return [
            [
                'a:4:{s:4:"cart";i:1;s:8:"shipping";i:2;s:7:"payment";i:3;s:7:"success";i:4;}',
                ['cart' => 1, 'shipping' => 2, 'payment' => 3, 'success' => 4]
            ],
            [
                'a:0:{}',
                []
            ]
        ];
    }

    /**
     * @param $value
     * @param $expected
     *
     * @dataProvider serializedValuesProvider
     */
    public function testUnserializeValueShouldReturnArray($value, $expected)
    {
        /** @var Model $model */
        $model = get();

        $result = $model->unserializeValue($value);

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider nonStringValueProvider
     */
    public function testUnserializeValueShouldReturnEmptyArrayWhenPassedInValueNotString($value)
    {
        /** @var Model $model */
        $model = get();

        $result = $model->unserializeValue($value);

        $this->assertEquals([], $result);
    }

    /**
     * @param $value
     * @param $expected
     *
     * @dataProvider serializedValuesProvider
     */
    public function testSerializeValueShouldSerializedString($expected, $value)
    {
        /** @var Model $model */
        $model = get();

        $result = $model->serializeValue($value);

        $this->assertEquals($expected, $result);
    }

    /**
     * dataProvider encoded arrays
     */
    public function encodedArraysProvider()
    {
        return [
            [
                [
                    '_bc16ad3e80e269e316e0e8b2e82d1adb' => ['checkout_page' => 'shipping', 'step_number' => '1'],
                    '_56d2817b6b4041b920d0b772376f4832' => ['checkout_page' => 'payment', 'step_number' => '2'],
                    '_1459367050469_469' => ['checkout_page' => 'success', 'step_number' => '3'],
                    '__empty' => ''
                ],
                true
            ],
            [
                [
                    'shipping' => '1',
                    'payment' => '2',
                    'success' => '3'
                ],
                false
            ],
        ];
    }

    /**
     * @param $array
     * @param $expected
     *
     * @dataProvider encodedArraysProvider
     */
    public function testIsEncodedArrayFieldValueShouldReturnTrueOrFalse($array, $expected)
    {
        /** @var Model $model */
        $model = get();

        $result = $model->isEncodedArrayFieldValue($array);

        $this->assertEquals($expected, $result);
    }

    public function testDecodeArrayFieldValueShouldReturnNormalArray()
    {
        $array = [
            '_bc16ad3e80e269e316e0e8b2e82d1adb' => [
                'checkout_page' => 'shipping',
                'step_number' => '1'
            ],
            '_56d2817b6b4041b920d0b772376f4832' => [
                'checkout_page' => 'payment',
                'step_number' => '2'
            ],
            '_1459367050469_469' => [
                'checkout_page' => 'success',
                'step_number' => '3'
            ],
            '__empty' => ''
        ];

        /** @var Model $model */
        $model = get();

        $result = $model->decodeArrayFieldValue($array);

        $expected = ['shipping' => 1, 'payment' => 2, 'success' => 3];

        $this->assertEquals($expected, $result);
    }

    /**
     * dataProvider for order revenue with or without shipping
     */
    public function uniqueArraysProvider()
    {
        return [
            [
                ['shipping' => 1, 'payment' => 2, 'success' => 3],
                true
            ],
            [
                ['shipping' => 1, 'payment' => 1, 'success' => 2],
                false
            ]
        ];
    }

    /**
     * @param $array
     * @param $expected
     *
     * @dataProvider uniqueArraysProvider
     */
    public function testHasUniqueValuesShouldReturnTrueOrFalse($array, $expected)
    {
        /** @var Model $model */
        $model = get();

        $result = $model->hasUniqueValues($array);

        $this->assertEquals($expected, $result);
    }
}
