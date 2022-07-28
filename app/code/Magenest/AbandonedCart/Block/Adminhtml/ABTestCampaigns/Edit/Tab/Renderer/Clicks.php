<?php


namespace Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns\Edit\Tab\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class NameRule
 * @package Magenest\AbandonedCart\Block\Adminhtml\ABTestCampaigns\Edit\Tab\Renderer
 */
class Clicks extends AbstractRenderer
{

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $clicks = $row->getData('clicks');
        if(!$clicks){
            $clicks = "0";
        }
        return $clicks;
    }
}
