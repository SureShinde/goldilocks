<?php

namespace Magenest\WebApiLog\Block\Adminhtml;

use Magenest\WebApiLog\Model\ApiLogFactory;
use Magento\Backend\Block\Template;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class ApiLogListing
 *
 * @package Magenest\WebApiLog\Block\Adminhtml
 */
class ApiLogListing extends Template
{

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    public $_template = 'Magenest_WebApiLog::log_detail.phtml';

    /**
     * @var ApiLogFactory
     */
    protected $apiLogFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * ApiLogListing constructor.
     *
     * @param Template\Context $context
     * @param ApiLogFactory $apiLogFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ApiLogFactory $apiLogFactory,
        SerializerInterface $serializer
    ) {
        $this->apiLogFactory = $apiLogFactory;
        $this->serializer = $serializer;

        parent::__construct($context);
    }

    /**
     * Get admin api log detail
     *
     * @return \Magenest\WebApiLog\Model\ApiLog
     */
    public function getLog()
    {
        $id   = $this->getRequest()->getParam('id');
        $apiLog = $this->apiLogFactory->create()->load($id);
        return $apiLog;
    }

    /**
     * @param $content
     * @return array
     */
    public function convertData($content)
    {
        if (!empty($content)) {
            return $this->serializer->unserialize($content);
        }
        return [];
    }
}
