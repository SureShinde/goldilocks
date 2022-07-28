<?php
namespace Magenest\FbChatbot\Model;

class MessageBuilder{
    public function createGenericTemplate($elements) {
        return array(
            "attachment" => array(
                "type" => "template",
                "payload" => array(
                    "template_type" => "generic",
                    'image_aspect_ratio' => 'square',
                    "elements" => $elements
                )
            )
        );
    }

    public function createButtonTemplate($text, $buttons) {
        return array(
            "attachment" => array(
                "type" => "template",
                "payload" => array(
                    "template_type" => "button",
                    "text" => $text,
                    "buttons" => $buttons
                )
            )
        );
    }

    public function createMediaTemplate($attachments) {
        return array(
            "attachment" => array(
                "type" => "template",
                "payload" => array(
                    "template_type" => "media",
                    "elements" => [$attachments]
                )
            )
        );
    }

    public function createAttachmentElement($attachmentType, $url, $buttons = []) {
        return array(
            "media_type" => $attachmentType,
            "url" => $url,
            "buttons" => $buttons
        );
    }

    public function createTemplateElement($title, $subtitle, $buttons = [], $imageUrl = '') {
        return array(
            "title" => $title,
            "subtitle" => $subtitle,
            "image_url" => $imageUrl,
            "buttons" => $buttons
        );
    }

    public function createButton($type, $title, $payload = "", $url = "") {
        return array(
            "type" => $type,
            "title" => $title,
            "payload" => $payload,
            "url" => $url
        );
    }

    public function createTemplateDefaultAction($url, $isMessengerExtension = false, $webviewHeight = "TALL") {
        return array(
            "type" => "web_url",
            "url" => $url,
            "messenger_extensions" => $isMessengerExtension,
            "webview_height_ratio" => $webviewHeight
        );
    }

    public function createTextMessage($text) {
        return array(
            "text" => $text
        );
    }

    public function createQuickReplyTemplate($text,$replies){
        return array(
            "text" => $text,
            "quick_replies" => $replies
        );
    }
    public function createFileMessage($type, $attachment_id) {
        return array(
            "attachment" => array(
                "type" => $type,
                "payload" => array(
                    "attachment_id" => $attachment_id,
                    'is_reusable' => true
                )
            )
        );
    }
    public function createReceiptTemplate($payload) {
        return array(
            "attachment" => array(
                "type" => "template",
                "payload" => $payload
            )
        );
    }

}
