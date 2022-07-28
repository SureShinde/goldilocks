<?php

namespace Magenest\FbChatbot\Model;

use Magenest\FbChatbot\Api\Data\MenuInterface;
use Magenest\FbChatbot\Api\MenuRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;

class MenuRepository implements MenuRepositoryInterface{
    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var Menu[]
     */
    protected $instancesById = [];

    /**
     * @var Menu[]
     */
    protected $instances = [];

    /**
     * @var int
     */
    private $cacheLimit = 0;

    /**
     * @var MenuFactory
     */
    protected $_menuFactory;

    /**
     * @var ResourceModel\Menu
     */
    protected $_menuResource;

    /**
     * MenuRepository constructor.
     * @param MenuFactory $menuFactory
     * @param ResourceModel\Menu $menuResource
     * @param Json|null $serializer
     * @param int $cacheLimit
     */
    public function __construct(
        MenuFactory $menuFactory,
        \Magenest\FbChatbot\Model\ResourceModel\Menu $menuResource,
        Json $serializer = null,
        $cacheLimit = 1000
    ) {
        $this->_menuFactory = $menuFactory;
        $this->_menuResource = $menuResource;
        $this->cacheLimit = (int)$cacheLimit;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Json::class);
    }
    /**
     * @inheritDoc
     */
    public function save(MenuInterface $menu)
    {
         $this->_menuResource->save($menu);
         return $menu;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function getById($menuId)
    {
        $cacheKey = $this->getCacheKey([]);
        if (!isset($this->instancesById[$menuId][$cacheKey])) {
            $menu = $this->_menuFactory->create();
            $this->_menuResource->load($menu,$menuId);
            if (!$menu->getId()) {
                throw new NoSuchEntityException(
                    __("The menu that was requested doesn't exist. Verify the menu and try again.")
                );
            }
            $this->cacheMenu($cacheKey, $menu);
        }
        return $this->instancesById[$menuId][$cacheKey];
    }

    /**
     * @param $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }
        $serializeData = $this->serializer->serialize($serializeData);
        return sha1($serializeData);
    }

    /**
     * @param $cacheKey
     * @param MenuInterface $menu
     */
    private function cacheMenu($cacheKey, MenuInterface $menu)
    {
        $this->instancesById[$menu->getId()][$cacheKey] = $menu;
        $this->savePostInLocalCache($menu, $cacheKey);

        if ($this->cacheLimit && count($this->instances) > $this->cacheLimit) {
            $offset = round($this->cacheLimit / -2);
            $this->instancesById = array_slice($this->instancesById, $offset, null, true);
            $this->instances = array_slice($this->instances, $offset, null, true);
        }
    }

    /**
     * @param Menu $menu
     * @param $cacheKey
     */
    private function savePostInLocalCache(Menu $menu, $cacheKey)
    {
        $preparedSku = $this->prepareId($menu->getId());
        $this->instances[$preparedSku][$cacheKey] = $menu;
    }

    /**
     * @param string $id
     * @return string
     */
    private function prepareId($id)
    {
        return (string) trim($id);
    }

    /**
     * @inheritDoc
     */
    public function delete(MenuInterface $menu)
    {
        return $this->_menuResource->delete($menu);

    }

    /**
     * @inheritDoc
     */
    public function deleteById($menuId)
    {
        $menu = $this->_menuFactory->create();
        $this->_menuResource->load($menu,$menuId);
        return $this->_menuResource->delete($menu);
    }
}
