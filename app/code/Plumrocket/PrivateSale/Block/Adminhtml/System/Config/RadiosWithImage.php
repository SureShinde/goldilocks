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
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\Radios;

class RadiosWithImage extends Radios
{
    /**
     * @inheritDoc
     */
    protected function _optionToHtml($option, $selected)
    {
        $html = '<div class="admin__field admin__field-option">' .
            '<input type="radio" ' . $this->getRadioButtonAttributes($option);

        if (is_array($option)) {
            $html .= 'value="' . $this->_escape(
                    $option['value']
                ) . '" class="admin__control-radio" id="' . $this->getHtmlId() . $option['value'] . '"';

            if ($option['value'] == $selected) {
                $html .= ' checked="checked"';
            }

            $html .= ' />';
            $html .= '<label class="admin__field-label" for="' .
                $this->getHtmlId() .
                $option['value'] .
                '"><span>' .
                $option['label'] .
                '</span>
                <div><img src="' . $option['image'] . '" alt="' . $this->getHtmlId() . '"></div>
                </label>';

        } elseif ($option instanceof \Magento\Framework\DataObject) {

            $html .= 'id="' . $this->getHtmlId() . $option->getValue() . '"' . $option->serialize(
                    ['label', 'title', 'value', 'class', 'style']
                );

            if (in_array($option->getValue(), $selected)) {
                $html .= ' checked="checked"';
            }

            $html .= ' />';
            $html .= '<label class="inline" for="' .
                $this->getHtmlId() .
                $option->getValue() .
                '">' .
                $option->getLabel() .
                '<div><img src="' . $option->getImage() . '" alt="' . $this->getHtmlId() . '"></div>
                </label>';
        }

        $html .= '</div>';

        return $html;
    }
}
