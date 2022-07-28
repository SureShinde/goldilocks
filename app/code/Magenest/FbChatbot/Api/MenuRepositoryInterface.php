<?php
namespace Magenest\FbChatbot\Api;

use Magenest\FbChatbot\Api\Data\MenuInterface;

interface MenuRepositoryInterface
{
    /**
     * @param MenuInterface $menu
     * @return mixed
     */
    public function save(MenuInterface $menu);

    /**
     * @param $menuId
     * @return mixed
     */
    public function getById($menuId);

    /**
     * @param MenuInterface $menu
     * @return mixed
     */
    public function delete(MenuInterface $menu);

    /**
     * @param $menuId
     * @return mixed
     */
    public function deleteById($menuId);
}
