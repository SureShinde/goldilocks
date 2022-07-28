<?php


namespace Magenest\Popup\Block\Adminhtml\Popup\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class TemplatePreviewButton
 * @package Magenest\Popup\Block\Adminhtml\Popup\Edit\Button
 */
class TemplatePreviewButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getPopupId()) {
            $data = [
                'label' => __('Preview Popup'),
                'on_click' => sprintf("window.open('%s&background_image='+ (document.getElementsByClassName('preview-link')[0] ? document.getElementsByClassName('preview-link')[0].href : '0')+
                                                            '&template_id='+document.getElementsByTagName('select')['popup_template_id'].value+'&html_content='+escape(document.getElementsByTagName('textarea')['html_content'].value));", $this->getUrlPreview())
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getUrlPreview()
    {
        return $this->urlBuilder->getBaseUrl().'magenest_popup/popup/preview?popup_id='.$this->getPopupId();
    }
}
