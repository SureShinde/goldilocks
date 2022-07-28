<?php

namespace Magenest\FbChatbot\Ui\Component\Form;

use Magenest\FbChatbot\Model\Message;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class MessageTypes extends AbstractSource implements SourceInterface, OptionSourceInterface{
    /**
     * @var Message
     */
    protected $message;

    public function __construct(
        Message $message
    ) {
        $this->message = $message;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach ($this->message->getMessageTypes() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
