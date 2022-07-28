<?php

namespace Magenest\StoreLocatorPopup\Helper;

use Amasty\Storelocator\Model\ConfigProvider;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\Storelocator\Model\ResourceModel\Location as LocationResourceModel;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Magenest\StoreLocatorPopup\Helper\Cookie as CookieHelper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonFramework;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const XML_DISTANCE_LIMIT = 'amlocator/general/distance_limit';
    const XML_ENABLED_RESTRICT = 'storepickup_locator/general/enabled_restrict';
    const XML_PAYMENT_METHOD_RESTRICT = 'storepickup_locator/general/payment_method_restrict';

    const GOOGLE_MAP_HOST = 'https://maps.googleapis.com/maps/api/geocode/json';

    /** @var Cookie  */
    protected $cookieHelper;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var CollectionFactory  */
    protected $locationCollectionFactory;

    /** @var LocationFactory  */
    protected $locationModel;

    /** @var LocationResourceModel  */
    protected $locationResourceModel;

    /** @var ConfigProvider  */
    protected $configProvider;

    /** @var JsonFramework  */
    protected $serializer;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @param Context $context
     * @param Cookie $cookieHelper
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $locationCollectionFactory
     * @param LocationFactory $locationFactory
     * @param LocationResourceModel $locationResourceModel
     * @param ConfigProvider $configProvider
     * @param JsonFramework $serializer
     */
    public function __construct(
        Context $context,
        CookieHelper $cookieHelper,
        StoreManagerInterface $storeManager,
        CollectionFactory  $locationCollectionFactory,
        LocationFactory $locationFactory,
        LocationResourceModel $locationResourceModel,
        ConfigProvider   $configProvider,
        JsonFramework $serializer,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->cookieHelper = $cookieHelper;
        $this->storeManager = $storeManager;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->locationModel = $locationFactory;
        $this->locationResourceModel = $locationResourceModel;
        $this->configProvider = $configProvider;
        $this->serializer = $serializer;
        $this->resource = $resource;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getDistanceLimitConfig()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue(self::XML_DISTANCE_LIMIT, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getEnabledRestrict()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue(self::XML_ENABLED_RESTRICT, ScopeInterface::SCOPE_STORE, $storeId);
    }


    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getPaymentMethodRestrict()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue(self::XML_PAYMENT_METHOD_RESTRICT, ScopeInterface::SCOPE_STORE, $storeId);
    }


    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getLocation()
    {
        $locationCollection = $this->locationCollectionFactory->create();
        $select = $locationCollection->getSelect();
        $locationCollection->joinMainImage();
        $select->where('main_table.status = 1');
        $locationCollection->addDistance($select);
        $locationCollection->joinScheduleTable();
        $connection = $this->resource->getConnection();
        $data['totalRecords'] = 0;
        $data['items'] = [];
        foreach ($locationCollection as $location) {
            if ($location->getShowSchedule() && $location->getScheduleString()) {
                $workingTimeToday = $location->getWorkingTimeToday();
            } else {
                $workingTimeToday = null;
            }
            $location->setData('working_time', $workingTimeToday);
            $storeIds = explode(',', $location->getStores());

            $selectSource = $connection->select()->from(
                ['isch' => $connection->getTableName('ecombricks_store__inventory_stock_sales_channel')],
                ['source_code' => 'inventory_source.source_code']
            )
                ->join(
                    ['store' => $connection->getTableName('store')],
                    'store.code = isch.code',
                    ['']
                )
                ->join(
                    ['is' => $connection->getTableName('inventory_stock')],
                    'is.stock_id = isch.stock_id',
                    ['']
                )->join(
                    ['issl' => $connection->getTableName('inventory_source_stock_link')],
                    'issl.stock_id =is.stock_id',
                    ['']
                )
                ->join(
                    ['inventory_source' => $connection->getTableName('inventory_source')],
                    'inventory_source.source_code = issl.source_code',
                    ['']
                )->where('store.store_id in (?)', $storeIds)->limit(1);
            $sourceCode = $connection->fetchOne($selectSource);
            if ($sourceCode) {
                $location->setData('source_code', $sourceCode);
                $data['totalRecords'] = $data['totalRecords']++;
                $data['items'][] = $location->getData();
            }
        }
        return $data;
    }

    /**
     * @param $addressFrom
     * @param $addressTo
     *
     * @return float|mixed
     * @throws InputException
     */
    public function calculateDistance($addressFrom = 'GF Convergys Bldg., Salcedo St., cor', $addressTo = '')
    {
        $apiKey = $this->configProvider->getApiKey();
        $addressFrom    = str_replace(' ', '+', $addressFrom);
        // Geocoding API request with start address
        $geocodeFrom = file_get_contents(self::GOOGLE_MAP_HOST . '?address=' . $addressFrom . '&sensor=false&key=' . $apiKey);
        $outputFrom = $this->serializer->unserialize($geocodeFrom);
        if (!empty($outputFrom['error_message'])) {
            return $outputFrom['error_message'];
        }
        $latitudeFrom = $outputFrom['results'][0]['geometry']['location']['lat'];
        $longitudeFrom = $outputFrom['results'][0]['geometry']['location']['lng'];
        $addressTo = $this->getAddressTo();
        if ($addressTo == null) {
            throw new InputException(__('Store not found. Please switch to another store'));
        }
        $latitudeTo = $addressTo['lat'];
        $longitudeTo  = $addressTo['lng'];

        // Calculate distance between latitude and longitude
        $theta    = $longitudeFrom - $longitudeTo;
        $dist    = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
        $dist    = acos($dist);
        $dist    = rad2deg($dist);
        $miles    = $dist * 60 * 1.1515;
        // unit is km
        $distance = round($miles * 1.609344, 2);
        $distanceLimit = $this->getDistanceLimitConfig();
        if ((float)$distance > (float)$distanceLimit) {
            throw new InputException(
                __(
                    'Sorry but the store can\'t ship more than %distance km.
                    Please switch to another store or your shipping address',
                    ['distance' => $distanceLimit]
                )
            );
        }
    }

    /**
     * Return position of store is selected by customer
     *
     * @return array|null
     */
    public function getAddressTo()
    {
        $storeId = $this->cookieHelper->get();
        $locatorModel = $this->locationModel->create();
        $this->locationResourceModel->load($locatorModel, $storeId);
        if ($locatorModel->getId()) {
            return [
                'lat' => $locatorModel->getLat(),
                'lng' => $locatorModel->getLng()
            ];
        }
        return null;
    }
}
