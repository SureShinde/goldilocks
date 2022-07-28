<?php

namespace Magenest\FbChatbot\Ui\Component\Listing\Columns;

use Magenest\FbChatbot\Model\Button;
use Magenest\FbChatbot\Model\ResourceModel\Button\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class ButtonNames extends AbstractSource implements SourceInterface, OptionSourceInterface{
    /**
     * @var CollectionFactory
     */
    protected $buttonColFactory;

    public function __construct(
        CollectionFactory $buttonColFactory
    ) {
        $this->buttonColFactory = $buttonColFactory->create();
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach ($this->buttonColFactory->getData() as $value) {
            $result[] = ['value' => $value[Button::ID], 'label' => $value[Button::TITLE]];
        }

        return $result;
    }
}
