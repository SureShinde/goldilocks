<?php


namespace Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns\Edit\Tab\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class NameRule
 * @package Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns\Edit\Tab\Renderer
 */
class NameRule extends AbstractRenderer
{

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $actions              = [];
        $url              = $this->getUrl('*/rule/edit/', ['id'=>$row->getId()]);
        $actions[]            = [
            '@' => [
                'href' => $url,
                'caption' => __($row->getName()),
                'target' => '_blank'
            ],
            '#' => __($row->getName()),
        ];
        return $this->_actionsToHtml($actions);
    }

    /**
     * @param array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions)
    {
        $html             = [];
        $attributesObject = new \Magento\Framework\DataObject();
        foreach ($actions as $action) {
            $attributesObject->setData($action['@']);
            $html[] = '<a ' . $attributesObject->serialize() . '>' . $action['#'] . '</a>';
        }
        return implode('<span class="separator">&nbsp;<br/>&nbsp;</span>', $html);
    }
}
