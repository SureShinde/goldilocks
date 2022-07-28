<?php
namespace Magenest\FbChatbot\Model\Source;

use Magenest\FbChatbot\Model\Message;

class MediaType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Message::MESSAGE_MEDIA_IMAGE,
                'label' => __('Image')
            ],
            [
                'value' => Message::MESSAGE_MEDIA_VIDEO,
                'label' => __('Video')
            ]
        ];
    }
}
