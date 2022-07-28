<?php
namespace Magenest\FbChatbot\Api;

use Magenest\FbChatbot\Api\Data\ButtonInterface;

interface ButtonRepositoryInterface
{
    /**
     * @param ButtonInterface $button
     * @return mixed
     */
    public function save(ButtonInterface $button);

    /**
     * @param $buttonId
     * @return mixed
     */
    public function getById($buttonId);

    /**
     * @param ButtonInterface $button
     * @return mixed
     */
    public function delete(ButtonInterface $button);

    /**
     * @param $buttonId
     * @return mixed
     */
    public function deleteById($buttonId);
}
