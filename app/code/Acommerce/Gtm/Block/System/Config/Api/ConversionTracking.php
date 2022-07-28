<?php
namespace Acommerce\Gtm\Block\System\Config\Api;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class ConversionTracking
 * @package Acommerce\Gtm\Block\System\Config\Api
 */
class ConversionTracking extends Field
{

    protected $_template = 'Acommerce_Gtm::system/config/api/conversion_tracking_container.phtml';

    /**
     * @var \Acommerce\Gtm\Model\Api\ConversionTracking
     */
    protected $apiModel = null;

    /**
     * @var string
     */
    protected $itemPostUrl = null;

    /**
     * Version constructor.
     * @param \Acommerce\Gtm\Model\Api\ConversionTracking $apiModel
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Acommerce\Gtm\Model\Api\ConversionTracking $apiModel,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->apiModel = $apiModel;
    }


    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }


    /**
     * @return \Acommerce\Gtm\Model\Api\ConversionTracking
     */
    public function getApiModel()
    {
        return $this->apiModel;
    }

    /**
     * @return string
     */
    public function getUrlForItemsCreation() {
        $this->itemPostUrl = $this->_urlBuilder->getUrl('googletagmanager/conversiontracking/create');
        return $this->itemPostUrl;
    }
}