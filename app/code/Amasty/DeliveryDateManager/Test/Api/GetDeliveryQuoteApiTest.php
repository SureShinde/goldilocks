<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Test\Api;

use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryDateQuote;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GetDeliveryQuoteApiTest extends WebapiAbstract
{
    public const GET_DELIVERY_QUOTE_API_PATH = '/V1/carts/mine/get-delivery-date';
    public const SERVICE_NAME = 'amastyDeliverydateDeliveryQuoteServiceV1';
    public const SERVICE_NAME_GUEST = 'amastyDeliverydateDeliveryGuestQuoteServiceV1';
    public const SERVICE_VERSION = 'V1';
    public const METHOD_NAME = 'GetFromQuoteAddressId';

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
     * @magentoApiDataFixture Magento/CheckoutAddressSearch/_files/customer_with_addresses.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_order_limit.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel_configuration.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_date_schedule.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_schedule_delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval_date_schedule.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval_delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_quote.php
     *
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     */
    public function testGetDeliveryDateForAuthorize()
    {
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test01', 'reserved_order_id');
        $quoteId = $quote->getId();
        $request = [
            'quoteId' => $quoteId,
            'quoteAddressId' => $quote->getShippingAddress()->getId()
        ];

        $expected = 1;
        $res = $this->sendRequest($request, self::METHOD_NAME, self::SERVICE_NAME);
        $res = current($res);
        self::assertSame($expected, $res);
    }

    /**
     * @covers \Amasty\DeliveryDateManager\Api\DeliveryQuoteServiceInterface::getFromQuoteAddressId
     *
     * @magentoApiDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_order_limit.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel_configuration.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_date_schedule.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_schedule_delivery_channel.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval_date_schedule.php
     * @magentoApiDataFixture Amasty_DeliveryDateManager::Test/_files/delivery_time_interval_delivery_channel.php
     *
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     */
    public function testGetDeliveryDateForGuset()
    {
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
        $quote->load('guest_quote', 'reserved_order_id');
        $quoteId = $this->getQuoteMaskId((int)$quote->getId());
        $quote->getShippingAddress()->save();
        $this->createDeliveryQuote($quote->getId(), $quote->getShippingAddress()->getId());
        $request = [
            'cartId' => $quoteId,
            'quoteAddressId' => $quote->getShippingAddress()->getId()
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/guest-carts/" . $quoteId . "/quote-address-id/" .
                    $quote->getShippingAddress()->getId() . "/get-delivery-date",
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_GUEST,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME_GUEST . self::METHOD_NAME
            ],
        ];

        $expected = 1;
        $res = (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $request);
        $res = current($res);
        self::assertSame($expected, $res);
    }

    /**
     * @param array $requestData
     * @param string $method
     * @param string $serviceName
     * @param bool $isGuest
     * @return array|bool|float|int|string
     */
    private function sendRequest(array $requestData, string $method, string $serviceName, bool $isGuest = false)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::GET_DELIVERY_QUOTE_API_PATH . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => $serviceName,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => $serviceName . $method
            ],
        ];

        if (!$isGuest) {
            $customerTokenService = $this->objectManager->create(
                \Magento\Integration\Api\CustomerTokenServiceInterface::class
            );
            $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
            $serviceInfo['rest']['token'] = $token;
            $serviceInfo['soap']['token'] = $token;
        }

        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $requestData);

        return $response;
    }

    /**
     * get Masked id by Quote Id
     *
     * @param int $quoteId
     * @return string|null
     */
    public function getQuoteMaskId(int $quoteId): ?string
    {
        $quoteIdToMaskQuoteId = $this->objectManager->create(
            \Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface::class
        );
        $maskedId = $quoteIdToMaskQuoteId->execute((int)$quoteId);

        return $maskedId;
    }

    /**
     * @param $quoteId
     * @param $quoteAddressId
     */
    public function createDeliveryQuote(string $quoteId, string $quoteAddressId)
    {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime($today . "+1 days"));

        $deliveryQuoteData = [
            [
                'delivery_quote_id' => 1,
                'quote_id' => $quoteId,
                'quote_address_id' => $quoteAddressId,
                'date' => $tomorrow,
                'comment' => 'test',
                'time_interval_id' => 1
            ]
        ];

        foreach ($deliveryQuoteData as $data) {
            $moduleDataSetup = $this->objectManager->create(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
            $moduleDataSetup->getConnection()->insertOnDuplicate(
                $moduleDataSetup->getTable(DeliveryDateQuote::MAIN_TABLE),
                $data
            );
        }
    }
}
