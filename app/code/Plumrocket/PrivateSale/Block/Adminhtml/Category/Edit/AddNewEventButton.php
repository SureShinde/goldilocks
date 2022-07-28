<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\PrivateSale\Block\Adminhtml\Category\Edit;

class AddNewEventButton extends \Magento\Backend\Block\Widget\Button
{
    /**
     * @return string
     */
    public function getAttributesHtml()
    {
        $data = $this->getRequest()->getParams();
        $params = ['type' => '1'];

        if (isset($data['id'])) {
            $params['category_event'] = $data['id'];
        }

        $this->setData([
            'id' => 'prprivatesale_add_new_event',
            'name' => 'prprivatesale_add_new_event',
            'onclick' => 'setLocation("' . $this->getUrl('prprivatesale/event/edit', $params) . '")',
            'class' => 'primary',
            'label' => __('Add New Event')
        ]);

        return parent::getAttributesHtml();
    }

    /**
     * Prepare attributes
     *
     * @param string $title
     * @param array $classes
     * @param string $disabled
     * @return array
     */
    protected function _prepareAttributes($title, $classes, $disabled)
    {
        $attributes = [
            'id' => $this->getId(),
            'name' => $this->getElementName(),
            'title' => $title,
            'type' => $this->getType(),
            'class' => join(' ', $classes),
            'onclick' => $this->getOnClick(),
            'style' => $this->getStyle(),
            'value' => $this->getValue(),
            'disabled' => $disabled,
        ];
        if ($this->hasData('backend_button_widget_hook_id')) {
            $attributes['backend-button-widget-hook-id'] = $this->getData('backend_button_widget_hook_id');
        }
        if ($this->getDataAttribute()) {
            foreach ($this->getDataAttribute() as $key => $attr) {
                $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
            }
        }
        return $attributes;
    }
}
