<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Test\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ValidationDeliveryQuoteApiTest extends WebapiAbstract
{
    public const VALIDATION_DELIVERY_QUOTE_API_PATH = '/V1/carts/mine/validate-delivery-date/';
    public const SERVICE_NAME = 'amastyDeliverydateDeliveryQuoteServiceV1';
    public const SERVICE_NAME_GUEST = 'amastyDeliverydateDeliveryGuestQuoteServiceV1';
    public const SERVICE_VERSION = 'V1';
    public const METHOD_NAME = 'Validate';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_order_limit.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel_configuration.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_date_schedule.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_schedule_delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval_date_schedule.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval_delivery_channel.php
     * @dataProvider dataForValidation
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     *
     * @param string $date
     * @param string $comment
     * @param $expectedResult
     * @param array|null $exception
     */
    public function testValidationDeliveryDate(string $date, string $comment, $expectedResult, array $exception = null)
    {
        $curentTest = 'default';
        if ($exception[$curentTest]) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($exception[$curentTest]);
        }

        $res = $this->validationDeliveryDate($date, $comment);
        self::assertSame($expectedResult[$curentTest], $res);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_order_limit.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel_configuration.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_date_schedule.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_schedule_delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval_date_schedule.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval_delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_order.php
     * @dataProvider dataForValidation
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     *
     *
     * @param string $date
     * @param string $comment
     * @param $expectedResult
     * @param array|null $exception
     */
    public function testValidationOrderLimit(string $date, string $comment, $expectedResult, array $exception = null)
    {
        $curentTest = 'orderLimitTest';
        if ($exception[$curentTest]) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($exception[$curentTest]);
        }

        $res = $this->validationDeliveryDate($date, $comment);
        self::assertSame($expectedResult[$curentTest], $res);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_order_limit.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel_configuration.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_date_schedule.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_schedule_delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval_date_schedule.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval_delivery_channel.php
     * @magentoConfigFixture default_store amdeliverydate/comment_field/enabled_comment 1
     * @magentoConfigFixture default_store amdeliverydate/comment_field/required 1
     * @magentoConfigFixture default_store amdeliverydate/comment_field/maxlength 4
     * @dataProvider dataForValidation
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     *
     * @param string $date
     * @param string $comment
     * @param $expectedResult
     * @param array|null $exception
     */
    public function testValidationDeliveryDateComment(
        string $date,
        string $comment,
        $expectedResult,
        array $exception = null
    ) {
        $curentTest = 'comment';
        if ($exception[$curentTest]) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($exception[$curentTest]);
        }

        $res = $this->validationDeliveryDate($date, $comment);
        self::assertSame($expectedResult[$curentTest], $res);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_order_limit.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel_configuration.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_date_schedule.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_schedule_delivery_channel.php
     * @magentoConfigFixture default_store amdeliverydate/time_field/enabled_time 1
     * @dataProvider dataForIsTime
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     *
     * @param string $date
     * @param string $comment
     * @param $expectedResult
     * @param string|null $exception
     * @param array $data
     */
    public function testValidationDeliveryDateIsTimeEnabled(
        string $date,
        string $comment,
        $expectedResult,
        string $exception = null,
        array $data
    ) {
        if ($exception) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($exception);
        }

        if (isset($data['timeInterval'])) {
            $this->createTimeInterval($data['timeInterval']['from'], $data['timeInterval']['to']);
        }
        $res = $this->validationDeliveryDate($date, $comment);
        self::assertSame($expectedResult, $res);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/quote_with_customer.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_order_limit.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel_configuration.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_date_schedule.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_schedule_delivery_channel.php
     * @magentoConfigFixture default_store amdeliverydate/time_field/enabled_time 0
     * @dataProvider dataForIsTime
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     *
     * @param string $date
     * @param string $comment
     * @param $expectedResult
     * @param array|null $exception
     * @param array $data
     */
    public function testValidationDeliveryDateIsTimeDisabled(
        string $date,
        string $comment,
        $expectedResult,
        array $exception = null,
        array $data
    ) {
        $curentTest = 'comment';
        if ($exception[$curentTest]) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($exception[$curentTest]);
        }

        $res = $this->validationDeliveryDate($date, $comment);
        self::assertSame($expectedResult[$curentTest], $res);
    }

    /**
     * @param string $date
     * @param string $comment
     * @return array|bool|float|int|string
     */
    public function validationDeliveryDate(string $date, string $comment)
    {
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test01', 'reserved_order_id');
        $quoteId = $quote->getId();
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setShippingMethod('flatrate_flatrate')
            ->setShippingDescription('Flat Rate - Fixed')
            ->save();
        $request = [
            'quoteId' => $quoteId,
            'quoteAddressId' => (int)$quote->getShippingAddress()->getId(),
            'date' => $date,
            'timeIntervalId' => 1,
            'comment' => $comment
        ];

        $this->createDeliveryQuote($quoteId, $quote->getShippingAddress()->getId(), $date);

        return $this->sendRequest($request);
    }

    /**
     * @return array[]
     */
    public function dataForValidation(): array
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime($today . "-1 days"));
        $tomorrow = date('Y-m-d', strtotime($today . "+1 days"));
        $dayAfterTomorrow = date('Y-m-d', strtotime($today . "+2 days"));

        return [
            'Less Today Validation' => [
                'date' => $yesterday,
                'comment' => '123',
                'expectedResult' =>
                    [
                        'default' => false,
                        'orderLimitTest' => false,
                        'comment' => false
                    ],
                'exception' =>
                    [
                        'default' => 'Delivery Date Validation Failed',
                        'orderLimitTest' => 'Delivery Date Validation Failed',
                        'comment' => 'Delivery Date Validation Failed'
                    ],
            ],
            'Not Today Validation' => [
                'date' => $today,
                'comment' => '123',
                'expectedResult' =>
                    [
                        'default' => false,
                        'orderLimitTest' => false,
                        'comment' => false
                    ],
                'exception' =>
                    [
                        'default' => 'Delivery Date Validation Failed',
                        'orderLimitTest' => 'Delivery Date Validation Failed',
                        'comment' => 'Delivery Date Validation Failed'
                    ],
            ],
            'Valid Data' => [
                'date' => $tomorrow,
                'comment' => '1234',
                'expectedResult' =>
                    [
                        'default' => true,
                        'orderLimitTest' => false,
                        'comment' => true
                    ],
                'exception' =>
                    [
                        'default' => null,
                        'orderLimitTest' => 'Delivery Date Validation Failed',
                        'comment' => null
                    ],
            ],
            'Not Valid Date Schedule' => [
                'date' => $dayAfterTomorrow,
                'comment' => '1234',
                'expectedResult' =>
                    [
                        'default' => false,
                        'orderLimitTest' => false,
                        'comment' => false
                    ],
                'exception' =>
                    [
                        'default' => 'Delivery Time Validation Failed',
                        'orderLimitTest' => 'Delivery Time Validation Failed',
                        'comment' => 'Delivery Time Validation Failed'
                    ],
            ],
            'Comment Required' => [
                'date' => $tomorrow,
                'comment' => '',
                'expectedResult' =>
                    [
                        'default' => true,
                        'orderLimitTest' => true,
                        'comment' => false
                    ],
                'exception' =>
                    [
                        'default' => null,
                        'orderLimitTest' => null,
                        'comment' => 'Delivery Comment is required.',
                    ],
            ],
            'Comment not valid length ' => [
                'date' => $tomorrow,
                'comment' => '12345',
                'expectedResult' =>
                    [
                        'default' => true,
                        'orderLimitTest' => true,
                        'comment' => false
                    ],
                'exception' =>
                    [
                        'default' => null,
                        'orderLimitTest' => null,
                        'comment' => 'Delivery Comment is required.',
                    ],

            ]
        ];
    }

    /**
     * @return array[]
     *
     */
    public function dataForIsTime(): array
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime($today . "-1 days"));
        $tomorrow = date('Y-m-d', strtotime($today . "+1 days"));
        $dayAfterTomorrow = date('Y-m-d', strtotime($today . "+2 days"));

        return [
            'Without Time Interval' => [
                'date' => $yesterday,
                'comment' => '123',
                'expectedResult' => false,
                'exception' => 'Delivery Time Validation Failed',
                'data' => []
            ],
            'With Invalid Time Interval' => [
                'date' => $yesterday,
                'comment' => '123',
                'expectedResult' => false,
                'exception' => 'Delivery Time Validation Failed',
                'data' =>
                    [
                        'timeInterval' =>
                            [
                                'from' => 10,
                                'to' => 100
                            ]
                    ]
            ],

            'With Valid Time Interval' => [
                'date' => $yesterday,
                'comment' => '123',
                'expectedResult' => true,
                'exception' => null,
                'data' =>
                    [
                        'timeInterval' =>
                            [
                                'from' => 100,
                                'to' => 200
                            ]
                    ]
            ],
        ];
    }

    /**
     * @param string $quoteId
     * @param string $quoteAddressId
     * @param string $date
     */
    public function createDeliveryQuote(string $quoteId, string $quoteAddressId, string $date)
    {
        $deliveryQuoteData = [
            [
                'delivery_quote_id' => 1,
                'quote_id' => $quoteId,
                'quote_address_id' => $quoteAddressId,
                'date' => $date,
                'comment' => 'test',
                'time_interval_id' => 1
            ]
        ];

        foreach ($deliveryQuoteData as $data) {
            $moduleDataSetup = $this->objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
            $moduleDataSetup->getConnection()->insertOnDuplicate(
                $moduleDataSetup->getTable(
                    \Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateQuote::MAIN_TABLE
                ),
                $data
            );
        }
    }

    /**
     * @param int $from
     * @param int $to
     */
    public function createTimeInterval(int $from, int $to)
    {
        $timeIntervalData = [
            [
                'interval_id' => 1,
                'limit_id' => 1,
                'from' => $from,
                'to' => $to,
                'position' => 1
            ],
        ];

        $timeIntervalScheduleData = [
            [
                'relation_id' => 1,
                'date_schedule_id' => 1,
                'time_interval_id' => 1
            ],

        ];

        $timeIntervalDeliveryChannelData = [
            [
                'relation_id' => 1,
                'delivery_channel_id' => 1,
                'time_interval_id' => 1
            ],
        ];

        foreach ($timeIntervalData as $data) {
            $moduleDataSetup = $this->objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
            $moduleDataSetup->getConnection()->insertOnDuplicate(
                $moduleDataSetup->getTable(
                    \Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateQuote::MAIN_TABLE
                ),
                $data
            );
        }

        foreach ($timeIntervalScheduleData as $data) {
            /** @var \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup */
            $moduleDataSetup = $this->objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
            $moduleDataSetup->getConnection()->insertOnDuplicate(
                $moduleDataSetup->getTable(
                    \Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalDateScheduleRelation::MAIN_TABLE
                ),
                $data
            );
        }

        foreach ($timeIntervalDeliveryChannelData as $data) {
            /** @var \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup */
            $moduleDataSetup = $this->objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
            $moduleDataSetup->getConnection()->insertOnDuplicate(
                $moduleDataSetup->getTable(
                    \Amasty\DeliveryDateManager\Model\ResourceModel\TimeIntervalChannelRelation::MAIN_TABLE
                ),
                $data
            );
        }
    }

    /**
     * @param $requestData
     * @return array|bool|float|int|string
     */
    public function sendRequest($requestData)
    {
        $customerTokenService = $this->objectManager->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine/validate-delivery-date/',
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . self::METHOD_NAME,
                'token' => $token
            ],
        ];

        return $this->_webApiCall($serviceInfo, $requestData);
    }
}
