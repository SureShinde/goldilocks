<?php


namespace Magenest\AbandonedCart\Block\Adminhtml\Chart;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class Campaign extends AbstractChart
{
    /**
     * @var Registry $_registry
     */
    protected $_registry;

    /**
     * Campaign constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }
}
