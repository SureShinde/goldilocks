<?php

namespace Magenest\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\TestCampaign\Renderer;

use Magenest\AbandonedCart\Model\LogContentFactory;

class ShowEmailTest extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /** @var LogContentFactory $_logContent */
    protected $_logContent;

    /** @var \Magento\Framework\Registry|null $_coreRegistry */
    protected $_coreRegistry = null;

    /**
     * @var \Magenest\AbandonedCart\Helper\Data
     */
    protected $_helperData;

    /**
     * EmailTest constructor.
     * @param LogContentFactory $logContentFactory
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magenest\AbandonedCart\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magenest\AbandonedCart\Model\LogContentFactory $logContentFactory,
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Magenest\AbandonedCart\Helper\Data $helperData,
        array $data = []
    ) {
        $this->_logContent = $logContentFactory;
        $this->_coreRegistry = $registry;
        $this->_helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * get email for test in config and render into grid
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $result = '<div class="admin__grid-control">';
        $result .= '<span class="admin__grid-control-value">';
        return $result . $this->_helperData->getConfig("abandonedcart/general/test_email") . '</div>';
    }
}
