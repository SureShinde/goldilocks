<?php
namespace Magenest\FbChatbot\Api\Data;


interface ButtonInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    const ID = 'button_id';
    const CODE = 'code';
    const DESCRIPTION = 'description';
    const BUTTON_TYPE = 'button_type';
    const TITLE = 'title';

    public function getId();

    public function setId($id);

    public function getCode();

    public function setCode($code);

    public function getDescription();

    public function setDescription($description);

    public function getButtonType();

    public function setButtonType($buttonType);

    public function getTitle();

    public function setTitle($title);

}
