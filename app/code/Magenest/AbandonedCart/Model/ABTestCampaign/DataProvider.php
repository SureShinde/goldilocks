<?php
/*
 * @created 30/01/2020 - 08:16
 * @project m2.3.3
 * @author hahh
*/

namespace Magenest\AbandonedCart\Model\ABTestCampaign;

use Magenest\AbandonedCart\Model\ResourceModel\ABTestCampaign\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magenest\AbandonedCart\Model\ResourceModel\ABTestCampaignFactory;
use Magenest\AbandonedCart\Model\ABTestCampaignFactory as ABTestCampaignModelFactory;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /** @var \Magento\Framework\App\RequestInterface $_request */
    protected $_request;

    /** @var \Magenest\AbandonedCart\Model\ResourceModel\ABTestCampaignFactory $_aBTestCampaignResource */
    protected $_aBTestCampaignResource;

    /** @var \Magenest\AbandonedCart\Model\ABTestCampaignFactory  $_aBTestCampaignModelFactory */
    protected $_aBTestCampaignModelFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $abtestcampaignCollectionFactory
     * @param RequestInterface $request
     * @param ABTestCampaignFactory $aBTestCampaignResource
     * @param ABTestCampaignModelFactory $aBTestCampaignModelFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        CollectionFactory $abtestcampaignCollectionFactory,
        ABTestCampaignFactory $aBTestCampaignResource,
        ABTestCampaignModelFactory $aBTestCampaignModelFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $abtestcampaignCollectionFactory->create();
        $this->_aBTestCampaignResource = $aBTestCampaignResource->create();
        $this->_aBTestCampaignModelFactory = $aBTestCampaignModelFactory->create();
        $this->_request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        //get id
        $id   = $this->_request->getParam('id');
        if ($id != null) {
            //get data campaign
            $this->_aBTestCampaignResource->load($this->_aBTestCampaignModelFactory, $id, 'id');
            $this->loadedData[$id] = $this->_aBTestCampaignModelFactory->getData();
            //convert string to date
            $this->formatDate($id);
            return $this->loadedData;
        } else {
            return [];
        }
    }
    private function formatDate($id)
    {
        //format from date
        $fromDate = strtotime($this->loadedData[$id]['from_date']);
        $this->loadedData[$id]['from_date'] = date('m/d/y', $fromDate);

        //format to date
        $toDate = strtotime($this->loadedData[$id]['to_date']);
        $this->loadedData[$id]['to_date'] = date('m/d/y', $toDate);
    }
}
