<?php
namespace Magenest\WebApiLog\Plugin;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use \Magento\Framework\Serialize\SerializerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use Magento\GraphQl\Controller\GraphQl;
use Magenest\WebApiLog\Model\ApiLogFactory;
use Magenest\WebApiLog\Model\ResourceModel\ApiLog as ApiLogResource;
use Magento\Framework\Webapi\ErrorProcessor;
use \Magento\Store\Model\ScopeInterface;

/**
 * Class GraphApiLog
 * @package Magenest\WebApiLog\Plugin
 */
class GraphApiLog
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
     * RestApiLog constructor.
     * @param TimezoneInterface $date
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     * @param ApiLogFactory $apiLogFactory
     * @param ApiLogResource $apiLogResource
     */
    public function __construct(
        TimezoneInterface $date,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer,
        ApiLogFactory $apiLogFactory,
        ApiLogResource $apiLogResource
    )
    {
        $this->_date = $date;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_serializer = $serializer;
        $this->apiLogFactory = $apiLogFactory;
        $this->apiLogResource = $apiLogResource;
    }

    /**
     * @param GraphQl $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function aroundDispatch(GraphQl $subject, callable $proceed, RequestInterface $request)
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
            if($response->getContent()) {
                $content = $this->_serializer->unserialize($response->getContent());
                if(isset($content['errors'])) {
                    foreach ($content['errors'] as $error) {
                        $data = [
                            'message' => isset($error['message']) ? $error['message'] : 'unknowns error',
                            'trace'   => isset($error['locations']) ? $error['locations'] : ''
                        ];
                        if (!$this->_scopeConfig->getValue(self::API_LOG_TRACE, ScopeInterface::SCOPE_STORE)) {
                            unset($data['trace']);
                        }
                        $messageData[] = $data;
                    }
                    $apiLogResponse->setException($this->_serializer->serialize($messageData));
                }
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
