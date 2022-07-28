<?php

namespace Magenest\FbChatbot\Ui\Component\Listing\Columns;

use Magenest\FbChatbot\Model\Button;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class ButtonTypes extends AbstractSource implements SourceInterface, OptionSourceInterface{
    /**
     * @var Button
     */
    protected $button;

    public function __construct(
        Button $button
    ) {
        $this->button = $button;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach ($this->button->getButtonTypes() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
