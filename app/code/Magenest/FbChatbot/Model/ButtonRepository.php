<?php

namespace Magenest\FbChatbot\Model;

use Magenest\FbChatbot\Api\ButtonRepositoryInterface;
use Magenest\FbChatbot\Api\Data\ButtonInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;

class ButtonRepository implements ButtonRepositoryInterface
{
    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var Button[]
     */
    protected $instancesById = [];

    /**
     * @var Button[]
     */
    protected $instances = [];

    /**
     * @var int
     */
    private $cacheLimit = 0;

    /**
     * @var ButtonFactory
     */
    protected $_buttonFactory;

    /**
     * @var ResourceModel\Button
     */
    protected $_buttonResource;

    /**
     * ButtonRepository constructor.
     * @param ButtonFactory $buttonFactory
     * @param ResourceModel\Button $buttonResource
     * @param Json|null $serializer
     * @param int $cacheLimit
     */
    public function __construct(
        ButtonFactory $buttonFactory,
        \Magenest\FbChatbot\Model\ResourceModel\Button $buttonResource,
        Json $serializer = null,
        $cacheLimit = 1000
    ) {
        $this->_buttonFactory = $buttonFactory;
        $this->_buttonResource = $buttonResource;
        $this->cacheLimit = (int)$cacheLimit;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * @inheritDoc
     */
    public function save(ButtonInterface $button)
    {
        $this->_buttonResource->save($button);
        return $button;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function getById($buttonId)
    {
        $cacheKey = $this->getCacheKey([]);
        if (!isset($this->instancesById[$buttonId][$cacheKey])) {
            $button = $this->_buttonFactory->create();
            $this->_buttonResource->load($button,$buttonId);
            if (!$button->getId()) {
                throw new NoSuchEntityException(
                    __("The button that was requested doesn't exist. Verify the button and try again.")
                );
            }
            $this->cacheButton($cacheKey, $button);
        }
        return $this->instancesById[$buttonId][$cacheKey];
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
     * @param ButtonInterface $post
     */
    private function cacheButton($cacheKey, ButtonInterface $button)
    {
        $this->instancesById[$button->getId()][$cacheKey] = $button;
        $this->savePostInLocalCache($button, $cacheKey);

        if ($this->cacheLimit && count($this->instances) > $this->cacheLimit) {
            $offset = round($this->cacheLimit / -2);
            $this->instancesById = array_slice($this->instancesById, $offset, null, true);
            $this->instances = array_slice($this->instances, $offset, null, true);
        }
    }

    /**
     * @param Button $button
     * @param string $cacheKey
     */
    private function savePostInLocalCache(Button $button, $cacheKey)
    {
        $preparedSku = $this->prepareId($button->getId());
        $this->instances[$preparedSku][$cacheKey] = $button;
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
    public function delete(ButtonInterface $button)
    {
        return $this->_buttonResource->delete($button);

    }

    /**
     * @inheritDoc
     */
    public function deleteById($buttonId)
    {
        $button = $this->_buttonFactory->create();
        $this->_buttonResource->load($button,$buttonId);
        return $this->_buttonResource->delete($button);
    }

}
