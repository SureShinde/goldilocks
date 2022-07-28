<?php
namespace Magenest\WebApiLog\Plugin;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use \Magento\Framework\Serialize\SerializerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Webapi\Controller\Rest;
use Magenest\WebApiLog\Model\ApiLogFactory;
use Magenest\WebApiLog\Model\ResourceModel\ApiLog as ApiLogResource;
use Magento\Framework\Webapi\ErrorProcessor;
use \Magento\Store\Model\ScopeInterface;

/**
 * Class RestApiLog
 * @package Magenest\WebApiLog\Plugin
 */
class RestApiLog
{
    const API_LOG_ENABLE = 'api_log/general_settings/enable';
    const API_LOG_TRACE = 'api_log/general_settings/log_trace';


    /**
     * @var TimezoneInterface
     */
    protected $_date;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var SerializerInterface
     */
    private $_serializer;

    /**
     * @var ApiLogFactory
     */
    protected $apiLogFactory;

    /**
     * @var ApiLogResource
     */
    protected $apiLogResource;

    /**
     * @var ErrorProcessor
     */
    protected $_errorProcessor;

    /**
     * RestApiLog constructor.
     * @param TimezoneInterface $date
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     * @param ApiLogFactory $apiLogFactory
     * @param ApiLogResource $apiLogResource
     * @param ErrorProcessor $errorProcessor
     */
    public function __construct(
        TimezoneInterface $date,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer,
        ApiLogFactory $apiLogFactory,
        ApiLogResource $apiLogResource,
        ErrorProcessor $errorProcessor
    )
    {
        $this->_date = $date;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_serializer = $serializer;
        $this->apiLogFactory = $apiLogFactory;
        $this->apiLogResource = $apiLogResource;
        $this->_errorProcessor = $errorProcessor;
    }

    /**
     * @param Rest $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function aroundDispatch(Rest $subject, callable $proceed, RequestInterface $request)
    {
        $time = $this->_date->date()->format('Y-m-d H:i:s');
        $this->logRequest($request, $time);
        $response = $proceed($request);
        $this->logResponse($response, $time);

        return $response;
    }

    /**
     * @param $request
     * @param $time
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function logRequest($request, $time) {
        try {
            // If Enabled Api Log
            if (!$this->checkLogEnabled()) {
                return;
            }
            // Prepare Data For Log
            $requestedLogData = [
                'storeId' => $this->_storeManager->getStore()->getId(),
                'path' => $request->getPathInfo(),
                'httpMethod' => $request->getMethod(),
                'requestData' => $request->getContent(),
                'clientIp' => $request->getClientIp()
            ];
            // Logging Data
            $apiLog = $this->apiLogFactory->create();
            $apiLog->setTime($time);
            $apiLog->setContent($this->_serializer->serialize($requestedLogData));

        } catch (\Exception $exception) {
            $apiLog->setException($exception->getMessage());
        }
        $this->apiLogResource->save($apiLog);
    }

    /**
     * @param $response
     * @param $time
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function logResponse($response, $time)
    {
        try {
            // If Enabled Api Log
            if (!$this->checkLogEnabled()) {
                return;
            }
            $responseLogData = [
                'responseStatus' => $response->getReasonPhrase(),
                'responseStatusCode' => $response->getStatusCode(),
                'responseBody' => $response->getBody()
            ];
            $apiLogResponse = $this->apiLogFactory->create();
            $apiLogResponse->setType(2);
            $apiLogResponse->setTime($time);
            $apiLogResponse->setContent($this->_serializer->serialize($responseLogData));
            $messageData = [];
            foreach ($response->getException() as $exception) {
                $maskedException = $this->_errorProcessor->maskException($exception);
                $data = [
                    'message' => $maskedException->getMessage(),
                ];
                if ($maskedException->getDetails()) {
                    $data['parameters'] = $maskedException->getDetails();
                }
                if ($this->_scopeConfig->getValue(self::API_LOG_TRACE, ScopeInterface::SCOPE_STORE)) {
                    $data['trace'] = $exception instanceof \Magento\Framework\Webapi\Exception
                        ? $exception->getStackTrace()
                        : $exception->getTraceAsString();
                }
                $messageData[] = $data;
            }
            if ($response->getException()) {
                $apiLogResponse->setException($this->_serializer->serialize($messageData));
            }
            $this->apiLogResource->save($apiLogResponse);
        }catch (\Exception $exception) {
            // do something
        }
    }

    /**
     * @return mixed
     */
    public function checkLogEnabled() {
        return $this->_scopeConfig->getValue(self::API_LOG_ENABLE, ScopeInterface::SCOPE_STORE);
    }
}
