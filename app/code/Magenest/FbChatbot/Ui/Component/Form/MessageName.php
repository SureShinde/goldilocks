<?php

namespace Magenest\FbChatbot\Ui\Component\Form;

use Magenest\FbChatbot\Model\ResourceModel\Message\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class MessageName extends AbstractSource implements SourceInterface, OptionSourceInterface{

    /**
     * @var CollectionFactory
     */
    private $messageCollectionFactory;

    public function __construct(
        CollectionFactory $messageCollectionFactory
    ) {
        $this->messageCollectionFactory = $messageCollectionFactory;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];
        $messageCollection = $this->messageCollectionFactory->create()->getData();
        if ($messageCollection){
            foreach ($messageCollection as $message){
                $result [] = [
                    'label' => $message['name'],
                    'value' => $message['message_id'],
                ];
            }
        }

        return $result;
    }
}
