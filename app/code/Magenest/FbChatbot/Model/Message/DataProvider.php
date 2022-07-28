<?php
namespace Magenest\FbChatbot\Model\Message;

use Magenest\FbChatbot\Api\Data\MessageInterface;
use Magenest\FbChatbot\Model\Message\MetaData\ValueProvider;
use Magenest\FbChatbot\Model\ResourceModel\Message\CollectionFactory;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var ValueProvider
     */
    protected $valueProvider;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|mixed|null
     */
    protected $serializer;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $messageColFactory
     * @param ValueProvider $valueProvider
     * @param \Magento\Framework\Registry $registry
     * @param PoolInterface $pool
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $messageColFactory,
        ValueProvider $valueProvider,
        \Magento\Framework\Registry $registry,
        PoolInterface $pool,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $messageColFactory->create();
        $this->valueProvider = $valueProvider;
        $this->coreRegistry = $registry;
        $this->pool = $pool;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        $this->loadedData = array();

        foreach ($items as $message) {
            $message[MessageInterface::MESSAGE_TYPES] = $this->serializer->unserialize($message[MessageInterface::MESSAGE_TYPES]);
            $this->loadedData[$message->getId()] = $message->getData();
        }

        return $this->loadedData;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
