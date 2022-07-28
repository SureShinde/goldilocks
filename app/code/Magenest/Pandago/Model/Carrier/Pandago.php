<?php

namespace Magenest\Pandago\Model\Carrier;

use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Tracking\ResultFactory as TrackFactory;

/**
 * Custom shipping model
 */
class Pandago extends AbstractCarrierOnline implements CarrierInterface
{
    const CODE = 'pandago';
    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Rate result data
     *
     * @var Result
     */
    protected $_result;

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $rateMethodFactory;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    private \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory;
    private CollectionFactory $collectionFactory;

    /**
     * Pandago constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->messageManager = $messageManager;
        $this->collectionFactory = $collectionFactory;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $shippingCost = (float)$this->getConfigData('shipping_cost');

        $method->setPrice($shippingCost);
        $method->setCost($shippingCost);

        $result->append($method);

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $result = new \Magento\Framework\DataObject();
        $poundToKg =  0.4535;
        $weight = 0;
        $length = 0;
        $width = 0;
        $height = 0;
        $type = 0;
        $result->setTrackingNumber($request->getOrderShipment()->getOrderId());
        $result->setShippingLabelContent('Pandago Service');
        if (!$request->getOrderShipment()->getOrder()->getApiOrderId()) {
            $packages = $request->getPackages();
            foreach ($packages as $package) {
                if ($package['params']['weight']) {
                    $weight += ($package['params']['weight_units'] ==  'POUND') ? ($package['params']['weight'] * $poundToKg)
                        : $package['params']['weight'] * 1000;
                }
                $length = $package['params']['length'];
                $width = $package['params']['width'];
                $height = $package['params']['height'];
                $type = $package['params']['shipment_type'];
            }
            $this->createOrder($request->getOrderShipment(), $result);
        }
        return $result;
    }

    public function callApi($param, $uri)
    {
        $client = $this->httpClientFactory->create();
        $client->setUri($uri);
        $client->setMethod(\Zend_Http_Client::POST);
        $client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $client->setHeaders('Accept', 'application/json');
        $client->setHeaders('Authorization', 'Bearer');
        $client->setRawData(json_encode($param, true));
        return $client->request()->getBody();
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        $this->_getTracking($trackings);

        return $this->_result;
    }

    /**
     * Get cgi tracking
     *
     * @param string[] $trackings
     * @return TrackFactory
     */
    protected function _getTracking($trackings)
    {
        $result = $this->_trackFactory->create();
        foreach ($trackings as $tracking) {
            $status = $this->_trackStatusFactory->create();
            $shipmentTrack = $this->collectionFactory->create()
                ->addFieldToFilter(ShipmentTrackInterface::TRACK_NUMBER, $tracking)
                ->setPageSize(1)
                ->getFirstItem();
            $status->setCarrier($this->_code);
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl($shipmentTrack ? $shipmentTrack->getDescription() : '#');
            $result->append($status);
        }

        $this->_result = $result;

        return $result;
    }
}
