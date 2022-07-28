<?php

namespace Magenest\FbChatbot\Ui\Component\Form;

use Magenest\FbChatbot\Model\Button;
use Magenest\FbChatbot\Model\ResourceModel\Button\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class ButtonName extends AbstractSource implements SourceInterface, OptionSourceInterface{

    /**
     * @var CollectionFactory
     */
    private $buttonCollectionFactory;

    public function __construct(
        CollectionFactory $buttonCollectionFactory
    ) {
        $this->buttonCollectionFactory = $buttonCollectionFactory;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];
        $buttonCollection = $this->buttonCollectionFactory->create()->getData();
        if ($buttonCollection){
            foreach ($buttonCollection as $button){
                $result [] = [
                    'label' => $button[Button::TITLE],
                    'value' => $button[Button::ID],
                ];
            }
        }

        return $result;
    }
}
