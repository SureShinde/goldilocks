<?php

namespace Magenest\StoreLocatorPopup\Controller\Ajax;

use Magenest\StoreLocatorPopup\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class SearchAddress extends Action
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @param Context $context
     * @param Data $data
     */
    public function __construct(
        Context $context,
        Data $data
    ) {
        parent::__construct($context);
        $this->data = $data;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $locationAddress = $this->getRequest()->getParams();
        if (!$locationAddress || !$locationAddress['lat'] || !$locationAddress['lng']) {
            $response['totalRecords'] = 0;
            $response['items'] = [];
        } else {
            $post = [
                'lat' => $locationAddress['lat'],
                'lng' => $locationAddress['lng'],
                'sortByDistance' => true,
                'radius' => $this->data->getDistanceLimitConfig()
            ];
            foreach ($post as $name => $value) {
                $this->_request->setPostValue($name, $value);
            }
            $response = $this->data->getLocation();
        }
        $this->getResponse()->setBody(json_encode($response));
    }
}
