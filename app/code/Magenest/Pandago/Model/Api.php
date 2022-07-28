<?php

namespace Magenest\Pandago\Model;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\ResponseFactory;
use Magenest\Pandago\Model\Carrier\Pandago;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory;
use Magento\Inventory\Model\Source;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\GetLatLngFromAddress;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterfaceFactory;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Psr\Log\LoggerInterface;
use Zend_Http_Client;

class Api
{
    const ORDER_PATH = 'sg/api/v1/orders';
    const TOKEN_PATH = 'oauth2/token';
    const HOST_MERCHANT = 'api/v1/merchant';
    const CREATE_ORDER = 'sales_orders/new';
    const REVERSAL = 'reversal';
    const SALES_ORDER = 'sales_orders';

    const UPLOAD_DIR = 'pandago';

    const STATUS_CODE_OK = 200;

    const OFFLINE_PAYMENTS = [
        Checkmo::PAYMENT_METHOD_CHECKMO_CODE,
        Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE
    ];

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ListsInterface
     */
    private $localeLists;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    private Filesystem $filesystem;

    private ZendClientFactory $httpClientFactory;

    private File $file;

    private AddressInterfaceFactory $addressInterfaceFactory;

    private CollectionFactory $sourceCollectionFactory;

    private GetLatLngFromAddress $getLatLngFromAddress;
    /**
     * @var TrackFactory
     */
    private TrackFactory $trackFactory;

    /**
     * Api constructor.
     *
     * @param ClientFactory $clientFactory
     * @param Url $url
     * @param ResponseFactory $responseFactory
     * @param ListsInterface $localeLists
     * @param Json $json
     * @param Config $config
     * @param Filesystem $filesystem
     * @param ZendClientFactory $httpClientFactory
     * @param File $file
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientFactory $clientFactory,
        Url $url,
        ResponseFactory $responseFactory,
        ListsInterface $localeLists,
        Json $json,
        Config $config,
        Filesystem $filesystem,
        ZendClientFactory $httpClientFactory,
        AddressInterfaceFactory $addressInterfaceFactory,
        File $file,
        CollectionFactory $sourceCollectionFactory,
        GetLatLngFromAddress $getLatLngFromAddress,
        TrackFactory $trackFactory,
        LoggerInterface $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->url = $url;
        $this->responseFactory = $responseFactory;
        $this->localeLists = $localeLists;
        $this->json = $json;
        $this->config = $config;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->httpClientFactory = $httpClientFactory;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->file = $file;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->getLatLngFromAddress = $getLatLngFromAddress;
        $this->trackFactory = $trackFactory;
    }

    /**
     * Get Token
     *
     * @param $storeId
     * @return string
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Http_Client_Exception
     */
    public function getToken($storeId): string
    {
        $clientId = $this->config->getClientId($storeId);
        $keyId = $this->config->getKeyId($storeId);
        $keyPem = $this->config->getSecretFilePem();
        $scope = $this->config->getScope($storeId);
        $aud = $this->config->getAud($storeId);
        $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath(self::UPLOAD_DIR);
        $privateKey = $this->file->fileGetContents($path . DIRECTORY_SEPARATOR . $keyPem);
        $uuid = $this->generateUuidV4();
        $payload = [
            "iss" => $clientId,
            "sub" => $clientId,
            "aud" => $aud,
            "jti" => $uuid,
            "exp" => time() + 3600
        ];
        $jwt = JWT::encode($payload, $privateKey, 'RS256', $keyId);
        $params = [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'client_assertion' => $jwt,
            'scope' => $scope,
        ];
        $response = $this->sendRequest(
            $this->config->getEndpointUrl($storeId) . DIRECTORY_SEPARATOR . self::TOKEN_PATH,
            Zend_Http_Client::POST,
            $params,
            $this->getTokenHeader(),
            $storeId
        );
        return $response['access_token'] ?? '';
    }

