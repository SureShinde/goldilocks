<?php
namespace Magenest\FbChatbot\Model\Message\MetaData;

use Magenest\FbChatbot\Model\MessageFactory;
use Magenest\FbChatbot\Model\ResourceModel\Button\CollectionFactory;

/**
 * Metadata provider for chatbot message edit form.
 */
class ValueProvider
{
    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    public function __construct(
        MessageFactory $messageFactory,
        CollectionFactory $buttonColFactory
    ) {
        $this->messageFactory = $messageFactory;
        $this->buttonColFactory = $buttonColFactory;
    }

    /**
     * Get metadata for message form. It will be merged with form UI component declaration.
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */

    public function getMetadataValues()
    {
        $messageTypesOptions = [];
        $messageTypes = $this->messageFactory->create()->getMessageTypes();
        foreach ($messageTypes as $key => $messageType) {
            $messageTypesOptions[] = [
                'label' => $messageType,
                'value' => $key,
            ];
        }

        $buttonNameOptions = [];
        $buttonCollection = $this->buttonColFactory->create()->getData();
        if ($buttonCollection){
            foreach ($buttonCollection as $button){
                $buttonNameOptions [] = [
                    'label' => $button['name'],
                    'value' => $button['button_id'],
                ];
            }
        }

        $messageExtensionsOptions = [];
        $messageExtensions = $this->messageFactory->create()->getMessageExtensions();
        foreach ($messageExtensions as $key => $messageExtension) {
            $messageExtensionsOptions[] = [
                'label' => $messageExtension,
                'value' => $key,
            ];
        }

        $webviewHeightOptions = [];
        $webviewHeights = $this->messageFactory->create()->getWebviewHeights();
        foreach ($webviewHeights as $key => $webviewHeight) {
            $webviewHeightOptions[] = [
                'label' => $webviewHeight,
                'value' => $key,
            ];
        }

        return [
            'message' => [
                'children' => [
                    'message_type' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => $messageTypesOptions
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            'action' => [
                'children' =>[
                    'messenger_extensions' => [
                        'arguments' => [
                            'data' =>[
                                'config' => [
                                    'options' =>$messageExtensionsOptions
                                ]
                            ]
                        ]
                    ],
                    'webview_height_ratio' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' =>$webviewHeightOptions
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
