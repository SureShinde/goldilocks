<?php


namespace Magenest\AbandonedCart\Block\Adminhtml\Chart\Edit\Tab\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Status
 * @package Magenest\AbandonedCart\Block\Adminhtml\Chart\Edit\Tab\Renderer
 */
class Status extends AbstractRenderer
{

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        return $value == 1 ? __('Active') : __('Inactive');
    }
}
