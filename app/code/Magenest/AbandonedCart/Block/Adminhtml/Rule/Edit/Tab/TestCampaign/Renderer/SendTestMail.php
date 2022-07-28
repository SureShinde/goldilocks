<?php

namespace Magenest\AbandonedCart\Block\Adminhtml\Rule\Edit\Tab\TestCampaign\Renderer;

use Magenest\AbandonedCart\Helper\Data;
use Magenest\AbandonedCart\Model\LogContentFactory;
use Magenest\AbandonedCart\Model\ResourceModel\LogContent as ResourceLogContent;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magenest\AbandonedCart\Helper\SendMail;

class SendTestMail extends AbstractRenderer
{
    /** @var LogContentFactory $_logContent */
    protected $_logContent;

    /** @var Registry|null $_coreRegistry */
    protected $_coreRegistry = null;

    /** @var ResourceLogContent $_resourceLogContent */
    protected $_resourceLogContent;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * Action constructor.
     *
     * @param LogContentFactory $logContentFactory
     * @param Context $context
     * @param Registry $registry
     * @param ResourceLogContent $resourceLogContent
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        LogContentFactory $logContentFactory,
        Context $context,
        Registry $registry,
        ResourceLogContent $resourceLogContent,
        Data $helperData,
        array $data = []
    ) {
        $this->_logContent   = $logContentFactory;
        $this->_coreRegistry = $registry;
        $this->_resourceLogContent = $resourceLogContent;
        $this->_helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * render column Test Email into grid
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $actions              = [];
        $ruleModel            = $this->_coreRegistry->registry('abandonedcart_rule');
        $logContentModel = $this->_logContent->create();
        $this->_resourceLogContent->load($logContentModel, $row->getData('id'), 'id');
        $hasEmailTest = $this->_helperData->getConfig("abandonedcart/general/test_email") ? 1 : 0;
        $onclick              = "sendMail('" . $this->getUrl('*/*/sendEmail', ['type' => SendMail::SEND_TEST_MAIL,'id' => $row->getId(), 'rule_id' => $ruleModel->getId(), 'log_id' => $logContentModel->getId()]) . "',".$hasEmailTest.")";
        $actions[]            = [
            '@' => [
                'onclick' => $onclick,
                'href'=>'#',
                'caption' => __('Test Mail'),
                'id' => 'send-test-mail'
            ],
            '#' => __('Test Mail'),
        ];
        return $this->_actionsToHtml($actions);
    }

    /**
     * add action for text in column Test Mail
     * @param array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions)
    {
        $html             = [];
        $attributesObject = new DataObject();
        foreach ($actions as $action) {
            $attributesObject->setData($action['@']);
            $html[] = '<a ' . $attributesObject->serialize() . '>' . $action['#'] . '</a>';
        }
        return implode('<span class="separator">&nbsp;<br/>&nbsp;</span>', $html);
    }
}