    /**
     * Generate random UUID
     *
     * @param null $data
     * @return string
     * @throws \Exception
     */
    private function generateUuidV4($data = null)
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for the time_low
            random_int(0, 0xffff), random_int(0, 0xffff),
            // 16 bits for the time_mid
            random_int(0, 0xffff),
            // 16 bits for the time_hi,
            random_int(0, 0x0fff) | 0x4000,
            // 8 bits and 16 bits for the clk_seq_hi_res,
            // 8 bits for the clk_seq_low,
            random_int(0, 0x3fff) | 0x8000,
            // 48 bits for the node
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }

    /**
     * Create an address from an order
     *
     * @param OrderInterface $order
     *
     * @return null|AddressInterface
     */
    private function getAddressFromOrder(OrderInterface $order): ?AddressInterface
    {
        /** @var Address $shippingAddress */
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress === null) {
            return null;
        }

        return $this->addressInterfaceFactory->create(
            [
                'country' => $shippingAddress->getCountryId(),
                'postcode' => $shippingAddress->getPostcode() ?? '',
                'street' => implode("\n", $shippingAddress->getStreet()),
                'region' => $shippingAddress->getRegion() ?? $shippingAddress->getRegionCode() ?? '',
                'city' => $shippingAddress->getCity() ?? ''
            ]
        );
    }

    private function prepareSourceData($sourceCode)
    {
        /** @var Source $source */
        $source = $this->sourceCollectionFactory->create()
            ->addFieldToFilter('source_code', $sourceCode)
            ->setPageSize(1)
            ->getFirstItem();
        if ($source) {
            $sender = [
                "name" => $source->getName(),
                "phone_number" => $source->getPhone(),
                "location" => [
                    "address" => $source->getStreet(),
                    "latitude" => $source->getLatitude(),
                    "longitude" => $source->getLongitude(),
                ],
            ];
        } else {
            $sender = [];
        }
        return $sender;
    }

    /**
     * Prepare customer data
     *
     * @param Order $order
     * @throws \Magento\InventoryDistanceBasedSourceSelectionApi\Exception\NoSuchLatLngFromAddressProviderException
     */
    private function prepareCustomerData($order)
    {
        $shipping = $order->getShippingAddress();
        $address = $this->getAddressFromOrder($order);
        $addressLatLng = $this->getLatLngFromAddress->execute($address);
        return [
            "name" => $shipping->getFirstname() . ' ' . $shipping->getLastname(),
            "phone_number" => '+84377567557',//$shipping->getTelephone(),//todo validate phone number
            "location" => [
                "address" => implode(PHP_EOL, $shipping->getStreet()),
                "latitude" => $addressLatLng->getLat(),
                "longitude" => $addressLatLng->getLng()
            ],
            "note" => $order->getCustomerNote()
        ];
    }

    /**
     * Create Pandago order
     *
     * @param $shipment
     * @return Order
     * @throws LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    public function createOrder($shipment)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();
        $storeId = $order->getStoreId();
        $sourceCode = $shipment->getExtensionAttributes()->getSourceCode();
        $sender = $this->prepareSourceData($sourceCode);
        $recipient = $this->prepareCustomerData($order);
        $params = [
            'client_order_id' => $order->getIncrementId(),
            'sender' => $sender,
            'recipient' => $recipient,
            'amount' => (float)$order->getGrandTotal(),
            'payment_method' => $this->preparePaymentMethod($order),
            'description' => $this->config->getDescription($storeId)
        ];
        $response = $this->request(
            self::ORDER_PATH,
            $params,
            $this->getHeader($this->getToken($storeId)),
            Zend_Http_Client::POST,
            $storeId
        );
        if (isset($response['order_id']) && $response['order_id']) {
            $data = $this->prepareTrackingData($order, $response);
            $track = $this->trackFactory->create()->addData($data);
            $shipment->addTrack($track);
        }
        return $shipment;
    }

    /**
     * Prepare order payment method
     *
     * @param $order
     * @return string
     */
    private function preparePaymentMethod($order)
    {
        $paymentMethod = $order->getPayment()->getMethod();
        if (in_array($paymentMethod, self::OFFLINE_PAYMENTS)) {
            return 'CASH_ON_DELIVERY';
        }
        return 'PAID';
    }

    /**
     * @param $url
     * @param string $method
     * @param array $params
     * @param array $headers
     * @param null $storeId
     * @return array|bool|float|int|mixed|string|null
     * @throws LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    protected function sendRequest($url, $method = 'GET', $params = [], $headers = [], $storeId = null)
    {
        $client = $this->httpClientFactory->create();
        $client->setHeaders($headers);
        $client->setUri($url);
        switch ($method) {
            case 'GET':
            case 'DELETE':
                $client->setParameterGet($params);
                break;
            case 'POST':
                $client->setParameterPost($params);
                break;
            default:
                throw new LocalizedException(
                    __('Required HTTP method is not supported.')
                );
        }
        $response = $client->request($method);
        if ($this->config->getEnableLogging($storeId)) {
            $this->logger->info('Request: ' . $url . ': ' . $this->json->serialize($params));
        }
        if ($response->isError()) {
            $status = $response->getStatus();
            if (($status == 400 || $status == 401)) {
                $decodedResponse = $this->processResponse($response);
                if (isset($decodedResponse['message'])) {
                    $message = $decodedResponse['message'];
                } else {
                    $message = __('Unspecified OAuth error occurred.');
                }
            } else {
                $message = __('HTTP error %d occurred while issuing request.', $status);
            }
            throw new LocalizedException(__($message));
        }
        $responseContent = $response->getBody();
        if ($this->config->getEnableLogging($storeId)) {
            $this->logger->info('Response: ' . $url . ': ' . $responseContent);
        }
        return $this->json->unserialize($responseContent);
    }

    /**
     * Make request to Pandago
     *
     * Use magento 2 best practices
     * https://devdocs.magento.com/guides/v2.4/ext-best-practices/tutorials/create-integration-with-api.html
     *
     * @param string $path
     * @param array $params
     * @param array $header
     * @param string $method
     * @return array
     * @throws LocalizedException
     */
    private function request($path, $params = [], $header = [], $method = 'GET', $storeId = null): array
    {
        /** @var Client $client */
        $client = $this->clientFactory->create(
            [
                'config' => [
                    'base_uri' => $this->config->getEndpointBaseUrl($storeId),
                    'headers' => $header
                ]
            ]
        );

        if ($method === Zend_Http_Client::GET) {
            $params = ['query' => $params];
        } else {
            $params = ['body' => json_encode($params)];
        }
        if ($this->config->getEnableLogging($storeId)) {
            $this->logger->info('Request: ' . $path . ': ' . $this->json->serialize($params));
        }
        try {
            $response = $client->request(
                $method,
                $path,
                $params
            );
        } catch (GuzzleException $exception) {
            $this->logger->error(__(
                'Have an error when try to call to Pandago API: %1 %2',
                $exception->getMessage(),
                $this->json->serialize(
                    [
                        'path' => $path,
                        'params' => $params,
                        'method' => $method
                    ]
                )
            ));

            $message = __($exception->getMessage());
            throw new LocalizedException(__($message));
        }

        if (!in_array($response->getStatusCode(), range(200, 206))) {
            $this->logger->error(__('Have an error when try to call to Pandago API: %1', $this->json->serialize([
                'path' => $path,
                'params' => $params,
                'method' => $method,
                'code' => $response->getStatusCode(),
                'reason' => $response->getReasonPhrase()
            ])));

            $message = __($response->getReasonPhrase());
            throw new LocalizedException(__($message));
        }
        $responseContent = $response->getBody()->getContents();
        if ($this->config->getEnableLogging($storeId)) {
            $this->logger->info('Response: ' . $path . ':' . $responseContent);
        }
        return  $this->json->unserialize($responseContent);
    }

    /**
     * @param \Zend_Http_Response $response
     * @return mixed
     */
    protected function processResponse($response)
    {
        $decodedResponse = $this->json->unserialize($response->getBody());
        if (empty($decodedResponse)) {
            $parsed_response = [];
            parse_str($response->getBody(), $parsed_response);
            $decodedResponse = $this->json->unserialize($this->json->serialize($parsed_response));
        }

        return $decodedResponse;
    }

    /**
     * Get request header
     *
     * @param $accessToken
     * @return array
     */
    private function getHeader($accessToken): array
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json'
        ];
    }

    private function getTokenHeader(): array
    {
        return [
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * @param OrderInterface $order
     * @param array $response
     * @return array
     */
    private function prepareTrackingData(OrderInterface $order, array $response)
    {
        return [
            'carrier_code' => Pandago::CODE,
            'title' => $order->getShippingDescription(),
            'number' => $response['order_id'],
            'track_number' => $response['order_id'],
            'qty' => $order->getTotalQtyOrdered(),
            'description' => $response['tracking_link'] ?? ''
        ];
    }
}
