<?php

namespace Magenest\Order\Plugin\Sales\Block\Adminhtml\Order\Create;


use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Block\Adminhtml\Order\Create\Data;

class DataPlugin
{
    /**
     * @param Data $subject
     * @param $result
     * @param $id
     * @param bool $useCache
     * @return string
     * @throws LocalizedException
     */
    public function afterGetChildHtml(Data $subject, $result, $id, bool $useCache = true): string
    {
        if ($id == 'form_account') {
            $block = $subject->getLayout()->createBlock('Magenest\Order\Block\Adminhtml\Order\Create\Source')
                ->setTemplate('Magenest_Order::order/create/source.phtml')->toHtml();
            $result = $result . $block;
        }
        return $result;
    }
}
