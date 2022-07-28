<?php


namespace Magenest\AbandonedCart\Block\Adminhtml\Chart\Edit\Tab\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Action
 * @package Magenest\AbandonedCart\Block\Adminhtml\Chart\Edit\Tab\Renderer
 */
class Action extends AbstractRenderer
{

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $actions = [];
        $url = $this->getUrl(
            '*/*/campaign',
            [
                'from' => $row->getData('from_date'),
                'to' => $row->getData('to_date'),
                'id_campaign' => $row->getData('id'),
                '_current' => true
            ]
        );
        $actions[] = [
            '@' => [
                'href' => $url,
                'caption' => __('View Report')
            ],
            '#' => __('View Report'),
        ];
        return $this->_actionsToHtml($actions);
    }

    /**
     * @param array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions)
    {
        $html = [];
        $attributesObject = new \Magento\Framework\DataObject();
        foreach ($actions as $action) {
            $attributesObject->setData($action['@']);
            $html[] = '<a ' . $attributesObject->serialize() . '>' . $action['#'] . '</a>';
        }
        return implode('<span class="separator">&nbsp;<br/>&nbsp;</span>', $html);
    }
}
