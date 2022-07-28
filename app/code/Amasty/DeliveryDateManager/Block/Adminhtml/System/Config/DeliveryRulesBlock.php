<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class DeliveryRulesBlock extends Field
{
    public const BLOCKS_URL_PLACEHOLDER = '{blocks_url}';

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->prepareComment($element);

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     */
    private function prepareComment(AbstractElement $element): void
    {
        $blocksUrl = $this->getUrl('cms/block/index');
        $comment = str_replace(self::BLOCKS_URL_PLACEHOLDER, $blocksUrl, (string)$element->getComment());
        $element->setComment($comment);
    }
}
